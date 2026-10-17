<?php

namespace App\Services;

use App\Models\Library;
use App\Models\LibraryPayout;
use App\Models\LibraryPurchase;
use App\Traits\ImageUploadTrait;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Rekap bagi hasil pustaka berbayar per bulan. Pendapatan diakui pada bulan
 * pembayaran dikonfirmasi admin (verified_at) untuk pembelian berstatus aktif.
 */
class LibraryRevenueService
{
    use ImageUploadTrait;

    public const PERIOD_PATTERN = '/^\d{4}-(0[1-9]|1[0-2])$/';

    public static function defaultPeriod(): string
    {
        return now()->subMonthNoOverflow()->format('Y-m');
    }

    /**
     * Transfer hanya boleh dicatat untuk bulan yang sudah lewat, agar angkanya tidak bertambah lagi.
     */
    public static function isClosed(string $period): bool
    {
        return $period < now()->format('Y-m');
    }

    /**
     * Satu baris per pustaka yang punya bagi hasil (atau sudah punya catatan transfer di periode ini).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function monthlySummary(string $period): Collection
    {
        [$start, $end] = $this->range($period);

        $stats = LibraryPurchase::query()
            ->where('status', LibraryPurchase::STATUS_ACTIVE)
            ->whereBetween('verified_at', [$start, $end])
            ->groupBy('library_id')
            ->selectRaw('library_id, COUNT(*) as purchase_count, COALESCE(SUM(price), 0) as gross_amount')
            ->get()
            ->keyBy('library_id');

        $payouts = LibraryPayout::where('period', $period)->get()->keyBy('library_id');

        return Library::query()
            ->where(fn ($q) => $q->whereNotNull('payee_name')->whereNotNull('author_share_percent'))
            ->orWhereIn('id', $payouts->keys())
            ->orderBy('title')
            ->get()
            ->map(fn (Library $library) => $this->row(
                $library,
                (int) ($stats->get($library->id)?->purchase_count ?? 0),
                (int) ($stats->get($library->id)?->gross_amount ?? 0),
                $payouts->get($library->id),
            ));
    }

    /**
     * @return array<string, mixed>
     */
    public function summaryFor(Library $library, string $period): array
    {
        [$start, $end] = $this->range($period);

        $purchases = $this->activePurchases($library, $start, $end);

        return $this->row(
            $library,
            $purchases->count(),
            (int) $purchases->sum('price'),
            $library->payouts()->where('period', $period)->first(),
        );
    }

    /**
     * @return Collection<int, LibraryPurchase>
     */
    public function purchasesFor(Library $library, string $period): Collection
    {
        [$start, $end] = $this->range($period);

        return $this->activePurchases($library, $start, $end);
    }

    /**
     * Catat transfer dan bekukan angka periode tersebut. Mengembalikan catatan yang
     * sudah ada bila periode itu pernah dicatat (tidak menggandakan transfer).
     *
     * @param  array{transferred_at:string, transfer_proof:UploadedFile, admin_note:?string, processed_by:int}  $data
     */
    public function markPaid(Library $library, string $period, array $data): LibraryPayout
    {
        return DB::transaction(function () use ($library, $period, $data) {
            $existing = $library->payouts()->where('period', $period)->lockForUpdate()->first();

            if ($existing) {
                return $existing;
            }

            $summary = $this->summaryFor($library, $period);

            return $library->payouts()->create([
                'period' => $period,
                'purchase_count' => $summary['purchase_count'],
                'gross_amount' => $summary['gross_amount'],
                'share_percent' => $summary['share_percent'],
                'author_amount' => $summary['author_amount'],
                'payee_name' => $library->payee_name,
                'transferred_at' => $data['transferred_at'],
                'transfer_proof_path' => $this->handleImageUpload($data['transfer_proof'], 'library-payout-proofs', null, null, 80, 'local'),
                'admin_note' => $data['admin_note'] ?? null,
                'processed_by' => $data['processed_by'],
            ]);
        });
    }

    /**
     * Bagian penulis dibulatkan ke bawah ke Rupiah; sisanya bagian platform,
     * sehingga keduanya selalu berjumlah sama dengan pendapatan kotor.
     */
    public static function authorAmount(int $gross, int $percent): int
    {
        return intdiv($gross * $percent, 100);
    }

    /**
     * @return array<string, mixed>
     */
    private function row(Library $library, int $count, int $gross, ?LibraryPayout $payout): array
    {
        if ($payout) {
            return [
                'library' => $library,
                'payee_name' => $payout->payee_name,
                'purchase_count' => $payout->purchase_count,
                'gross_amount' => $payout->gross_amount,
                'share_percent' => $payout->share_percent,
                'author_amount' => $payout->author_amount,
                'platform_amount' => $payout->gross_amount - $payout->author_amount,
                'payout' => $payout,
                // Mis. pembelian ditolak (refund) setelah transfer dicatat.
                'changed_after_payout' => $count !== $payout->purchase_count || $gross !== $payout->gross_amount,
            ];
        }

        $percent = (int) $library->author_share_percent;
        $author = self::authorAmount($gross, $percent);

        return [
            'library' => $library,
            'payee_name' => $library->payee_name,
            'purchase_count' => $count,
            'gross_amount' => $gross,
            'share_percent' => $percent,
            'author_amount' => $author,
            'platform_amount' => $gross - $author,
            'payout' => null,
            'changed_after_payout' => false,
        ];
    }

    private function activePurchases(Library $library, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return $library->purchases()
            ->where('status', LibraryPurchase::STATUS_ACTIVE)
            ->whereBetween('verified_at', [$start, $end])
            ->orderBy('verified_at')
            ->get();
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function range(string $period): array
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $period)->startOfMonth();

        return [$start, $start->endOfMonth()];
    }
}

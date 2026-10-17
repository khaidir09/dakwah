<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Library;
use App\Models\LibraryPayout;
use App\Services\LibraryRevenueService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryRevenueController extends Controller
{
    public function __construct(protected LibraryRevenueService $service) {}

    public function index(Request $request)
    {
        $period = $this->period($request);
        $rows = $this->service->monthlySummary($period);

        return view('pages.admin.library-revenue.index', [
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'rows' => $rows,
            'totals' => [
                'gross_amount' => $rows->sum('gross_amount'),
                'author_amount' => $rows->sum('author_amount'),
                'platform_amount' => $rows->sum('platform_amount'),
            ],
        ]);
    }

    public function show(Request $request, Library $library)
    {
        $period = $this->period($request);

        return view('pages.admin.library-revenue.show', [
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'library' => $library,
            'summary' => $this->service->summaryFor($library, $period),
            'purchases' => $this->service->purchasesFor($library, $period)->load('user'),
            'isClosed' => LibraryRevenueService::isClosed($period),
        ]);
    }

    /**
     * Versi cetak untuk dikirim ke penulis; tanpa identitas pembeli.
     */
    public function report(Request $request, Library $library)
    {
        $period = $this->period($request);

        return view('pages.admin.library-revenue.report', [
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'library' => $library,
            'summary' => $this->service->summaryFor($library, $period),
            'purchases' => $this->service->purchasesFor($library, $period),
        ]);
    }

    public function markPaid(Request $request, Library $library)
    {
        $validated = $request->validate([
            'bulan' => ['required', 'regex:'.LibraryRevenueService::PERIOD_PATTERN],
            'transferred_at' => 'required|date|before_or_equal:today',
            'transfer_proof' => 'required|image|mimes:webp,jpg,jpeg,png|max:2048',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $period = $validated['bulan'];
        $back = redirect()->route('admin.library-revenue.show', [$library, 'bulan' => $period]);

        if (! LibraryRevenueService::isClosed($period)) {
            return $back->withErrors(['bulan' => 'Transfer hanya bisa dicatat untuk bulan yang sudah berakhir.']);
        }

        if ($library->author_share_percent === null || blank($library->payee_name)) {
            return $back->withErrors(['bulan' => 'Isi penerima dan persentase bagi hasil pustaka ini terlebih dahulu.']);
        }

        if ($library->payouts()->where('period', $period)->exists()) {
            return $back->withErrors(['bulan' => 'Transfer untuk periode ini sudah pernah dicatat.']);
        }

        $this->service->markPaid($library, $period, [
            'transferred_at' => $validated['transferred_at'],
            'transfer_proof' => $validated['transfer_proof'],
            'admin_note' => $validated['admin_note'] ?? null,
            'processed_by' => $request->user()->id,
        ]);

        return $back->with('message', 'Transfer bagi hasil dicatat.');
    }

    public function proof(LibraryPayout $payout)
    {
        abort_if(empty($payout->transfer_proof_path) || ! Storage::disk('local')->exists($payout->transfer_proof_path), 404);

        return Storage::disk('local')->response($payout->transfer_proof_path);
    }

    private function periodLabel(string $period): string
    {
        return CarbonImmutable::createFromFormat('!Y-m', $period)->locale('id')->translatedFormat('F Y');
    }

    private function period(Request $request): string
    {
        $period = (string) $request->query('bulan', '');

        return preg_match(LibraryRevenueService::PERIOD_PATTERN, $period) ? $period : LibraryRevenueService::defaultPeriod();
    }
}

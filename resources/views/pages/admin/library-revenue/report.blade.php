<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Laporan Bagi Hasil {{ $library->title }} – {{ $periodLabel }}</title>
    <style>
        body { font-family: system-ui, -apple-system, "Segoe UI", sans-serif; color: #1f2937; margin: 0; background: #f3f4f6; }
        .page { max-width: 720px; margin: 24px auto; background: #fff; padding: 40px; border-radius: 12px; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #6b7280; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 14px; }
        th, td { padding: 8px 4px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        th { font-size: 12px; text-transform: uppercase; color: #6b7280; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .total td { font-weight: 700; border-bottom: none; }
        .status { margin-top: 24px; padding: 12px 16px; border-radius: 8px; font-size: 14px; }
        .paid { background: #ecfdf5; color: #065f46; }
        .unpaid { background: #fffbeb; color: #92400e; }
        .actions { max-width: 720px; margin: 24px auto 0; text-align: right; }
        .actions button { padding: 8px 16px; border: 0; border-radius: 8px; background: #7c3aed; color: #fff; font-size: 14px; cursor: pointer; }
        @media print {
            body { background: #fff; }
            .page { margin: 0; padding: 0; border-radius: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>

    <div class="page">
        <h1>Laporan Bagi Hasil</h1>
        <div class="muted">Syaikhuna · Periode {{ $periodLabel }} · Dibuat {{ now()->locale('id')->translatedFormat('d F Y') }}</div>

        <table>
            <tr><th style="width: 40%">Judul</th><td>{{ $library->title }}</td></tr>
            <tr><th>Penerima</th><td>{{ $summary['payee_name'] ?? '—' }}</td></tr>
            <tr><th>Jumlah pembelian</th><td>{{ $summary['purchase_count'] }}</td></tr>
        </table>

        <table>
            <thead>
                <tr><th>Rincian</th><th class="num">Jumlah</th></tr>
            </thead>
            <tbody>
                <tr><td>Pendapatan kotor</td><td class="num">Rp {{ number_format($summary['gross_amount'], 0, ',', '.') }}</td></tr>
                <tr><td>Bagian platform ({{ 100 - $summary['share_percent'] }}%)</td><td class="num">Rp {{ number_format($summary['platform_amount'], 0, ',', '.') }}</td></tr>
                <tr class="total"><td>Bagian penulis ({{ $summary['share_percent'] }}%)</td><td class="num">Rp {{ number_format($summary['author_amount'], 0, ',', '.') }}</td></tr>
            </tbody>
        </table>

        @if($summary['payout'])
            <div class="status paid">Sudah ditransfer pada {{ $summary['payout']->transferred_at->locale('id')->translatedFormat('d F Y') }}.</div>
        @else
            <div class="status unpaid">Belum ditransfer.</div>
        @endif

        {{-- Rincian transaksi tanpa identitas pembeli. --}}
        <table>
            <thead>
                <tr><th>No</th><th>Tanggal konfirmasi</th><th class="num">Harga</th></tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $purchase->verified_at->locale('id')->translatedFormat('d F Y') }}</td>
                        <td class="num">Rp {{ number_format($purchase->price, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="muted">Tidak ada pembelian pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
    h1 { font-size: 16px; margin-bottom: 2px; }
    p.subtitle { color: #64748b; margin-top: 0; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
    th { background-color: #f1f5f9; font-weight: bold; }
    tr:nth-child(even) { background-color: #f8fafc; }
    .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
    .badge-pemasukan { background-color: #dcfce7; color: #15803d; }
    .badge-pengeluaran { background-color: #fee2e2; color: #b91c1c; }
    .badge-draft { background-color: #f1f5f9; color: #475569; }
    .badge-finalized { background-color: #dbeafe; color: #1d4ed8; }
    .badge-approved { background-color: #dcfce7; color: #15803d; }
    .badge-rejected { background-color: #fee2e2; color: #b91c1c; }
    .summary { margin-top: 16px; width: 320px; }
    .summary td { border: none; padding: 4px 8px; }
    .summary .label { color: #64748b; }
    .summary .value { text-align: right; font-weight: bold; }
    .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>
    <h1>Laporan Keuangan</h1>
    <p class="subtitle">Periode: {{ $monthLabel }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>User</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Catatan Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
            <tr>
                <td>{{ $r->date->format('d-m-Y') }}</td>
                <td>{{ $r->user->name }}</td>
                <td><span class="badge badge-{{ $r->type }}">{{ ucfirst($r->type) }}</span></td>
                <td>{{ $r->category ?? '-' }}</td>
                <td>Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                <td>{{ $r->admin_note ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="7">Tidak ada data keuangan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td class="label">Total Pemasukan (disetujui)</td>
            <td class="value">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Total Pengeluaran (disetujui)</td>
            <td class="value">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Saldo</td>
            <td class="value">Rp {{ number_format($totalIncome - $totalExpense, 0, ',', '.') }}</td>
        </tr>
    </table>

    <p class="footer">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &middot; Workforce Pro</p>
</body>
</html>

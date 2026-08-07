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
    .badge-hadir { background-color: #dcfce7; color: #15803d; }
    .badge-izin { background-color: #dbeafe; color: #1d4ed8; }
    .badge-sakit { background-color: #fef3c7; color: #b45309; }
    .badge-alpa { background-color: #fee2e2; color: #b91c1c; }
    .footer { margin-top: 20px; font-size: 9px; color: #94a3b8; }
</style>
</head>
<body>
    <h1>Laporan Absensi</h1>
    <p class="subtitle">Periode: {{ $monthLabel }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Status</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $r)
            <tr>
                <td>{{ $r->date->format('d-m-Y') }}</td>
                <td>{{ $r->user->name }}</td>
                <td>{{ $r->check_in ?? '-' }}</td>
                <td>{{ $r->check_out ?? '-' }}</td>
                <td><span class="badge badge-{{ $r->status }}">{{ ucfirst($r->status) }}</span></td>
                <td>{{ $r->notes ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="6">Tidak ada data absensi pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Dicetak pada {{ now()->translatedFormat('d F Y, H:i') }} WIB &middot; Workforce Pro</p>
</body>
</html>

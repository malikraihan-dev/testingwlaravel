@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight">Dashboard Admin</h2>
    <p class="text-slate-500">Ringkasan kehadiran tim hari ini</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-slate-900 shadow-sm">
        <div class="flex justify-between items-start mb-2">
            <p class="text-slate-500 text-sm font-semibold">Total User</p>
            <span class="material-symbols-outlined text-slate-900">groups</span>
        </div>
        <h3 class="text-3xl font-bold">{{ $totalUsers }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-emerald-500 shadow-sm">
        <div class="flex justify-between items-start mb-2">
            <p class="text-slate-500 text-sm font-semibold">Absensi Hari Ini</p>
            <span class="material-symbols-outlined text-emerald-500">how_to_reg</span>
        </div>
        <h3 class="text-3xl font-bold">{{ $todayAttendance }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-blue-500 shadow-sm">
        <div class="flex justify-between items-start mb-2">
            <p class="text-slate-500 text-sm font-semibold">Hadir Hari Ini</p>
            <span class="material-symbols-outlined text-blue-500">task_alt</span>
        </div>
        <h3 class="text-3xl font-bold">{{ $todayHadir }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-amber-500 shadow-sm">
        <div class="flex justify-between items-start mb-2">
            <p class="text-slate-500 text-sm font-semibold">Belum Absen</p>
            <span class="material-symbols-outlined text-amber-500">person_off</span>
        </div>
        <h3 class="text-3xl font-bold">{{ max($totalUsers - $todayAttendance, 0) }}</h3>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
    <div class="mb-6">
        <h3 class="text-lg font-bold">Grafik Kehadiran 7 Hari Terakhir</h3>
        <p class="text-slate-500 text-sm">Jumlah status kehadiran per hari</p>
    </div>
    <canvas id="attendanceChart" height="90"></canvas>
</div>

<p class="text-sm text-slate-400 mt-4">
    Tip: klik ikon <span class="material-symbols-outlined text-sm align-middle">auto_awesome</span> di pojok kanan bawah untuk bertanya ke AI soal data kehadiran ini.
</p>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
fetch("{{ route('admin.attendances.chart-data') }}")
    .then(res => res.json())
    .then(data => {
        const ctx = document.getElementById('attendanceChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    { label: 'Hadir', data: data.datasets.hadir, backgroundColor: '#10b981' },
                    { label: 'Izin', data: data.datasets.izin, backgroundColor: '#3b82f6' },
                    { label: 'Sakit', data: data.datasets.sakit, backgroundColor: '#f59e0b' },
                    { label: 'Alpa', data: data.datasets.alpa, backgroundColor: '#ef4444' },
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    });
</script>
@endsection

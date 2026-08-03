@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight">Halo, {{ auth()->user()->name }} 👋</h2>
    <p class="text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
</div>

<div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm mb-8 text-center">
    <h3 id="live-clock" class="text-5xl font-bold tracking-tight mb-1"></h3>
    <p class="text-slate-500 mb-6">Waktu saat ini</p>

    <div class="flex justify-center gap-8 mb-6">
        <div>
            <p class="text-xs text-slate-400 uppercase font-bold">Check-in</p>
            <p class="text-xl font-bold">{{ $todayRecord->check_in ?? '--:--' }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase font-bold">Check-out</p>
            <p class="text-xl font-bold">{{ $todayRecord->check_out ?? '--:--' }}</p>
        </div>
    </div>

    @if(!auth()->user()->hasFaceEnrolled())
        <p class="text-xs text-slate-400 mb-3">
            Belum ada verifikasi wajah.
            <a href="{{ route('attendance.face-enroll') }}" class="underline font-bold">Aktifkan sekarang</a>
        </p>
    @endif

    <div class="flex justify-center gap-3">
        @if($todayRecord)
            <button class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl flex items-center gap-2 opacity-40 cursor-not-allowed" disabled>
                <span class="material-symbols-outlined">check_circle</span> Check-in
            </button>
        @elseif(auth()->user()->hasFaceEnrolled())
            <button id="face-checkin-trigger" class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined">face</span> Check-in (Verifikasi Wajah)
            </button>
        @else
            <form method="POST" action="{{ route('attendance.checkin') }}">
                @csrf
                <button class="px-6 py-3 bg-emerald-600 text-white font-bold rounded-xl flex items-center gap-2">
                    <span class="material-symbols-outlined">check_circle</span> Check-in
                </button>
            </form>
        @endif

        <form method="POST" action="{{ route('attendance.checkout') }}">
            @csrf
            <button class="px-6 py-3 bg-amber-500 text-white font-bold rounded-xl flex items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed" {{ (!$todayRecord || $todayRecord->check_out) ? 'disabled' : '' }}>
                <span class="material-symbols-outlined">logout</span> Check-out
            </button>
        </form>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="p-6 border-b border-slate-200">
        <h3 class="text-lg font-bold">Riwayat Terbaru</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Check-in</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Check-out</th>
                    <th class="px-6 py-3 text-xs font-bold text-slate-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($recentAttendances as $a)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium">{{ $a->date->format('d M Y') }}</td>
                    <td class="px-6 py-4">{{ $a->check_in ?? '--:--' }}</td>
                    <td class="px-6 py-4">{{ $a->check_out ?? '--:--' }}</td>
                    <td class="px-6 py-4">
                        @php
                            $colors = ['hadir' => 'bg-green-100 text-green-700', 'izin' => 'bg-blue-100 text-blue-700', 'sakit' => 'bg-amber-100 text-amber-700', 'alpa' => 'bg-red-100 text-red-700'];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $colors[$a->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($a->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-6 text-center text-slate-400">Belum ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        <a href="{{ route('attendance.index') }}" class="text-sm font-bold text-slate-900 hover:underline">Lihat semua riwayat &rarr;</a>
    </div>
</div>

@if(auth()->user()->hasFaceEnrolled() && !$todayRecord)
    @include('partials.face-checkin-modal')
@endif
@endsection

@section('scripts')
<script>
function updateClock() {
    const now = new Date();
    document.getElementById('live-clock').textContent = now.toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000);
updateClock();
</script>
@endsection

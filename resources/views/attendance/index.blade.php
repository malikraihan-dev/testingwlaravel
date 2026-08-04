@extends('layouts.app')

@section('title', 'Absensi Saya')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold tracking-tight">Absensi Saya</h2>
        <p class="text-slate-500">Riwayat lengkap kehadiran kamu</p>
    </div>
    <div class="flex gap-3 items-center flex-wrap">
        @if(auth()->user()->hasFaceEnrolled())
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                <span class="material-symbols-outlined text-sm">verified_user</span> Verifikasi wajah aktif
            </span>
        @else
            <a href="{{ route('attendance.face-enroll') }}" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm font-bold hover:bg-slate-100 flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">face</span> Daftarkan Wajah Sekarang
            </a>
        @endif

        @if($todayRecord)
            <button class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm opacity-40 cursor-not-allowed" disabled>Check-in</button>
        @elseif(auth()->user()->hasFaceEnrolled())
            <button id="face-checkin-trigger" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">face</span> Check-in (Verifikasi Wajah)
            </button>
        @else
            <form method="POST" action="{{ route('attendance.checkin') }}">
                @csrf
                <button class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm">Check-in</button>
            </form>
        @endif

        <form method="POST" action="{{ route('attendance.checkout') }}">
            @csrf
            <button class="px-5 py-2.5 bg-amber-500 text-white font-bold rounded-xl text-sm disabled:opacity-40" {{ (!$todayRecord || $todayRecord->check_out) ? 'disabled' : '' }}>Check-out</button>
        </form>
    </div>
</div>

@if(!auth()->user()->hasFaceEnrolled() && !$todayRecord)
<div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <p class="font-bold text-amber-800">Kamu belum mendaftarkan wajah</p>
        <p class="text-sm text-amber-700">Daftarkan wajah dulu kalau ingin check-in pakai verifikasi wajah — cuma perlu sekali. Tanpa ini, check-in tetap bisa dilakukan biasa.</p>
    </div>
    <a href="{{ route('attendance.face-enroll') }}" class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm whitespace-nowrap">Daftarkan Wajah</a>
</div>
@endif

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Foto</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Check-in</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Check-out</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($attendances as $a)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-medium">{{ $a->date->format('d M Y') }}</td>
                    <td class="px-4 py-4">
                        @if($a->photo_url)
                            <a href="{{ $a->photo_url }}" target="_blank">
                                <img src="{{ $a->photo_url }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200">
                            </a>
                        @else
                            <span class="text-slate-300 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">{{ $a->check_in ?? '--:--' }}</td>
                    <td class="px-4 py-4">{{ $a->check_out ?? '--:--' }}</td>
                    <td class="px-4 py-4">
                        @php
                            $colors = ['hadir' => 'bg-green-100 text-green-700', 'izin' => 'bg-blue-100 text-blue-700', 'sakit' => 'bg-amber-100 text-amber-700', 'alpa' => 'bg-red-100 text-red-700'];
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $colors[$a->status] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($a->status) }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-500">{{ $a->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $attendances->links() }}
    </div>
</div>

@if(auth()->user()->hasFaceEnrolled() && !$todayRecord)
    @include('partials.face-checkin-modal')
@endif
@endsection

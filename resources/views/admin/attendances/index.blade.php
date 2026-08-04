@extends('layouts.app')

@section('title', 'Kelola Absensi')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight">Kelola Absensi</h2>
    <p class="text-slate-500">Lihat dan kelola data kehadiran semua user</p>
</div>

<div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-6">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <select name="user_id" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm flex-1">
            <option value="">Semua User</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm flex-1">
            <option value="">Semua Status</option>
            @foreach(['hadir','izin','sakit','alpa'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm flex-1">
        <button class="px-6 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm">Filter</button>
    </form>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase">User</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Foto Verifikasi</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Check-in</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Check-out</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($attendances as $a)
                    <tr class="hover:bg-slate-50 align-top">
                        <td class="py-4 px-6 font-medium">{{ $a->user->name }}</td>
                        <td class="py-4 px-4 text-slate-600">{{ $a->date->format('d M Y') }}</td>
                        <td class="py-4 px-4">
                            @if($a->photo_url)
                                <a href="{{ $a->photo_url }}" target="_blank" title="Klik untuk lihat ukuran penuh">
                                    <img src="{{ $a->photo_url }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                                </a>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">Tanpa verifikasi wajah</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-slate-600">{{ $a->check_in ?? '-' }}</td>
                        <td class="py-4 px-4 text-slate-600">{{ $a->check_out ?? '-' }}</td>
                        <td class="py-4 px-4">
                            <form method="POST" action="{{ route('admin.attendances.update', $a) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs">
                                    @foreach(['hadir','izin','sakit','alpa'] as $s)
                                        <option value="{{ $s }}" {{ $a->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                                <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold">Simpan</button>
                            </form>
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <form method="POST" action="{{ route('admin.attendances.destroy', $a) }}" onsubmit="return confirm('Hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-9 h-9 inline-flex items-center justify-center rounded-lg hover:bg-red-100 text-red-600" title="Hapus">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $attendances->links() }}
    </div>
</div>
@endsection

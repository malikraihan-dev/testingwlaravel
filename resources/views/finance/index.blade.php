@extends('layouts.app')

@section('title', 'Keuangan Saya')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold tracking-tight">Keuangan Saya</h2>
        <p class="text-slate-500">Catatan pemasukan &amp; pengeluaran pribadi</p>
    </div>
    <a href="{{ route('finance.create') }}" class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl flex items-center gap-2 text-sm">
        <span class="material-symbols-outlined text-lg">add</span> Tambah Catatan
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-emerald-500 shadow-sm">
        <p class="text-slate-500 text-sm font-semibold mb-1">Total Pemasukan (disetujui)</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-red-500 shadow-sm">
        <p class="text-slate-500 text-sm font-semibold mb-1">Total Pengeluaran (disetujui)</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-amber-500 shadow-sm">
        <p class="text-slate-500 text-sm font-semibold mb-1">Menunggu Review Admin</p>
        <h3 class="text-2xl font-bold">{{ $pendingCount }}</h3>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tanggal</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Tipe</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Kategori</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Jumlah</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">File</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($records as $r)
                @php
                    $statusColors = [
                        'draft' => 'bg-slate-100 text-slate-600',
                        'finalized' => 'bg-blue-100 text-blue-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                    ];
                    $statusLabels = [
                        'draft' => 'Draft',
                        'finalized' => 'Menunggu Review',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ];
                @endphp
                <tr class="hover:bg-slate-50 align-top">
                    <td class="px-6 py-4 font-medium">{{ $r->date->format('d M Y') }}</td>
                    <td class="px-4 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $r->type === 'pemasukan' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($r->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-slate-600">{{ $r->category ?? '-' }}</td>
                    <td class="px-4 py-4 font-bold">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                    <td class="px-4 py-4">
                        @if($r->attachment_path)
                            <a href="{{ route('finance.download', $r) }}" class="text-blue-600 hover:underline text-xs font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">download</span> Unduh
                            </a>
                        @else
                            <span class="text-slate-400 text-xs">Belum ada</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$r->status] }}">{{ $statusLabels[$r->status] }}</span>
                        @if($r->status === 'rejected' && $r->admin_note)
                            <p class="text-xs text-red-500 mt-1 max-w-[160px]">{{ $r->admin_note }}</p>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        @if($r->canBeEditedBy(auth()->user()))
                            <a href="{{ route('finance.edit', $r) }}" class="w-9 h-9 inline-flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            <form method="POST" action="{{ route('finance.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus catatan ini?')">
                                @csrf @method('DELETE')
                                <button class="w-9 h-9 inline-flex items-center justify-center rounded-lg hover:bg-red-100 text-red-600" title="Hapus">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        @endif
                        @if($r->canBeFinalizedBy(auth()->user()))
                            <form method="POST" action="{{ route('finance.finalize', $r) }}" class="inline">
                                @csrf
                                <button class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold">Finalisasi</button>
                            </form>
                        @elseif($r->status === 'draft' && !$r->attachment_path)
                            <p class="text-xs text-slate-400 mt-1">Upload file untuk finalisasi</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">Belum ada catatan keuangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $records->links() }}
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Kelola Keuangan')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight">Kelola Keuangan</h2>
    <p class="text-slate-500">Review, setujui, atau tolak catatan keuangan semua user</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-emerald-500 shadow-sm">
        <p class="text-slate-500 text-sm font-semibold mb-1">Total Pemasukan (disetujui)</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalIncomeApproved, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-red-500 shadow-sm">
        <p class="text-slate-500 text-sm font-semibold mb-1">Total Pengeluaran (disetujui)</p>
        <h3 class="text-2xl font-bold">Rp {{ number_format($totalExpenseApproved, 0, ',', '.') }}</h3>
    </div>
    <div class="bg-white p-5 border border-slate-200 rounded-xl border-t-4 border-t-amber-500 shadow-sm">
        <p class="text-slate-500 text-sm font-semibold mb-1">Menunggu Review</p>
        <h3 class="text-2xl font-bold">{{ $pendingCount }}</h3>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-6">
    <p class="text-xs font-bold text-slate-500 uppercase mb-3">Ekspor Laporan Bulanan</p>
    <div class="flex flex-col md:flex-row gap-3 items-end">
        <div class="flex-1">
            <label class="block text-xs font-bold mb-1">Pilih Bulan</label>
            <input type="month" id="export-month" value="{{ now()->format('Y-m') }}" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm w-full">
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="exportReport('excel')" class="px-5 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">description</span> Unduh Excel
            </button>
            <button type="button" onclick="exportReport('pdf')" class="px-5 py-2.5 bg-red-600 text-white font-bold rounded-xl text-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">picture_as_pdf</span> Unduh PDF
            </button>
        </div>
    </div>
</div>

<div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm mb-6">
    <p class="text-xs font-bold text-slate-500 uppercase mb-3">Filter Tabel</p>
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        <select name="user_id" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm flex-1">
            <option value="">Semua User</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <select name="type" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm flex-1">
            <option value="">Semua Tipe</option>
            <option value="pemasukan" {{ request('type') === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
            <option value="pengeluaran" {{ request('type') === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
        </select>
        <select name="status" class="px-4 py-2.5 border border-slate-300 rounded-xl text-sm flex-1">
            <option value="">Semua Status</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Menunggu Review</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
        </select>
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
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Tipe</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Jumlah</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">File</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
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
                    <td class="py-4 px-6 font-medium">{{ $r->user->name }}</td>
                    <td class="py-4 px-4">{{ $r->date->format('d M Y') }}</td>
                    <td class="py-4 px-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $r->type === 'pemasukan' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($r->type) }}
                        </span>
                    </td>
                    <td class="py-4 px-4 font-bold">Rp {{ number_format($r->amount, 0, ',', '.') }}</td>
                    <td class="py-4 px-4">
                        @if($r->attachment_path)
                            <a href="{{ route('finance.download', $r) }}" class="text-blue-600 hover:underline text-xs font-bold flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">download</span> Unduh
                            </a>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="py-4 px-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusColors[$r->status] }}">{{ $statusLabels[$r->status] }}</span>
                    </td>
                    <td class="py-4 px-6 text-right whitespace-nowrap">
                        @if($r->status === 'finalized')
                            <form method="POST" action="{{ route('admin.finance.approve', $r) }}" class="inline">
                                @csrf @method('PUT')
                                <button class="px-3 py-1.5 bg-emerald-600 text-white rounded-lg text-xs font-bold" title="Setujui">Setujui</button>
                            </form>
                            <button type="button" onclick="rejectRecord({{ $r->id }})" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-bold">Tolak</button>
                            <form id="reject-form-{{ $r->id }}" method="POST" action="{{ route('admin.finance.reject', $r) }}" class="hidden">
                                @csrf @method('PUT')
                                <input type="hidden" name="admin_note" id="reject-note-{{ $r->id }}">
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.finance.destroy', $r) }}" class="inline" onsubmit="return confirm('Hapus data ini secara permanen?')">
                            @csrf @method('DELETE')
                            <button class="w-9 h-9 inline-flex items-center justify-center rounded-lg hover:bg-red-100 text-red-600" title="Hapus">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $records->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
function rejectRecord(id) {
    const note = prompt('Alasan penolakan (wajib diisi):');
    if (note === null) return;
    if (note.trim() === '') {
        alert('Alasan penolakan wajib diisi.');
        return;
    }
    document.getElementById('reject-note-' + id).value = note;
    document.getElementById('reject-form-' + id).submit();
}

function exportReport(type) {
    const month = document.getElementById('export-month').value;
    const url = type === 'excel'
        ? "{{ route('admin.finance.export.excel') }}"
        : "{{ route('admin.finance.export.pdf') }}";
    window.location.href = url + '?month=' + month;
}
</script>
@endsection

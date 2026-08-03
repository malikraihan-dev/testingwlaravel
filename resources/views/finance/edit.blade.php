@extends('layouts.app')

@section('title', 'Edit Catatan Keuangan')

@section('content')
<h2 class="text-2xl font-bold tracking-tight mb-6">Edit Catatan Keuangan</h2>

<div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm max-w-xl">
    @if ($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($finance->status === 'rejected' && $finance->admin_note)
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <p class="font-bold mb-1">Catatan ditolak admin:</p>
            <p>{{ $finance->admin_note }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('finance.update', $finance) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-bold mb-1">Tipe</label>
            <select name="type" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
                <option value="pemasukan" {{ old('type', $finance->type) === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ old('type', $finance->type) === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Kategori <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="category" value="{{ old('category', $finance->category) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Jumlah (Rp)</label>
            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $finance->amount) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ old('date', $finance->date->toDateString()) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">{{ old('description', $finance->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">File Pendukung</label>
            @if($finance->attachment_path)
                <a href="{{ route('finance.download', $finance) }}" class="text-blue-600 hover:underline text-sm flex items-center gap-1 mb-2">
                    <span class="material-symbols-outlined text-sm">description</span> {{ $finance->attachment_original_name ?? 'File saat ini' }}
                </a>
            @endif
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">
            <p class="text-xs text-slate-400 mt-1">Kosongkan jika tidak ingin ganti file.</p>
        </div>
        <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm">Update</button>
            <a href="{{ route('finance.index') }}" class="px-5 py-2.5 border border-slate-300 font-bold rounded-xl text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection

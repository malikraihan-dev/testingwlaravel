@extends('layouts.app')

@section('title', 'Tambah Catatan Keuangan')

@section('content')
<h2 class="text-2xl font-bold tracking-tight mb-6">Tambah Catatan Keuangan</h2>

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

    <form method="POST" action="{{ route('finance.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-bold mb-1">Tipe</label>
            <select name="type" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
                <option value="pemasukan" {{ old('type') === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ old('type') === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Kategori <span class="text-slate-400 font-normal">(opsional)</span></label>
            <input type="text" name="category" value="{{ old('category') }}" placeholder="misal: Gaji, Transport, Makan" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Jumlah (Rp)</label>
            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Tanggal</label>
            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">File Pendukung <span class="text-slate-400 font-normal">(PDF/JPG/PNG, maks 5MB — wajib sebelum finalisasi)</span></label>
            <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">
        </div>
        <p class="text-xs text-slate-400">Catatan akan tersimpan sebagai <strong>draft</strong> dulu. Kamu bisa finalisasi belakangan setelah file pendukung diupload.</p>
        <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm">Simpan sebagai Draft</button>
            <a href="{{ route('finance.index') }}" class="px-5 py-2.5 border border-slate-300 font-bold rounded-xl text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection

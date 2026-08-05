@extends('layouts.app')

@section('title', 'Upload Dokumen')

@section('content')
<h2 class="text-2xl font-bold tracking-tight mb-6">Upload Dokumen</h2>

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

    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-bold mb-1">Judul Dokumen</label>
            <input type="text" name="title" value="{{ old('title') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Deskripsi <span class="text-slate-400 font-normal">(opsional)</span></label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">File</label>
            <input type="file" name="file" accept=".doc,.docx,.pdf,.xls,.xlsx" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl" required>
            <p class="text-xs text-slate-400 mt-1">Format: Word (.doc/.docx), PDF, atau Excel (.xls/.xlsx), maks 10MB.</p>
        </div>
        <p class="text-xs text-slate-400">Setelah dibuat, kamu bisa mengundang user lain untuk berkolaborasi dari halaman detail dokumen.</p>
        <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm">Upload</button>
            <a href="{{ route('documents.index') }}" class="px-5 py-2.5 border border-slate-300 font-bold rounded-xl text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection

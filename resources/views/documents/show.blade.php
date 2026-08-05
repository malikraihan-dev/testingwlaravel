@extends('layouts.app')

@section('title', $document->title)

@section('content')
@php $isOwner = $document->isOwnedBy(auth()->user()); @endphp

<div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6">
    <div>
        <a href="{{ route('documents.index') }}" class="text-sm text-slate-500 hover:underline flex items-center gap-1 mb-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali ke Dokumen
        </a>
        <h2 class="text-2xl font-bold tracking-tight">{{ $document->title }}</h2>
        @if($document->description)
            <p class="text-slate-500 mt-1">{{ $document->description }}</p>
        @endif
        <p class="text-xs text-slate-400 mt-2">Pemilik: <strong>{{ $document->owner->name }}</strong></p>
    </div>
    @if($isOwner)
        <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Hapus dokumen ini beserta semua riwayat versinya?')">
            @csrf @method('DELETE')
            <button class="px-4 py-2.5 border border-red-200 text-red-600 font-bold rounded-xl text-sm hover:bg-red-50 flex items-center gap-2">
                <span class="material-symbols-outlined text-lg">delete</span> Hapus Dokumen
            </button>
        </form>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Upload new version --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="font-bold mb-3">Upload Versi Baru</h3>
            <form method="POST" action="{{ route('documents.versions.store', $document) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="file" name="file" accept=".doc,.docx,.pdf,.xls,.xlsx" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl text-sm" required>
                <button class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm whitespace-nowrap">Upload</button>
            </form>
            @error('file')
                <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
            @enderror
        </div>

        {{-- Version history --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-slate-200">
                <h3 class="font-bold">Riwayat Versi</h3>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($document->versions as $v)
                <div class="p-4 px-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                            <span class="material-symbols-outlined">description</span>
                        </div>
                        <div>
                            <p class="font-bold text-sm">Versi {{ $v->version_number }} <span class="text-xs text-slate-400 uppercase">({{ $v->extension }})</span></p>
                            <p class="text-xs text-slate-500">{{ $v->original_name }} &middot; oleh {{ $v->uploader->name }} &middot; {{ $v->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <a href="{{ route('documents.versions.download', $v) }}" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-bold hover:bg-slate-100 flex items-center gap-1">
                        <span class="material-symbols-outlined text-sm">download</span> Unduh
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Collaborators --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="font-bold mb-3">Kolaborator</h3>

            <div class="space-y-2 mb-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-slate-900 text-white text-xs flex items-center justify-center font-bold">
                            {{ strtoupper(substr($document->owner->name, 0, 1)) }}
                        </div>
                        {{ $document->owner->name }}
                    </span>
                    <span class="text-xs text-slate-400 font-bold">Pemilik</span>
                </div>

                @foreach($document->collaborators as $c)
                <div class="flex items-center justify-between text-sm">
                    <span class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs flex items-center justify-center font-bold">
                            {{ strtoupper(substr($c->name, 0, 1)) }}
                        </div>
                        {{ $c->name }}
                    </span>
                    @if($isOwner)
                        <form method="POST" action="{{ route('documents.collaborators.destroy', [$document, $c]) }}" onsubmit="return confirm('Keluarkan {{ $c->name }} dari dokumen ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-500 hover:underline text-xs font-bold">Keluarkan</button>
                        </form>
                    @endif
                </div>
                @endforeach
            </div>

            @if($isOwner)
                <form method="POST" action="{{ route('documents.collaborators.store', $document) }}" class="flex gap-2 pt-3 border-t border-slate-100">
                    @csrf
                    <select name="user_id" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm" required>
                        <option value="">Pilih user...</option>
                        @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <button class="px-3 py-2 bg-slate-900 text-white rounded-lg text-sm font-bold">+</button>
                </form>
            @else
                <p class="text-xs text-slate-400 pt-3 border-t border-slate-100">Hanya pemilik dokumen yang bisa mengelola kolaborator.</p>
            @endif
        </div>

        {{-- Edit info (owner only) --}}
        @if($isOwner)
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="font-bold mb-3">Edit Info Dokumen</h3>
            <form method="POST" action="{{ route('documents.update', $document) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-bold mb-1">Judul</label>
                    <input type="text" name="title" value="{{ $document->title }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ $document->description }}</textarea>
                </div>
                <button class="w-full px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-bold">Simpan</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection

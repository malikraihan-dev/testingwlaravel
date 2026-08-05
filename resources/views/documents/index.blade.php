@extends('layouts.app')

@section('title', 'Dokumen')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold tracking-tight">Dokumen</h2>
        <p class="text-slate-500">Dokumen kerja milikmu dan yang kamu ikut kolaborasi</p>
    </div>
    <a href="{{ route('documents.create') }}" class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl flex items-center gap-2 text-sm">
        <span class="material-symbols-outlined text-lg">upload_file</span> Upload Dokumen
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Judul</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Pemilik</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Versi Terbaru</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Kolaborator</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Update Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documents as $doc)
                <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('documents.show', $doc) }}'">
                    <td class="px-6 py-4">
                        <p class="font-bold">{{ $doc->title }}</p>
                        @if($doc->description)
                            <p class="text-xs text-slate-500 truncate max-w-xs">{{ $doc->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-4 text-slate-600">{{ $doc->owner->name }}</td>
                    <td class="px-4 py-4">
                        @if($doc->latestVersion)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold uppercase">
                                <span class="material-symbols-outlined text-sm">description</span>
                                v{{ $doc->latestVersion->version_number }} · {{ $doc->latestVersion->extension }}
                            </span>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex -space-x-2">
                            @foreach($doc->collaborators->take(4) as $c)
                                <div class="w-7 h-7 rounded-full bg-slate-900 text-white text-xs flex items-center justify-center font-bold border-2 border-white" title="{{ $c->name }}">
                                    {{ strtoupper(substr($c->name, 0, 1)) }}
                                </div>
                            @endforeach
                            @if($doc->collaborators->isEmpty())
                                <span class="text-slate-400 text-xs">Belum ada</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-4 text-slate-500 text-sm">{{ $doc->updated_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400">Belum ada dokumen. Upload dokumen pertamamu.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $documents->links() }}
    </div>
</div>
@endsection

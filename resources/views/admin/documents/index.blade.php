@extends('layouts.app')

@section('title', 'Kelola Dokumen')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight">Kelola Dokumen</h2>
    <p class="text-slate-500">Pantau semua dokumen yang diupload user</p>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Judul</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Pemilik</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Versi</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Kolaborator</th>
                    <th class="px-4 py-4 text-xs font-bold text-slate-500 uppercase">Update Terakhir</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documents as $doc)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4 font-bold">{{ $doc->title }}</td>
                    <td class="px-4 py-4 text-slate-600">{{ $doc->owner->name }}</td>
                    <td class="px-4 py-4">
                        @if($doc->latestVersion)
                            v{{ $doc->latestVersion->version_number }} ({{ $doc->latestVersion->extension }})
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-4">{{ $doc->collaborators->count() }} orang</td>
                    <td class="px-4 py-4 text-slate-500 text-sm">{{ $doc->updated_at->diffForHumans() }}</td>
                    <td class="px-6 py-4 text-right">
                        <form method="POST" action="{{ route('admin.documents.destroy', $doc) }}" onsubmit="return confirm('Hapus dokumen ini secara permanen?')">
                            @csrf @method('DELETE')
                            <button class="w-9 h-9 inline-flex items-center justify-center rounded-lg hover:bg-red-100 text-red-600" title="Hapus">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">Belum ada dokumen.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $documents->links() }}
    </div>
</div>
@endsection

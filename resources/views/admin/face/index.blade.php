@extends('layouts.app')

@section('title', 'Kelola Wajah')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold tracking-tight">Kelola Verifikasi Wajah</h2>
    <p class="text-slate-500">Lihat, ganti, atau hapus data wajah setiap user</p>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase">Foto</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">User</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50">
                    <td class="py-4 px-6">
                        @if($user->face_photo_url)
                            <img src="{{ $user->face_photo_url }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined">face</span>
                            </div>
                        @endif
                    </td>
                    <td class="py-4 px-4">
                        <p class="font-bold">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    </td>
                    <td class="py-4 px-4">
                        @if($user->hasFaceEnrolled())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-bold">
                                <span class="material-symbols-outlined text-sm">verified_user</span> Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-bold">Belum daftar</span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-right whitespace-nowrap">
                        <a href="{{ route('admin.face.edit', $user) }}" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-bold hover:bg-slate-100 inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm">edit</span> {{ $user->hasFaceEnrolled() ? 'Ganti Foto' : 'Daftarkan' }}
                        </a>
                        @if($user->hasFaceEnrolled())
                            <form method="POST" action="{{ route('admin.face.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus data wajah {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-bold hover:bg-red-100">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

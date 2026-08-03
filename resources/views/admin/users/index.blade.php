@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-bold tracking-tight">Kelola User</h2>
        <p class="text-slate-500">Mengelola {{ $users->total() }} user terdaftar</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl flex items-center gap-2 shadow-sm hover:opacity-90 transition text-sm">
        <span class="material-symbols-outlined text-lg">person_add</span> Tambah User
    </a>
</div>

<div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase">User</th>
                    <th class="py-4 px-4 text-xs font-bold text-slate-500 uppercase">Role</th>
                    <th class="py-4 px-6 text-xs font-bold text-slate-500 uppercase text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $user)
                <tr class="hover:bg-slate-50 transition-colors group">
                    <td class="py-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold shrink-0">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-bold">{{ $user->name }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="py-4 px-4">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $user->role === 'admin' ? 'bg-slate-900 text-white' : 'bg-blue-100 text-blue-700' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Yakin hapus user ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-red-100 text-red-600" title="Hapus">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-slate-200">
        {{ $users->links() }}
    </div>
</div>
@endsection

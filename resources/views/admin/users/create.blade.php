@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<h2 class="text-2xl font-bold tracking-tight mb-6">Tambah User</h2>

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

    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-bold mb-1">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:outline-none" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:outline-none" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Password</label>
            <input type="password" name="password" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:outline-none" required>
        </div>
        <div>
            <label class="block text-sm font-bold mb-1">Role</label>
            <select name="role" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:outline-none" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 bg-slate-900 text-white font-bold rounded-xl text-sm">Simpan</button>
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 border border-slate-300 font-bold rounded-xl text-sm">Batal</a>
        </div>
    </form>
</div>
@endsection

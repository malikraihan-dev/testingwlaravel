@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-md px-4">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8">
        <h1 class="text-2xl font-bold text-center mb-1">Workforce Pro</h1>
        <p class="text-slate-500 text-center mb-6 text-sm">Masuk ke akun kamu</p>

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:outline-none" required autofocus>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-slate-900 focus:outline-none" required>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300">
                <label for="remember" class="text-sm">Ingat saya</label>
            </div>
            <button class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl">Login</button>
        </form>

        <p class="text-xs text-slate-400 text-center mt-6">
            Admin default: admin@example.com / password123
        </p>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-4xl mx-4 grid grid-cols-1 md:grid-cols-2 rounded-2xl overflow-hidden border border-slate-200 shadow-xl min-h-[560px]">

    {{-- Left panel: branding + feature highlights --}}
    <div class="hidden md:flex flex-col justify-between bg-slate-900 text-white p-10">
        <div>
            <div class="flex items-center gap-2.5 mb-10">
                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-slate-900 text-lg">work</span>
                </div>
                <span class="font-bold">Workforce Pro</span>
            </div>
            <h2 class="text-2xl font-bold leading-snug mb-3">Satu portal untuk seluruh tim kamu.</h2>
            <p class="text-sm text-slate-400 leading-relaxed">Absensi, keuangan, dan dokumen kerja dalam satu tempat.</p>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 text-lg">face</span>
                <span class="text-sm text-slate-400">Check-in dengan verifikasi wajah</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 text-lg">account_balance_wallet</span>
                <span class="text-sm text-slate-400">Kelola keuangan dengan approval</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-slate-400 text-lg">folder_open</span>
                <span class="text-sm text-slate-400">Dokumen kerja bareng tim</span>
            </div>
        </div>
    </div>

    {{-- Right panel: login form --}}
    <div class="bg-white flex flex-col justify-center p-8 sm:p-10">
        <div class="max-w-xs mx-auto w-full">
            <h1 class="text-xl font-bold mb-1">Masuk ke akun kamu</h1>
            <p class="text-sm text-slate-500 mb-6">Selamat datang kembali.</p>

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
                    <label class="block text-xs font-bold mb-1.5">Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@perusahaan.com"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none"
                            required autofocus>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold mb-1.5">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                        <input id="password-input" type="password" name="password" placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-slate-900 focus:outline-none"
                            required>
                        <button type="button" id="toggle-password" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span id="toggle-password-icon" class="material-symbols-outlined text-lg">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="rounded border-slate-300 mr-2">
                    <label for="remember" class="text-sm text-slate-600">Ingat saya</label>
                </div>

                <button class="w-full py-2.5 bg-slate-900 text-white text-sm font-bold rounded-xl hover:bg-slate-800 transition-colors">Login</button>
            </form>

            <div class="mt-6 px-3 py-2.5 bg-slate-50 rounded-xl flex items-center gap-2">
                <span class="material-symbols-outlined text-slate-400 text-base">info</span>
                <span class="text-xs text-slate-500">Admin default: admin@example.com / password123</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const toggleBtn = document.getElementById('toggle-password');
const passwordInput = document.getElementById('password-input');
const toggleIcon = document.getElementById('toggle-password-icon');

toggleBtn.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    toggleIcon.textContent = isPassword ? 'visibility_off' : 'visibility';
});
</script>
@endsection

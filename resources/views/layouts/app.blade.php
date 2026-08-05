<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Workforce Pro')</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; vertical-align: middle; }
</style>
</head>
<body class="bg-slate-50 text-slate-900">

@auth
<aside class="hidden lg:flex flex-col fixed left-0 top-0 h-full w-64 bg-white border-r border-slate-200 z-50 overflow-y-auto">
    <div class="p-6">
        <h1 class="text-2xl font-bold text-slate-900">Workforce Pro</h1>
        <p class="text-xs text-slate-500 mt-1">Management Portal</p>
    </div>
    <nav class="flex-1 px-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-sm">Dashboard</span>
        </a>
        @unless(auth()->user()->isAdmin())
        <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('attendance.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">calendar_month</span>
            <span class="text-sm">Absensi Saya</span>
        </a>
        @endunless

        <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('finance.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">payments</span>
            <span class="text-sm">Keuangan Saya</span>
        </a>

        <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('documents.*') && !request()->routeIs('admin.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">folder_open</span>
            <span class="text-sm">Dokumen</span>
        </a>

        @if(auth()->user()->isAdmin())
        <p class="px-4 pt-4 pb-1 text-xs font-bold text-slate-400 uppercase">Admin</p>
        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">badge</span>
            <span class="text-sm">Kelola User</span>
        </a>
        <a href="{{ route('admin.attendances.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('admin.attendances.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">calendar_month</span>
            <span class="text-sm">Kelola Absensi</span>
        </a>
        <a href="{{ route('admin.face.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('admin.face.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">face</span>
            <span class="text-sm">Kelola Wajah</span>
        </a>
        <a href="{{ route('admin.finance.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('admin.finance.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">account_balance_wallet</span>
            <span class="text-sm">Kelola Keuangan</span>
        </a>
        <a href="{{ route('admin.documents.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg font-bold transition-colors {{ request()->routeIs('admin.documents.*') ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
            <span class="material-symbols-outlined">folder_managed</span>
            <span class="text-sm">Kelola Dokumen</span>
        </a>
        @endif
    </nav>
    <div class="p-4 border-t border-slate-200">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-600 hover:bg-slate-100 font-bold text-sm">
                <span class="material-symbols-outlined">logout</span> Logout
            </button>
        </form>
    </div>
</aside>

<main class="lg:ml-64 min-h-screen">
    <header class="bg-white border-b border-slate-200 h-16 flex justify-between items-center px-6 sticky top-0 z-40">
        <div class="lg:hidden font-bold">Workforce Pro</div>
        <div class="ml-auto flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold leading-none">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </header>

    <div class="p-6 max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-lg text-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-lg text-sm font-medium">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</main>

@if(auth()->user()->isAdmin())
    @include('partials.ai-chat-widget')
@endif
@endauth

@guest
    <div class="min-h-screen flex items-center justify-center">
        @yield('content')
    </div>
@endguest

@yield('scripts')
</body>
</html>

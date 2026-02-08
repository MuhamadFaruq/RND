<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Duniatex RND System</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-900">
    <div class="min-h-screen flex flex-col md:flex-row">
        
        <aside class="w-full md:w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col">
            <div class="p-6 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}" class="h-8 rounded-lg" alt="Logo">
                    <span class="font-black italic tracking-tighter uppercase text-xl text-red-600">Duniatex</span>
                </div>
            </div>

            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white' : 'text-slate-400' }}">
                    🏠 Dashboard
                </a>

                @if(auth()->user()->role === 'marketing')
                    <div class="pt-4 pb-2 px-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Marketing Menu</div>
                    <a href="{{ route('marketing.dashboard') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('marketing.dashboard') ? 'bg-red-600' : 'text-slate-400' }}">📊 Overview</a>
                    <a href="{{ route('marketing.orders.index') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('marketing.orders.*') ? 'bg-red-600' : 'text-slate-400' }}">📋 Order List</a>
                @endif

                @if(auth()->user()->role === 'operator')
                    <div class="pt-4 pb-2 px-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Production Menu</div>
                    <a href="{{ route('operator.logbook') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('operator.logbook') ? 'bg-red-600' : 'text-slate-400' }}">📔 Logbook</a>
                    <a href="{{ route('operator.knitting') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('operator.knitting') ? 'bg-red-600' : 'text-slate-400' }}">🧶 Knitting</a>
                    <a href="{{ route('operator.dyeing') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('operator.dyeing') ? 'bg-red-600' : 'text-slate-400' }}">🧪 Dyeing</a>
                @endif

                @if(auth()->user()->role === 'admin')
                    <div class="pt-4 pb-2 px-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Admin Control</div>
                    <a href="{{ route('admin.users') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('admin.users') ? 'bg-red-600' : 'text-slate-400' }}">👥 Users</a>
                    <a href="{{ route('admin.divisions') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('admin.divisions') ? 'bg-red-600' : 'text-slate-400' }}">🏢 Divisions</a>
                    <a href="{{ route('admin.monitoring') }}" class="block px-4 py-3 rounded-xl font-bold hover:bg-slate-800 transition {{ request()->routeIs('admin.monitoring') ? 'bg-red-600' : 'text-slate-400' }}">🖥️ Live Monitor</a>
                @endif
            </nav>

            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 rounded-xl font-black uppercase italic tracking-tighter text-red-500 hover:bg-red-500/10 transition">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 h-screen overflow-y-auto">
            <header class="bg-white border-b border-slate-200 p-4 sticky top-0 z-30 flex justify-between items-center md:hidden">
                <span class="font-black italic text-red-600 uppercase">Duniatex</span>
                <span class="text-xs font-bold text-slate-500 uppercase">{{ auth()->user()->name }}</span>
            </header>

            <div class="p-4 md:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
</body>
</html>
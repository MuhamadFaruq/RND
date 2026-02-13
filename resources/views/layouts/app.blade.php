<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duniatex RND System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    @livewireStyles
</head>
<body class="font-sans antialiased bg-slate-100">
    <div class="min-h-screen">
        <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-extrabold tracking-wider text-red-600">
                                RND <span class="text-white">DUNIATEX</span>
                            </a>
                        </div>

                        <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex items-center">
                            <a href="{{ route('dashboard') }}" 
                            class="px-3 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                Dashboard
                            </a>

                            @if(Auth::user()->role === 'super_admin')
                                <a href="{{ route('admin.users') }}" 
                                class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.users') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                    Users Management
                                </a>

                                <a href="#" 
                                class="px-3 py-2 rounded-md text-sm font-bold transition text-slate-300 hover:text-white hover:bg-slate-800">
                                    Monitoring Admin
                                </a>
                            @endif

                            @if(Auth::user()->role === 'marketing')
                                <a href="{{ route('marketing.orders.index') }}" class="text-slate-300 hover:text-white px-3 py-2 text-sm font-bold">
                                    Order Entry
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="flex items-center gap-4">
                            <span class="text-slate-400 text-sm italic">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bg-slate-800 text-slate-300 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-md text-xs font-bold transition">
                                    LOGOUT
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        @if (isset($header))
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duniatex Production System</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-slate-900">
    <div class="relative flex items-top justify-center min-h-screen sm:items-center sm:pt-0">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 text-center">
            <img src="{{ asset('images/logo.jpg') }}" class="h-32 mx-auto mb-8 rounded-2xl shadow-2xl" alt="Logo">
            
            <h1 class="text-4xl font-black italic uppercase tracking-tighter text-white mb-4">
                Production <span class="text-red-600">RND System</span>
            </h1>
            
            <div class="mt-8 flex justify-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-red-600 text-white px-8 py-3 rounded-2xl font-black uppercase italic transition hover:bg-red-700">Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-slate-900 px-8 py-3 rounded-2xl font-black uppercase italic transition hover:bg-slate-200">Log in</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-slate-800 text-white px-8 py-3 rounded-2xl font-black uppercase italic transition hover:bg-slate-700">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </div>
</body>
</html>
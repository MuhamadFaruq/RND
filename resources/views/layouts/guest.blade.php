<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Duniatex RND') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body class="font-sans antialiased">
        {{-- 
            PENTING: Hapus class bg-slate-100 dan kotak putih di sini. 
            Biarkan file login.blade.php yang menangani background dan card-nya.
        --}}
        <div class="min-h-screen border-none outline-none">
            {{ $slot }}
        </div>
    </body>
</html>
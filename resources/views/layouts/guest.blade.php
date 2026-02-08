<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Duniatex RND') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,900&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-slate-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#f8fafc]">
            <div class="mb-6">
                <a href="/">
                    <img src="{{ asset('images/logo.jpg') }}" class="h-20 w-auto rounded-2xl shadow-lg border-4 border-white" alt="Duniatex Logo">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-10 py-12 bg-white shadow-[0_20px_50px_rgba(0,0,0,0.05)] overflow-hidden sm:rounded-[3rem] border border-slate-100">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    &copy; {{ date('Y') }} Duniatex Group - Production RND System
                </p>
            </div>
        </div>
    </body>
</html>
@extends('layouts.app')

@section('title', 'System Health — SIPEKA')

@section('content')
<div class="min-h-screen bg-slate-950 text-white p-6 md:p-10 italic font-sans">
    <div class="max-w-6xl mx-auto">

        {{-- HEADER --}}
        <div class="mb-10">
            <h1 class="text-4xl font-black uppercase tracking-tighter text-red-500 leading-none">
                System <span class="text-white">Health</span>
            </h1>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-2">
                SIPEKA — Server Status Monitor
            </p>
        </div>

        {{-- STORAGE CARD --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-[2rem] p-7">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Storage Used</p>
                <p class="text-4xl font-black text-red-500">{{ $storage['used'] }} <span class="text-lg text-slate-500">GB</span></p>
                <div class="mt-4 h-2 bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full rounded-full @if($storage['percentage'] > 80) bg-red-500 @elseif($storage['percentage'] > 60) bg-amber-500 @else bg-emerald-500 @endif"
                         style="width: {{ $storage['percentage'] }}%"></div>
                </div>
                <p class="text-[9px] text-slate-500 mt-2 font-bold uppercase">
                    {{ $storage['percentage'] }}% — {{ $storage['free'] }} GB free of {{ $storage['total'] }} GB
                </p>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-[2rem] p-7">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">PHP Version</p>
                <p class="text-4xl font-black text-emerald-400">{{ $php_version }}</p>
                <p class="text-[9px] text-slate-500 mt-2 font-bold uppercase">Runtime Environment</p>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-[2rem] p-7">
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Laravel Version</p>
                <p class="text-4xl font-black mkt-text">{{ $laravel_version }}</p>
                <p class="text-[9px] text-slate-500 mt-2 font-bold uppercase">Framework Version</p>
            </div>
        </div>

        {{-- SERVER INFO --}}
        <div class="bg-slate-900 border border-slate-800 rounded-[2rem] p-7 mb-8">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 mb-5">Server Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex justify-between border-b border-slate-800 pb-3">
                    <span class="text-[10px] font-black text-slate-500 uppercase">Server Time</span>
                    <span class="text-[10px] font-bold text-white">{{ $server_time }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-3">
                    <span class="text-[10px] font-black text-slate-500 uppercase">DB Driver</span>
                    <span class="text-[10px] font-bold text-emerald-400 uppercase">{{ config('database.default') }}</span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-3">
                    <span class="text-[10px] font-black text-slate-500 uppercase">App Environment</span>
                    <span class="text-[10px] font-bold @if(app()->environment('production')) text-red-400 @else text-amber-400 @endif uppercase">
                        {{ app()->environment() }}
                    </span>
                </div>
                <div class="flex justify-between border-b border-slate-800 pb-3">
                    <span class="text-[10px] font-black text-slate-500 uppercase">Debug Mode</span>
                    <span class="text-[10px] font-bold @if(config('app.debug')) text-red-400 @else text-emerald-400 @endif uppercase">
                        {{ config('app.debug') ? 'ON (WARNING!)' : 'OFF (OK)' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- BACK BUTTON --}}
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-2 bg-slate-800 hover:bg-red-600 text-white px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all">
            ← Kembali
        </a>

    </div>
</div>
@endsection

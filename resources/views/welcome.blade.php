<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duniatex Production System</title>
    @vite(['resources/css/app.css'])
</head>
<body class="antialiased bg-black flex items-center justify-center min-h-screen overflow-hidden selection:bg-red-600 selection:text-white">
    
    {{-- 1. CLEAN BACKGROUND LAYER --}}
    <div class="absolute inset-0 z-0 overflow-hidden">
        {{-- Background Utama: Menggunakan scale agar tidak ada garis putih di pinggir saat diblur --}}
        <div class="absolute inset-0 scale-110">
            <img src="{{ asset('images/bg.jpg') }}" 
                 class="w-full h-full object-cover object-center" 
                 alt="Background Pabrik">
        </div>
        
        {{-- 
            PERBAIKAN UTAMA: BACKDROP BLUR 
            Menggunakan backdrop-blur-xl untuk hasil halus seperti contoh yang Anda kirim
        --}}
        <div class="absolute inset-0 backdrop-blur-sm bg-black/60 shadow-inner"></div>
        
        {{-- Gradasi Gelap (Vignette) untuk memfokuskan mata ke tengah --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/80"></div>
    </div>

    {{-- 2. MAIN INTERFACE --}}
    <div class="relative z-10 max-w-8xl mx-auto px-4 text-center">
        
        {{-- LOGO SECTION: Clean Floating --}}
        <div class="relative inline-block mb-12 group">
            {{-- Efek Glow di belakang logo --}}
            <div class="absolute inset-0 bg-red-600/20 blur-3xl rounded-full scale-150"></div>
            <img src="{{ asset('images/lg.png') }}" 
                 {{-- PERBAIKAN: Height disesuaikan agar proporsional di mobile & desktop --}}
                 class="relative h-32 md:h-40 w-auto object-contain drop-shadow-[0_20px_20px_rgba(0,0,0,0.8)] transition-transform duration-700 group-hover:scale-105" 
                 alt="Duniatex Logo">
        </div>
        
        {{-- HEADLINE SECTION --}}
        <div class="space-y-6">
            <div class="inline-block px-5 py-1.5 bg-red-600/10 border border-red-600/30 rounded-full mb-2 backdrop-blur-md">
                <span class="text-red-500 text-[10px] font-black tracking-[0.4em] uppercase italic">Internal Network Platform</span>
            </div>
            
            <h1 class="text-6xl md:text-8xl font-black italic uppercase tracking-tighter text-white leading-tight">
                PRODUCTION <br>
                <span class="text-red-600 drop-shadow-[0_4px_10px_rgba(0,0,0,0.5)]">RND SYSTEM</span>
            </h1>
            
            <p class="text-slate-300 font-bold tracking-[0.3em] uppercase text-[10px] md:text-xs max-w-2xl mx-auto leading-relaxed opacity-90">
                Integrated Manufacturing Intelligence & <br> Data Integrity Management
            </p>
        </div>
        
        {{-- ACTION BUTTONS --}}
        <div class="mt-16 flex flex-col sm:flex-row justify-center items-center gap-8">
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="group relative px-12 py-4 bg-red-600 text-white font-black uppercase italic tracking-widest overflow-hidden rounded-sm transition-all hover:bg-red-500 hover:shadow-[0_0_50px_rgba(220,38,38,0.4)] active:scale-95">
                        <span class="relative z-10">ENTER DASHBOARD _</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="group relative px-16 py-4 bg-white rounded-2xl text-black font-black uppercase italic tracking-widest transition-all hover:bg-slate-100 hover:scale-105 shadow-2xl active:scale-95">
                        Access System
                    </a>
                @endauth
            @endif
        </div>

        {{-- 3. SYSTEM FOOTER: Professional Status Indicator --}}
        <div class="mt-8 pt-8 border-t border-white/10 flex flex-col md:flex-row justify-center items-center gap-6 md:gap-12 opacity-50">
            <div class="flex items-center">
                <span class="text-[10px] font-mono text-slate-900 uppercase tracking-widest italic">Duniatex Group &copy; 2026</span>
            </div>
        </div>
    </div>
</body>
</html>
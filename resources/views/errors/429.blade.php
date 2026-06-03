<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keamanan Terdeteksi | Duniatex RND</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            overflow: hidden; 
            background-color: #0b0f19;
        }
        .glow-red { filter: drop-shadow(0 0 15px rgba(237, 28, 36, 0.4)); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 md:p-6 overflow-hidden">
    {{-- Background Layer --}}
    <div class="absolute inset-0 z-0 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-tr from-brand-950/20 via-transparent to-black"></div>
        <div class="absolute bottom-0 left-0 right-0 h-96 bg-gradient-to-t from-brand-900/10 to-transparent"></div>
        {{-- Animated Grid --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(#ED1C24 0.5px, transparent 0.5px); background-size: 24px 24px;"></div>
    </div>

    <div class="relative z-10 text-center max-w-lg w-full animate-in fade-in zoom-in duration-1000 px-4">
        {{-- Animated Icon --}}
        <div class="inline-block relative mb-6 md:mb-10 group">
            <div class="absolute inset-0 bg-brand-600 blur-[60px] opacity-20 group-hover:opacity-40 transition-opacity animate-pulse"></div>
            <div class="relative bg-brand-950/30 p-6 md:p-10 rounded-[2.5rem] md:rounded-[3.5rem] border border-brand-500/30 backdrop-blur-xl glow-red">
                <svg class="w-16 h-16 md:w-24 md:h-24 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-4xl md:text-7xl font-black italic uppercase tracking-tighter text-white mb-4 leading-none">
            ACCESS <span class="text-brand-600">LOCKED</span>
        </h1>
        
        <p class="text-slate-400 font-bold uppercase tracking-[0.15em] md:tracking-[0.3em] italic text-[10px] md:text-xs mb-10 leading-relaxed max-w-md mx-auto">
            Terlalu banyak upaya login terdeteksi.<br class="hidden md:block">Akses Anda dibatasi sementara demi keamanan.
        </p>

        {{-- COUNTDOWN SECTION --}}
        <div class="mb-20">
            <p class="text-[9px] md:text-[11px] text-slate-500 font-black mb-3 uppercase tracking-[0.4em] italic leading-none">Akses Terbuka Kembali Dalam:</p>
            <div class="relative inline-block">
                <div id="countdown" class="text-7xl md:text-9xl font-black italic text-white tracking-tighter glow-red leading-none">
                    {{ $seconds ?? '60' }}
                </div>
            </div>
            <p class="text-[10px] md:text-xs text-brand-600 font-black uppercase mt-4 tracking-[0.3em] italic">Detik</p>
        </div>

        <div class="text-[9px] md:text-[11px] font-black text-slate-800 dark:text-slate-800 uppercase tracking-[0.4em] md:tracking-[0.8em] italic leading-none transition-colors">
            DUNIATEX <span class="mx-1">&bull;</span> RND SYSTEM <span class="mx-1">&bull;</span> 2026
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let seconds = parseInt(document.getElementById('countdown').innerText);
            const countdownEl = document.getElementById('countdown');
            
            if (isNaN(seconds)) seconds = 60;

            const timer = setInterval(() => {
                seconds--;
                if (seconds < 0) {
                    clearInterval(timer);
                    window.location.href = '{{ route('login') }}';
                    return;
                }
                countdownEl.innerText = seconds.toString().padStart(2, '0');
            }, 1000);
        });
    </script>

    {{-- Scanline Effect --}}
    <div class="fixed inset-0 pointer-events-none opacity-[0.02] bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] z-[100] bg-[length:100%_2px,3px_100%]"></div>
</body>
</html>

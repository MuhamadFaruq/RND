<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Maintenance | Duniatex RND</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #0f172a; overflow: hidden; }
        .glow { filter: drop-shadow(0 0 20px rgba(220, 38, 38, 0.4)); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-6">
    {{-- Background Layer --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-tr from-red-600/10 via-transparent to-black"></div>
        <div class="absolute bottom-0 left-0 right-0 h-64 bg-gradient-to-t from-red-600/10 to-transparent"></div>
    </div>

    <div class="relative z-10 text-center max-w-2xl animate-in fade-in zoom-in duration-1000">
        {{-- Animated Icon --}}
        <div class="inline-block relative mb-8">
            <div class="absolute inset-0 bg-red-600 blur-3xl opacity-20 animate-pulse"></div>
            <div class="relative bg-red-600/20 p-8 rounded-[3rem] border border-red-600/30">
                <svg class="w-20 h-20 text-red-600 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-6xl font-black italic uppercase tracking-tighter text-white mb-4">
            UNDER <span class="text-red-600">MAINTENANCE</span>
        </h1>
        
        <p class="text-slate-400 font-bold uppercase tracking-[0.2em] italic text-sm mb-10 leading-relaxed">
            Sistem sedang dioptimasi oleh tim IT RND.<br>Mohon tunggu beberapa saat untuk pengalaman yang lebih baik.
        </p>

        <div class="grid grid-cols-2 gap-4 max-w-sm mx-auto mb-12">
            <div class="bg-white/5 border border-white/10 p-4 rounded-3xl">
                <div class="text-xs text-slate-500 font-black mb-1 uppercase tracking-widest">STATUS</div>
                <div class="text-amber-500 font-black italic">UPDATING</div>
            </div>
            <div class="bg-white/5 border border-white/10 p-4 rounded-3xl">
                <div class="text-xs text-slate-500 font-black mb-1 uppercase tracking-widest">DEPT</div>
                <div class="text-red-500 font-black italic">RND IT</div>
            </div>
        </div>

        {{-- Logout Button for Trapped Users --}}
        @if(auth()->check())
            <form method="POST" action="{{ route('logout') }}" class="mb-12">
                @csrf
                <button type="submit" class="group flex items-center gap-3 mx-auto px-6 py-3 rounded-2xl bg-white/5 border border-white/10 text-slate-400 hover:bg-red-600 hover:text-white hover:border-red-500 transition-all duration-300">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="text-[10px] font-black uppercase tracking-widest italic">Keluar dari Sesi</span>
                </button>
            </form>
        @endif

        <div class="flex items-center justify-center gap-2 mb-12 opacity-50">
            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></div>
            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-[0.3em] italic">Auto-detecting system status...</span>
        </div>

        <div class="text-[10px] font-black text-slate-700 uppercase tracking-[0.5em] italic">
            DUNIATEX &bull; RND SYSTEM &bull; 2026
        </div>
    </div>

    {{-- Auto Recovery Script --}}
    <script>
        function checkSystemStatus() {
            fetch('{{ route('api.maintenance-check') }}')
                .then(response => response.json())
                .then(data => {
                    if (!data.is_maintenance) {
                        // Jika sistem sudah ONLINE, otomatis refresh halaman
                        window.location.reload();
                    }
                })
                .catch(err => {
                    // Jika server mati total, abaikan agar tidak spam error di console
                });
        }
        // Cek setiap 15 detik untuk respon yang lebih cepat
        setInterval(checkSystemStatus, 15000);
    </script>

    {{-- Scanline Effect --}}
    <div class="fixed inset-0 pointer-events-none opacity-5 bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] z-[100] bg-[length:100%_2px,3px_100%]"></div>
</body>
</html>

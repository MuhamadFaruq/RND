<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Maintenance | Duniatex RND</title>
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
                <svg class="w-16 h-16 md:w-24 md:h-24 text-brand-600 animate-spin" style="animation-duration: 8s" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-4xl md:text-7xl font-black italic uppercase tracking-tighter text-white mb-4 leading-none">
            UNDER <span class="text-brand-600">REPAIR</span>
        </h1>
        
        <p class="text-slate-400 font-bold uppercase tracking-[0.15em] md:tracking-[0.3em] italic text-[10px] md:text-xs mb-8 md:mb-12 leading-relaxed max-w-md mx-auto">
            Sistem sedang dioptimasi oleh tim IT RND.<br class="hidden md:block">Mohon tunggu beberapa saat untuk pengalaman yang lebih baik.
        </p>

        <div class="grid grid-cols-2 gap-3 md:gap-6 max-w-sm mx-auto mb-10 md:mb-16">
            <div class="mkt-surface-alt border mkt-border p-4 md:p-6 rounded-[1.5rem] md:rounded-[2.5rem] shadow-xl">
                <div class="text-[8px] md:text-[10px] text-slate-500 font-black mb-1 md:mb-2 uppercase tracking-widest italic leading-none">Status</div>
                <div class="text-sm md:text-lg text-brand-600 font-black italic uppercase leading-none">Updating</div>
            </div>
            <div class="mkt-surface-alt border mkt-border p-4 md:p-6 rounded-[1.5rem] md:rounded-[2.5rem] shadow-xl">
                <div class="text-[8px] md:text-[10px] text-slate-500 font-black mb-1 md:mb-2 uppercase tracking-widest italic leading-none">Unit</div>
                <div class="text-sm md:text-lg text-emerald-500 font-black italic uppercase leading-none">IT RND</div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12 md:mb-20">
            {{-- 1. STOP IMPERSONATING (If active) --}}
            @if(session()->has('impersonator_id'))
                <a href="{{ route('admin.stop-impersonate') }}" class="w-full sm:w-auto group flex items-center justify-center gap-3 px-8 py-4 rounded-2xl md:rounded-3xl bg-amber-600 text-white font-black uppercase italic tracking-widest text-[10px] md:text-xs hover:bg-black transition-all duration-500 shadow-2xl shadow-amber-900/40">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                    </svg>
                    Stop Impersonate
                </a>
            @endif

            {{-- 2. USER MANAGEMENT (Only for Super Admin to access Impersonate feature) --}}
            @if(auth()->check() && (auth()->user()->isSuperAdmin() || session()->has('impersonator_id')))
                <a href="{{ route('admin.users') }}" class="w-full sm:w-auto group flex items-center justify-center gap-3 px-8 py-4 rounded-2xl md:rounded-3xl bg-emerald-600 text-white font-black uppercase italic tracking-widest text-[10px] md:text-xs hover:bg-black transition-all duration-500 shadow-2xl shadow-emerald-900/40">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    User Management
                </a>
            @endif

            {{-- 3. LOGOUT / BACK TO LOGIN --}}
            @if(auth()->check())
                <form id="logout-form-maintenance" method="POST" action="{{ route('logout') }}" class="hidden">
                    @csrf
                </form>
                <button type="button" onclick="confirmLogout()"
                    class="w-full sm:w-auto group flex items-center justify-center gap-3 px-8 py-4 rounded-2xl md:rounded-3xl bg-brand-600 text-white font-black uppercase italic tracking-widest text-[10px] md:text-xs hover:bg-black transition-all duration-500 shadow-2xl shadow-brand-900/40">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout Sesi
                </button>
            @else
                <a href="{{ route('login') }}" class="w-full sm:w-auto group flex items-center justify-center gap-3 px-8 py-4 rounded-2xl md:rounded-3xl bg-brand-600 text-white font-black uppercase italic tracking-widest text-[10px] md:text-xs hover:bg-black transition-all duration-500 shadow-2xl shadow-brand-900/40">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Halaman Login
                </a>
            @endif
        </div>

        <div class="flex items-center justify-center gap-2 mb-10 opacity-50">
            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-ping"></div>
            <span class="text-[8px] md:text-[9px] font-bold text-slate-500 uppercase tracking-[0.2em] md:tracking-[0.4em] italic leading-none">Auto-detecting system status...</span>
        </div>

        <div class="text-[9px] md:text-[11px] font-black text-slate-800 dark:text-slate-800 uppercase tracking-[0.4em] md:tracking-[0.8em] italic leading-none transition-colors">
            DUNIATEX <span class="mx-1">&bull;</span> RND SYSTEM <span class="mx-1">&bull;</span> 2026
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Auto Recovery Script --}}
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'KONFIRMASI LOGOUT',
                text: "Apakah Anda yakin ingin keluar?",
                icon: 'warning',
                iconColor: '#ED1C24',
                showCancelButton: true,
                confirmButtonColor: '#ED1C24',
                cancelButtonColor: '#334155',
                confirmButtonText: 'YA, KELUAR',
                cancelButtonText: 'BATAL',
                background: '#0b0f19',
                color: '#fff',
                customClass: {
                    popup: 'border border-white/10 backdrop-blur-xl rounded-3xl',
                    title: 'font-black italic uppercase tracking-tighter',
                    confirmButton: 'rounded-xl font-bold italic tracking-widest uppercase text-xs px-6 py-3 mx-2',
                    cancelButton: 'rounded-xl font-bold italic tracking-widest uppercase text-xs px-6 py-3 mx-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form-maintenance').submit();
                }
            });
        }

        function checkSystemStatus() {
            fetch('{{ route('api.maintenance-check') }}')
                .then(response => {
                    if (!response.ok) {
                        return null;
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && !data.is_maintenance) {
                        window.location.reload();
                    }
                })
                .catch(() => {});
        }
        setInterval(checkSystemStatus, 10000);
    </script>

    {{-- Scanline Effect --}}
    <div class="fixed inset-0 pointer-events-none opacity-[0.02] bg-[linear-gradient(rgba(18,16,16,0)_50%,rgba(0,0,0,0.25)_50%),linear-gradient(90deg,rgba(255,0,0,0.06),rgba(0,255,0,0.02),rgba(0,0,255,0.06))] z-[100] bg-[length:100%_2px,3px_100%]"></div>
</body>
</html>

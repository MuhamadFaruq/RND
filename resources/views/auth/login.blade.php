<x-guest-layout>
    <div class="min-h-screen bg-black flex items-center justify-center relative overflow-hidden selection:bg-red-600 selection:text-white">
        
        {{-- 1. BACKGROUND LAYER (Identik dengan Welcome) --}}
        <div class="absolute inset-0 z-0 overflow-hidden">
            <div class="absolute inset-0 scale-110">
                <img src="{{ asset('images/bg.jpg') }}" 
                     class="w-full h-full object-cover object-center" 
                     alt="Background Pabrik">
            </div>
            <div class="absolute inset-0 backdrop-blur-sm bg-black/60 shadow-inner"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/80"></div>
        </div>

        {{-- 2. LOGIN INTERFACE (Glassmorphism Card) --}}
        <div class="relative z-10 w-full max-w-md px-6">
            <div class="bg-white/5 backdrop-blur-2xl p-8 md:p-10 rounded-[3rem] border border-white/10 shadow-2xl">
                
                {{-- HEADER --}}
                <div class="mb-8 text-center">
                    <div class="relative inline-block group mb-4">
                        <div class="absolute inset-0 bg-red-600/20 blur-3xl rounded-full scale-150"></div>
                        <img src="{{ asset('images/lg.png') }}" class="relative h-20 mx-auto drop-shadow-[0_10px_10px_rgba(0,0,0,0.5)]" alt="Logo">
                    </div>
                    <h2 class="text-3xl font-black italic uppercase tracking-tighter text-white leading-none">
                        Production <span class="text-red-600">Login</span>
                    </h2>
                    <p class="text-[9px] text-slate-600 font-bold uppercase tracking-[0.3em] mt-3 italic opacity-90">Internal Network System</p>
                </div>

                <x-auth-session-status class="mb-4 text-emerald-400 text-[10px] font-bold uppercase text-center" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    {{-- FIELD: IDENTITY --}}
                    <div class="space-y-2">
                        <label class="block text-[9px] font-black uppercase text-slate-600 tracking-widest italic ml-4">Authorized Identity</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus 
                               class="w-full bg-white/5 border-white/10 text-white text-sm rounded-2xl focus:ring-red-600 focus:border-red-600 py-3.5 px-6 transition-all placeholder:text-slate-600"
                               placeholder="Email address">
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-red-500 text-[9px] font-bold uppercase italic ml-4" />
                    </div>

                    {{-- FIELD: PASSWORD --}}
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-600 tracking-widest italic ml-4">Access Secret</label>
                        <input type="password" name="password" required 
                               class="w-full bg-white/5 border-white/10 text-white text-sm rounded-2xl focus:ring-red-600 focus:border-red-600 py-3.5 px-6 transition-all placeholder:text-slate-600"
                               placeholder="••••••••">
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-red-500 text-[9px] font-bold uppercase italic ml-4" />
                    </div>

                    {{-- UTILITIES --}}
                    <div class="flex items-center justify-between px-2 pt-1">
                        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                            <input id="remember_me" type="checkbox" class="rounded border-white/10 bg-white/5 text-red-600 shadow-sm focus:ring-red-600 focus:ring-offset-0" name="remember">
                            <span class="ms-2 text-[10px] font-black uppercase text-slate-600 italic group-hover:text-white transition-colors">Ingat Saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-[9px] font-black text-slate-500 hover:text-red-500 uppercase tracking-widest italic transition-colors" href="{{ route('password.request') }}">
                                Lupa Password?
                            </a>
                        @endif
                    </div>

                    {{-- BUTTON --}}
                    <div class="pt-2">
                        <button type="submit" class="group relative w-full bg-red-600 text-white py-4 rounded-2xl font-black italic uppercase tracking-widest transition-all hover:bg-red-700 hover:shadow-[0_0_30px_rgba(220,38,38,0.4)] active:scale-95">
                            <span class="relative z-10">LOG IN</span>
                            <div class="absolute inset-0 w-0 bg-white/10 transition-all duration-300 group-hover:w-full"></div>
                        </button>
                    </div>
                </form>
            </div>

            {{-- FOOTER --}}
            <div class="mt-10 text-center opacity-40">
                <div class="flex justify-center items-center gap-6">
                    <span class="text-[10px] font-mono text-slate-500 uppercase tracking-widest italic">Duniatex &copy; 2026</span>
                </div>
            </div>
        </div>
    </div>

<script>
// Cek jika pengguna mencoba kembali ke halaman login setelah login berhasil
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// Mencegah navigasi balik secara fisik di browser
window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>
</x-guest-layout>
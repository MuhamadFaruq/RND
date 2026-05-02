<x-guest-layout>
    <style>
        /* Memaksa warna autofill browser agar tidak merusak desain dark mode */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0px 1000px #0a0a0a inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        input::placeholder {
            color: #475569 !important; /* slate-600 */
        }
    </style>

    <div
        class="min-h-screen w-full flex items-center justify-center relative selection:bg-red-600 selection:text-white font-sans overflow-x-hidden">

        {{-- 1. CLEAN BACKGROUND LAYER --}}
        <div class="absolute inset-0 z-0 overflow-hidden">
            <div class="absolute inset-0 scale-110">
                <img src="{{ asset('images/bg.jpg') }}" class="w-full h-full object-cover object-center"
                    alt="Background Pabrik">
            </div>
            <div class="absolute inset-0 backdrop-blur-sm bg-black/60 shadow-inner"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-transparent to-black/80"></div>
        </div>

        {{-- 2. PREMIUM COMPACT INTERFACE --}}
        <div class="relative z-10 w-full max-w-lg px-6 py-10 flex flex-col items-center">

            {{-- LOGO SECTION --}}
            <div class="mb-10 text-center animate-in fade-in zoom-in duration-700">
                <div class="relative inline-block mb-6">
                    <div class="absolute inset-0 bg-red-600/30 blur-[80px] rounded-full scale-150"></div>
                    <img src="{{ asset('images/lg.png') }}"
                        class="relative h-24 md:h-28 mx-auto drop-shadow-[0_15px_30px_rgba(0,0,0,1)]" alt="Logo">
                </div>

                <div class="space-y-3">
                    <div
                        class="inline-block px-5 py-1.5 bg-red-600 text-white rounded-full mb-2 shadow-[0_10px_20px_rgba(220,38,38,0.3)]">
                        <span class="text-[9px] font-black tracking-[0.6em] uppercase italic">RND INTELLIGENCE</span>
                    </div>
                    <h1
                        class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-white leading-none">
                        SYSTEM <span class="text-red-600">ACCESS</span>
                    </h1>
                </div>
            </div>

            {{-- FORM CARD: Premium Glassmorphism --}}
            <div
                class="w-full bg-white/[0.03] backdrop-blur-3xl p-10 md:p-12 rounded-[3.5rem] border border-white/10 shadow-[0_50px_100px_-20px_rgba(0,0,0,1)] relative overflow-hidden">
                
                <x-auth-session-status
                    class="mb-8 p-4 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-[10px] font-black uppercase rounded-2xl text-center"
                    :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    {{-- FIELD: IDENTITY --}}
                    <div class="space-y-3">
                        <label
                            class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em] italic ml-4">Identity
                            Authorization</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full bg-black/80 border border-white/10 text-white text-base rounded-[1.5rem] focus:ring-2 focus:ring-red-600 focus:border-red-600 py-4.5 px-8 transition-all placeholder:text-slate-600 shadow-2xl"
                            placeholder="Email Address">
                        <x-input-error :messages="$errors->get('email')"
                            class="mt-2 text-red-500 text-[10px] font-black uppercase italic ml-4" />
                    </div>

                    {{-- FIELD: PASSWORD --}}
                    <div class="space-y-3">
                        <div class="flex justify-between items-center ml-4">
                            <label
                                class="text-[10px] font-black uppercase text-slate-400 tracking-[0.3em] italic">Access
                                Secret</label>
                        </div>
                        <input type="password" name="password" required
                            class="w-full bg-black/80 border border-white/10 text-white text-base rounded-[1.5rem] focus:ring-2 focus:ring-red-600 focus:border-red-600 py-4.5 px-8 transition-all placeholder:text-slate-600 shadow-2xl"
                            placeholder="••••••••••••">
                        <x-input-error :messages="$errors->get('password')"
                            class="mt-2 text-red-500 text-[10px] font-black uppercase italic ml-4" />
                    </div>

                    {{-- UTILITIES --}}
                    <div class="flex items-center justify-between px-4 pt-2">
                        <label for="remember_me" class="inline-flex items-center group cursor-pointer">
                            <input id="remember_me" type="checkbox"
                                class="w-5 h-5 rounded-lg border-white/20 bg-white/5 text-red-600 focus:ring-red-600 focus:ring-offset-0 transition-all cursor-pointer"
                                name="remember">
                            <span
                                class="ms-3 text-xs font-bold text-slate-400 group-hover:text-white transition-colors">Remember
                                my session</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-[10px] font-black text-slate-500 hover:text-red-500 uppercase tracking-widest italic transition-colors"
                                href="{{ route('password.request') }}">
                                Reset?
                            </a>
                        @endif
                    </div>

                    {{-- ACTION BUTTON --}}
                    <div class="pt-6">
                        <button type="submit"
                            class="w-full bg-white text-black py-5 rounded-3xl font-black italic uppercase tracking-[0.2em] transition-all hover:bg-red-600 hover:text-white hover:scale-[1.02] shadow-[0_20px_40px_-10px_rgba(0,0,0,0.5)] active:scale-95 text-sm">
                            Unlock Access
                        </button>
                    </div>
                </form>
            </div>

            {{-- FOOTER --}}
            <div class="mt-12 text-center">
                <p class="text-[10px] font-black text-slate-700 uppercase tracking-[0.5em] italic">DUNIATEX &bull; RND
                    SYSTEM &bull; 2026</p>
            </div>
        </div>
    </div>

    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</x-guest-layout>
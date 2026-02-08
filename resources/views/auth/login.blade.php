<x-guest-layout>
    <div class="mb-4 text-center">
        <img src="{{ asset('images/logo.jpg') }}" class="h-20 mx-auto mb-4" alt="Duniatex Logo">
        <h2 class="text-2xl font-black italic uppercase tracking-tighter text-slate-800">Production Login</h2>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Email / Username</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-2xl border-slate-200 focus:ring-red-500">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Password</label>
            <input type="password" name="password" required class="w-full rounded-2xl border-slate-200 focus:ring-red-500">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-red-600 shadow-sm focus:ring-red-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat Saya</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-6">
            @if (Route::has('password.request'))
                <a class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase" href="{{ route('password.request') }}">
                    Lupa Password?
                </a>
            @endif

            <button type="submit" class="bg-slate-900 text-white px-8 py-3 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-black transition shadow-xl">
                Log In
            </button>
        </div>
    </form>
</x-guest-layout>
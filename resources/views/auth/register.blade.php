<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-black italic uppercase tracking-tighter text-slate-800">Daftar Akun Baru</h2>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Nama Lengkap</label>
            <input type="text" name="name" :value="old('name')" required autofocus class="w-full rounded-2xl border-slate-200">
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Email</label>
            <input type="email" name="email" :value="old('email')" required class="w-full rounded-2xl border-slate-200">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Password</label>
            <input type="password" name="password" required class="w-full rounded-2xl border-slate-200">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-2xl border-slate-200">
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="text-xs font-bold text-slate-400 hover:text-slate-600 uppercase mr-4" href="{{ route('login') }}">
                Sudah punya akun?
            </a>
            <button type="submit" class="bg-red-600 text-white px-8 py-3 rounded-2xl font-black italic uppercase tracking-tighter hover:bg-red-700 shadow-xl">
                Daftar
            </button>
        </div>
    </form>
</x-guest-layout>
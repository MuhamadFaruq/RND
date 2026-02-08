<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 italic font-medium">
        Lupa kata sandi? Beritahu kami alamat email Anda dan kami akan mengirimkan tautan untuk menyetel ulang kata sandi.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <label class="block text-xs font-black uppercase text-slate-400 mb-1">Email Anda</label>
            <input type="email" name="email" :value="old('email')" required autofocus class="w-full rounded-2xl border-slate-200">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-2xl font-black italic uppercase tracking-tighter shadow-xl">
                Kirim Email Reset Password
            </button>
        </div>
    </form>
</x-guest-layout>
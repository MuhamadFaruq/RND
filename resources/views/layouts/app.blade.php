<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Duniatex RND System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Mengatur tampilan scrollbar internal agar serasi dengan tema gelap */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #0f172a; 
        }
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #ef4444; /* Berubah merah saat di-hover sesuai tema RND */
        }
    </style>
    @livewireStyles
</head>
{{-- PERBAIKAN 1: Ganti bg-slate-100 menjadi bg-slate-950 agar sisa ruang samping tidak terlihat terpisah --}}
<body class="font-sans antialiased bg-slate-950 text-white">
    <div class="min-h-screen">
        @if(session()->has('impersonator_id'))
            <div class="bg-red-600 text-white p-2 text-center text-[10px] font-black uppercase italic tracking-widest sticky top-0 z-[100] flex justify-center items-center gap-4">
                ⚠️ MODE IMPERSONATE: Anda sedang masuk sebagai {{ auth()->user()->name }} ({{ auth()->user()->role }})
                <a href="{{ route('admin.stop-impersonate') }}" class="bg-white text-red-600 px-4 py-1 rounded-full hover:bg-slate-100 transition-all text-xs">
                    KEMBALI KE ADMIN
                </a>
            </div>
        @endif
        
        {{-- PERBAIKAN 2: Navigasi menggunakan max-w-full dan px-6 agar melebar --}}
        <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 sticky top-0 z-50">
                    <div class="flex">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="text-xl font-extrabold tracking-wider text-red-600">
                                RND <span class="text-white">DUNIATEX</span>
                            </a>
                        </div>

                        <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex items-center">
                            <a href="{{ Auth::user()->role === 'marketing' ? route('marketing.dashboard', ['menu' => 'dashboard']) : route('dashboard') }}" 
                            class="px-3 py-2 rounded-md text-sm font-bold transition {{ (request()->query('menu') === 'dashboard' || request()->routeIs('dashboard') || request()->routeIs('marketing.dashboard')) && request()->query('menu') !== 'input' && request()->query('menu') !== 'orders' ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                Dashboard
                            </a>

                            @if(Auth::user()->role === 'marketing')
                                <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}" 
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'input' ? 'bg-red-600 text-white' : 'text-slate-400 hover:text-white' }}">
                                    Input Marketing
                                </a>
                                <a href="{{ route('marketing.dashboard', ['menu' => 'orders']) }}" 
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'orders' ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                    Daftar Order
                                </a>
                            @endif

                            @if(in_array(Auth::user()->role, ['operator', 'knitting', 'dyeing', 'relax-dryer', 'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe']))
                                <a href="{{ route('operator.logbook', ['menu' => 'orders']) }}" 
                                    class="px-3 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'orders' ? 'bg-blue-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                    Permintaan
                                </a>
                                <a href="{{ route('operator.logbook', ['menu' => 'history']) }}" 
                                    class="px-3 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'history' ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                    Riwayat
                                </a>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'super-admin']))
                                <a href="{{ route('admin.monitoring') }}" 
                                class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.monitoring') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                    Monitoring
                                </a>

                                <a href="{{ route('admin.unit-monitoring') }}" 
                                class="{{ request()->routeIs('admin.unit-monitoring') ? 'bg-red-600' : '' }} px-4 py-2 rounded-lg font-bold">
                                    Unit Monitoring
                                </a>
                                
                                <a href="{{ route('admin.activity-logs') }}" 
                                class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.activity-logs') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                    Audit Trail
                                </a>

                                @if(auth()->user()->role === 'super-admin')
                                    <a href="{{ route('admin.users') }}" 
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.users') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                        Users Management
                                    </a>

                                    <a href="{{ route('admin.divisions') }}" 
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.divisions') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                        Divisions
                                    </a>

                                    <a href="{{ route('admin.config') }}" 
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.config') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-800' }}">
                                        System Config
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="hidden sm:flex sm:items-center sm:ml-6">
                        <div class="flex items-center gap-4">
                            <span class="text-slate-400 text-sm italic">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bg-slate-800 text-slate-300 hover:bg-red-600 hover:text-white px-3 py-1.5 rounded-md text-xs font-bold transition">
                                    LOGOUT
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

       {{-- PERBAIKAN 3: Hilangkan batasan lebar pada main dan kontainer pembungkusnya --}}
       <main class="py-4"> 
            {{-- Mengubah max-w-7xl menjadi max-w-full untuk menghilangkan ruang kosong di samping --}}
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- 1. Load SweetAlert2 Library --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireScripts

    <script>
        // A. Listener untuk Notifikasi Berhasil (Toast)
        window.addEventListener('show-success-toast', event => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: event.detail.message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#0f172a',
                color: '#fff',
                customClass: {
                    popup: 'border border-white/10 backdrop-blur-md rounded-2xl'
                }
            });
        });

        // B. Fungsi Konfirmasi Hapus
        function confirmDelete(id, name) {
            Swal.fire({
                title: '<span style="color: #fff; font-style: italic; font-weight: 900; text-transform: uppercase; letter-spacing: -1px; font-size: 24px;">Konfirmasi Hapus</span>',
                html: `Apakah Anda yakin ingin menghapus user <b class="text-red-500">${name}</b>?<br><small class="text-slate-400">Data ini akan dihapus permanen dari sistem RND.</small>`,
                icon: 'warning',
                iconColor: '#dc2626',
                background: '#0f172a',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#334155',
                confirmButtonText: 'YA, HAPUS DATA',
                cancelButtonText: 'BATAL',
                customClass: {
                    popup: 'border border-white/10 backdrop-blur-xl rounded-3xl',
                    title: 'font-black italic uppercase tracking-tighter',
                    confirmButton: 'rounded-xl font-bold italic tracking-widest uppercase text-xs px-6 py-3 mx-2',
                    cancelButton: 'rounded-xl font-bold italic tracking-widest uppercase text-xs px-6 py-3 mx-2'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // HANYA JIKA USER KLIK "YA", Sinyal dikirim ke Livewire
                    Livewire.dispatch('delete-confirmed', { id: id });
                }
            })
        }

        // C. Fungsi Navigasi Menu
        function triggerMenu(menuName) {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('change-menu', { menu: menuName });
            } else {
                console.error('Livewire belum dimuat sepenuhnya.');
            }
        }

        function confirmPurgeLogs() {
            Swal.fire({
                title: 'BERSIHKAN LOG AUDIT?',
                text: "Seluruh riwayat aktivitas di atas 6 bulan akan dihapus permanen untuk mengoptimalkan database.",
                icon: 'warning',
                background: '#0f172a',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'YA, BERSIHKAN',
                cancelButtonText: 'BATAL'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('purge-logs-confirmed'); // Pastikan listener ini terdaftar di PHP
                }
            })
        }
    </script>
</body>
</html>
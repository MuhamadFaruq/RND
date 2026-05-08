<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeManager()"
    x-bind:class="isDark ? 'dark' : ''">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- BLOCKING THEME SCRIPT: Prevent White Flash on Load --}}
    <script>
        (function () {
            const theme = localStorage.getItem('mkt-theme');
            const isDark = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <title>Duniatex RND System</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --mkt-bg: #f8fafc;
            --mkt-surface: #ffffff;
            --mkt-surface-alt: #f1f5f9;
            --mkt-border: #e2e8f0;
            --mkt-text: #0f172a;
            --mkt-text-muted: #64748b;
            --mkt-input-bg: #f8fafc;
        }

        .dark {
            --mkt-bg: #0f172a;
            --mkt-surface: #1e293b;
            --mkt-surface-alt: #0f172a;
            --mkt-border: #334155;
            --mkt-text: #f1f5f9;
            --mkt-text-muted: #94a3b8;
            --mkt-input-bg: #1e293b;
        }

        html {
            background-color: var(--mkt-bg);
            transition: none;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-color: var(--mkt-bg);
        }


        /* Utility classes that respond to theme */
        .mkt-bg {
            background-color: var(--mkt-bg) !important;
        }

        .mkt-surface {
            background-color: var(--mkt-surface) !important;
        }

        .mkt-surface-alt {
            background-color: var(--mkt-surface-alt) !important;
        }

        .mkt-border {
            border-color: var(--mkt-border) !important;
        }

        .mkt-text {
            color: var(--mkt-text) !important;
        }

        .mkt-text-muted {
            color: var(--mkt-text-muted) !important;
        }

        .mkt-input {
            background-color: var(--mkt-input-bg) !important;
            color: var(--mkt-text) !important;
        }

        /* Smooth transitions on theme change */
        *,
        *::before,
        *::after {
            transition: background-color 0.25s ease, border-color 0.25s ease, color 0.2s ease;
        }

        /* Body responds to the active theme */
        body {
            background-color: var(--mkt-bg);
        }

        /* Scrollbar styling */
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
            background: #ef4444;
        }
    </style>
    @livewireStyles
</head>
{{-- Dark mode body background is handled by mkt-bg on child components --}}

<body class="font-sans antialiased text-white">
    <div class="min-h-screen">
        @if(session()->has('impersonator_id'))
            <div
                class="bg-red-600 text-white p-2 text-center text-[10px] font-black uppercase italic tracking-widest sticky top-0 z-[100] flex justify-center items-center gap-4">
                ⚠️ MODE IMPERSONATE: Anda sedang masuk sebagai {{ auth()->user()->name }} ({{ auth()->user()->role }})
                <a href="{{ route('admin.stop-impersonate') }}"
                    class="bg-white text-red-600 px-4 py-1 rounded-full hover:bg-slate-100 transition-all text-xs">
                    KEMBALI KE ADMIN
                </a>
            </div>
        @endif

        <nav class="mkt-surface border-b mkt-border sticky top-0 z-50 transition-colors duration-300">
            @if(app()->isDownForMaintenance() && auth()->user()->isSuperAdmin())
                <div
                    class="bg-amber-600/90 backdrop-blur-md text-white py-1.5 text-center text-[10px] font-black uppercase italic tracking-[0.3em] animate-pulse border-b border-amber-500/50">
                    ⚠️ SYSTEM IN MAINTENANCE MODE: Seluruh role (Marketing & Operator) saat ini terkunci.
                </div>
            @endif
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 sticky top-0 z-50">
                    <div class="flex items-center">
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}"
                                class="text-xl font-extrabold tracking-wider text-red-600">
                                RND <span class="mkt-text">DUNIATEX</span>
                            </a>
                        </div>

                        {{-- Horizontal Menu: Hidden on screens smaller than XL (iPad Landscape/Portrait) --}}
                        <div class="hidden xl:flex space-x-2 xl:ml-10 items-center">
                            <a href="{{ Auth::user()->role === 'marketing' ? route('marketing.dashboard', ['menu' => 'dashboard']) : route('dashboard') }}"
                                class="px-3 py-2 rounded-md text-sm font-bold transition {{ (request()->query('menu') === 'dashboard' || request()->routeIs('dashboard') || request()->routeIs('marketing.dashboard')) && request()->query('menu') !== 'input' && request()->query('menu') !== 'orders' ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                Dashboard
                            </a>

                            @if(Auth::user()->role === 'marketing')
                                <a href="{{ route('marketing.dashboard', ['menu' => 'input']) }}"
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'input' ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                    Input Marketing
                                </a>
                                <a href="{{ route('marketing.dashboard', ['menu' => 'orders']) }}"
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'orders' ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                    Daftar Order
                                </a>
                            @endif

                            @if(in_array(Auth::user()->role, ['operator', 'knitting', 'dyeing', 'relax-dryer', 'finishing', 'stenter', 'tumbler', 'fleece', 'pengujian', 'qe']))
                                <a href="{{ route('operator.logbook', ['menu' => 'orders']) }}"
                                    class="px-3 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'orders' ? 'bg-blue-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                    Permintaan
                                </a>
                                <a href="{{ route('operator.logbook', ['menu' => 'history']) }}"
                                    class="px-3 py-2 rounded-md text-sm font-bold transition {{ request()->query('menu') === 'history' ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                    Riwayat
                                </a>
                            @endif

                            @if(in_array(auth()->user()->role, ['admin', 'super-admin']))
                                <a href="{{ route('admin.monitoring') }}"
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.monitoring') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                    Monitoring
                                </a>

                                <a href="{{ route('admin.unit-monitoring') }}"
                                    class="{{ request()->routeIs('admin.unit-monitoring') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }} px-4 py-2 rounded-lg font-bold transition">
                                    Unit Monitoring
                                </a>

                                <a href="{{ route('admin.activity-logs') }}"
                                    class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.activity-logs') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                    Audit Trail
                                </a>

                                @if(auth()->user()->role === 'super-admin')
                                    <a href="{{ route('admin.users') }}"
                                        class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.users') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                        Users Management
                                    </a>

                                    <a href="{{ route('admin.divisions') }}"
                                        class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.divisions') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                        Divisions
                                    </a>

                                    <a href="{{ route('admin.config') }}"
                                        class="px-4 py-2 rounded-md text-sm font-bold transition {{ request()->routeIs('admin.config') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-text hover:bg-slate-200 dark:hover:bg-slate-800' }}">
                                        System Config
                                    </a>
                                @endif
                            @endif

                            {{-- Global Search: Hidden on mobile, shown on XL --}}
                            <div class="hidden 2xl:flex items-center ml-8 relative group">
                                <span
                                    class="absolute left-4 text-slate-500 group-focus-within:text-red-500 transition-colors">🔍</span>
                                <input type="text" placeholder="GLOBAL SAP SEARCH..."
                                    class="pl-12 pr-4 py-2 w-64 mkt-surface-alt border mkt-border rounded-xl text-[10px] font-black uppercase italic focus:ring-2 focus:ring-red-600/20 outline-none transition-all placeholder:mkt-text-muted"
                                    onkeypress="if(event.key === 'Enter') { window.location.href = '/admin/unit-monitoring?search=' + this.value; }">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Theme Toggle --}}
                        <div class="flex items-center">
                            <button @click="toggleTheme()"
                                class="p-2 rounded-full mkt-surface-alt mkt-text-muted hover:mkt-text transition-all shadow-sm">
                                <template x-if="!isDark">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 3v1m0 16v1m9-9h-1m-16 0h-1m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.243 17.657l.707-.707M7.757 6.343l.707-.707M12 8a4 4 0 110 8 4 4 0 010-8z">
                                        </path>
                                    </svg>
                                </template>
                                <template x-if="isDark">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z">
                                        </path>
                                    </svg>
                                </template>
                            </button>
                        </div>

                        {{-- Profile & Logout: Desktop --}}
                        <div class="hidden xl:flex items-center gap-4 ml-2 pl-4 border-l mkt-border">
                            <div class="text-right">
                                <div class="text-[10px] font-black mkt-text uppercase tracking-widest italic">
                                    {{ Auth::user()->name }}</div>
                                <div class="text-[8px] mkt-text-muted font-bold uppercase">{{ Auth::user()->role }}
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="p-2 rounded-full mkt-surface-alt text-red-500 hover:bg-red-500 hover:text-white transition-all shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{-- Mobile Menu Toggle (Hamburger) --}}
                        <div class="xl:hidden flex items-center" x-data="{ open: false }">
                            <button @click="$dispatch('open-menu')"
                                class="p-2 rounded-xl mkt-surface-alt mkt-text-muted hover:mkt-text transition-all border mkt-border shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile Side Menu (Overlay) --}}
            <div x-data="{ open: false }" @open-menu.window="open = true" x-show="open"
                class="fixed inset-0 z-[100] xl:hidden" x-cloak>
                <div @click="open = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity">
                </div>
                <div class="absolute right-0 top-0 bottom-0 w-80 mkt-surface shadow-2xl transition-transform"
                    x-show="open" x-transition:enter="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="translate-x-0" x-transition:leave-end="translate-x-full">

                    <div class="p-6 h-full flex flex-col">
                        <div class="flex justify-between items-center mb-10">
                            <div class="text-lg font-black text-red-600 italic">MENU <span
                                    class="mkt-text">EXPLORER</span></div>
                            <button @click="open = false" class="p-2 rounded-xl mkt-surface-alt mkt-text-muted">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="flex-grow space-y-2 overflow-y-auto pr-2">
                            {{-- Mobile Navigation Links --}}
                            <a href="{{ route('dashboard') }}"
                                class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('dashboard') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">Dashboard</a>

                            @if(in_array(auth()->user()->role, ['admin', 'super-admin']))
                                <a href="{{ route('admin.monitoring') }}"
                                    class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('admin.monitoring') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">Monitoring</a>
                                <a href="{{ route('admin.unit-monitoring') }}"
                                    class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('admin.unit-monitoring') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">Unit
                                    Monitoring</a>
                                <a href="{{ route('admin.activity-logs') }}"
                                    class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('admin.activity-logs') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">Audit
                                    Trail</a>

                                @if(auth()->user()->role === 'super-admin')
                                    <a href="{{ route('admin.users') }}"
                                        class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('admin.users') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">Users
                                        Management</a>
                                    <a href="{{ route('admin.divisions') }}"
                                        class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('admin.divisions') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">Divisions</a>
                                    <a href="{{ route('admin.config') }}"
                                        class="flex items-center px-4 py-3 rounded-2xl {{ request()->routeIs('admin.config') ? 'bg-red-600 text-white' : 'mkt-text-muted hover:mkt-surface-alt' }} font-bold text-sm">System
                                        Config</a>
                                @endif
                            @endif
                        </div>

                        <div class="mt-auto pt-6 border-t mkt-border">
                            <div class="flex items-center gap-3 mb-6">
                                <div
                                    class="w-10 h-10 rounded-2xl bg-red-600 flex items-center justify-center text-white font-black">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-xs font-black mkt-text uppercase">{{ Auth::user()->name }}</div>
                                    <div class="text-[9px] mkt-text-muted font-bold uppercase">{{ Auth::user()->role }}
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full py-4 rounded-2xl bg-red-600/10 text-red-600 font-black uppercase text-xs tracking-widest hover:bg-red-600 hover:text-white transition-all">
                                    LOGOUT SYSTEM
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-4">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @livewireScripts

    <script>
        // =============================================
        // THEME MANAGER — Alpine.js global component
        // Menyimpan pilihan tema ke localStorage agar
        // tetap diingat setelah halaman di-refresh.
        // =============================================
        function themeManager() {
            return {
                isDark: localStorage.getItem('mkt-theme') === 'dark',

                init() {
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                },

                toggleTheme() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('mkt-theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('mkt-theme', 'light');
                    }
                }
            }
        }

        // A. Notifikasi Toast
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

        // B. Konfirmasi Hapus
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
                    Livewire.dispatch('delete-confirmed', { id: id });
                }
            })
        }

        // C. Navigasi Menu
        function triggerMenu(menuName) {
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('change-menu', { menu: menuName });
            } else {
                console.error('Livewire belum dimuat sepenuhnya.');
            }
        }

        // D. Konfirmasi Purge Logs
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
                    Livewire.dispatch('purge-logs-confirmed');
                }
            })
        }
        // E. Heartbeat Maintenance (Force Logout/Redirect)
        // Cek status maintenance setiap 30 detik untuk user non-admin
        @if(!auth()->user()->isSuperAdmin())
            function checkMaintenance() {
                fetch('{{ route('api.maintenance-check') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.is_maintenance) {
                            window.location.reload(); // Paksa refresh untuk memicu middleware 503
                        }
                    })
                    .catch(err => console.error('Maintenance check failed'));
            }
            setInterval(checkMaintenance, 30000); // Ticker 30 detik
        @endif

        // F. Global Real-time Clock
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2, '0');
            const m = String(now.getMinutes()).padStart(2, '0');
            const s = String(now.getSeconds()).padStart(2, '0');
            const options = { day: '2-digit', month: 'short', year: 'numeric' };

            const clockElements = document.querySelectorAll('.real-time-clock');
            const dateElements = document.querySelectorAll('.real-time-date');

            clockElements.forEach(el => el.textContent = `${h}:${m}:${s}`);
            dateElements.forEach(el => el.textContent = now.toLocaleDateString('id-ID', options).toUpperCase());
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>

</html>
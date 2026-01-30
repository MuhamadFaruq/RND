import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';

// Hapus const navigation dari sini (pindah ke dalam fungsi)

// ... (bagian import tetap sama)

export default function AuthenticatedLayout({ header, children }) {
    const { auth, flash } = usePage().props;
    const user = auth.user;

    // Di dalam AuthenticatedLayout.jsx

    const navigation = [
        { name: 'Dashboard', route: 'dashboard', icon: '📊', roles: ['superadmin', 'operator', 'marketing'] },
        
        { name: 'Monitoring', route: 'monitoring.dashboard', icon: '🏭', roles: ['superadmin'] },
        
        // LOGIKA FINAL: Logbook Operator
        { 
            name: 'Logbook Produksi', 
            route: (() => {
                const divisi = user.division?.toLowerCase();
                // TAMBAHKAN 'qc' ke dalam daftar ini agar langsung ke form
                if (['stenter', 'knitting', 'dyeing', 'pengujian', 'qe', 'qc'].includes(divisi)) {
                    return 'log.create';
                }
                return 'operator.divisions';
            })(),
            params: (() => {
                const divisi = user.division?.toLowerCase();
                // TAMBAHKAN 'qc' ke dalam daftar ini
                if (['stenter', 'knitting', 'dyeing', 'pengujian', 'qe', 'qc'].includes(divisi)) {
                    return { division: divisi };
                }
                return {};
            })(),
            icon: '📝', 
            roles: ['superadmin', 'operator'] 
        },

        { name: 'Marketing List', route: 'marketing.orders.index', icon: '📋', roles: ['superadmin', 'marketing'] },
        { name: 'Input Order', route: 'marketing.orders.create', icon: '➕', roles: ['marketing'] },
        
        { name: 'Users', route: 'users.index', icon: '🛡️', roles: ['superadmin'] },
        { name: 'System', route: 'admin.backup', icon: '⚙️', roles: ['superadmin'] },
    ];


    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const [showAlert, setShowAlert] = useState(false);

    useEffect(() => {
        if (flash?.error) {
            setShowAlert(true);
            const timer = setTimeout(() => setShowAlert(false), 8000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    return (
        <div className="min-h-screen bg-gray-100 relative">
            <nav className="border-b border-white/10 bg-[#ED1C24] sticky top-0 z-50">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href={route('dashboard')} className="flex items-center gap-3 group">
                                    <div className="bg-white p-1.5 rounded-xl shadow-sm group-hover:scale-105 transition-transform">
                                        <img src="/images/lg.png" alt="Duniatex Logo" className="h-8 w-auto object-contain" />
                                    </div>
                                    <div className="leading-tight">
                                        <div className="text-sm font-black tracking-tighter text-white italic uppercase">DUNIATEX</div>
                                        <div className="text-[9px] font-bold text-white/70 uppercase tracking-widest">Manufacturing System</div>
                                    </div>
                                </Link>
                            </div>

                            <div className="hidden space-x-4 sm:-my-px sm:ms-6 sm:flex">
                                {navigation
                                    .filter(item => item.roles.includes(user.role))
                                    .map((item) => (
                                        <div key={item.name} className="inline-flex items-center h-16">
                                            <NavLink 
                                                href={item.route ? route(item.route, item.params || {}) : '#'} 
                                                active={item.route ? route().current(item.route, item.params || {}) : false}
                                                className="px-1 text-[11px] font-bold"
                                            >
                                                <span className="mr-1.5">{item.icon}</span>
                                                {item.name}
                                            </NavLink>
                                        </div>
                                    ))}
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <div className="relative ms-3">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button type="button" className="inline-flex items-center rounded-md border border-white/10 bg-white/10 px-3 py-2 text-sm font-medium text-white transition hover:bg-white/15 focus:outline-none">
                                                <div className="flex flex-col items-end mr-3 leading-none">
                                                    <span className="font-black italic uppercase tracking-tighter text-xs">{user.name}</span>
                                                    <span className="text-[8px] uppercase font-black px-1.5 bg-white/20 rounded mt-1">{user.role}</span>
                                                </div>
                                                <svg className="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" /></svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>
                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>👤 Profile</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button" className="text-red-600 font-bold">Log Out</Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <button onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)} className="inline-flex items-center justify-center rounded-md p-2 text-white/80 hover:bg-white/10 focus:outline-none">
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'} strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path className={showingNavigationDropdown ? 'inline-flex' : 'hidden'} strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
                    <div className="space-y-1 pb-3 pt-2">
                        {navigation
                            .filter(item => item.roles.includes(user.role))
                            .map((item) => (
                                <ResponsiveNavLink 
                                    key={item.name} 
                                    href={item.route ? route(item.route, item.params || {}) : '#'} 
                                    active={item.route ? route().current(item.route, item.params || {}) : false}
                                >
                                    {item.icon} {item.name}
                                </ResponsiveNavLink>
                        ))}
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
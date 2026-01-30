import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

const divisions = [
    {
        key: 'knitting',
        label: 'Knitting',
        description: 'Knitting Machine Production Log',
        href: '/operator/log/knitting',
        color: 'border-blue-200 hover:border-[#ED1C24]',
        iconBg: 'bg-blue-50 text-blue-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M7 7l10 10M17 7L7 17" />
                <path d="M5 12h14" />
            </svg>
        ),
    },
    {
        key: 'scr-dyeing',
        label: 'SCR/Dyeing',
        description: 'Dyeing Process & Color Application',
        href: '/operator/log/scr-dyeing',
        color: 'border-indigo-200 hover:border-[#ED1C24]',
        iconBg: 'bg-indigo-50 text-indigo-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M12 3c3 4 6 7 6 10a6 6 0 11-12 0c0-3 3-6 6-10z" />
            </svg>
        ),
    },
    {
        key: 'relax-dryer',
        label: 'Relax Dryer',
        description: 'Relaxation & Drying Operations',
        href: '/operator/log/relax-dryer',
        color: 'border-emerald-200 hover:border-[#ED1C24]',
        iconBg: 'bg-emerald-50 text-emerald-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M4 12h8" />
                <path d="M12 6c4 0 8 3 8 6s-4 6-8 6" />
            </svg>
        ),
    },
    {
        key: 'compactor',
        label: 'Compactor',
        description: 'Compaction Process Control',
        href: '/operator/log/compactor',
        color: 'border-purple-200 hover:border-[#ED1C24]',
        iconBg: 'bg-purple-50 text-purple-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M7 7l10 10" />
                <path d="M7 17l10-10" />
            </svg>
        ),
    },
    {
        key: 'heat-setting',
        label: 'Heat Setting',
        description: 'Heat Setting & Stabilization',
        href: '/operator/log/heat-setting',
        color: 'border-orange-200 hover:border-[#ED1C24]',
        iconBg: 'bg-orange-50 text-orange-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M12 2c2 3 2 5 0 7s-2 4 0 7 2 4 0 6" />
            </svg>
        ),
    },
    {
        key: 'stenter',
        label: 'Stenter',
        description: 'Stenter Finishing (Belah)',
        href: '/operator/log/stenter',
        color: 'border-green-200 hover:border-[#ED1C24]',
        iconBg: 'bg-green-50 text-green-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M4 6h16v12H4z" />
                <path d="M8 10h8" />
            </svg>
        ),
    },
    {
        key: 'tumbler',
        label: 'Tumbler',
        description: 'Tumbling & Softening Process',
        href: '/operator/log/tumbler',
        color: 'border-sky-200 hover:border-[#ED1C24]',
        iconBg: 'bg-sky-50 text-sky-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M12 6v2" />
                <path d="M12 16v2" />
                <path d="M8 12h2" />
                <path d="M14 12h2" />
                <path d="M12 4a8 8 0 108 8" />
            </svg>
        ),
    },
    {
        key: 'fleece',
        label: 'Fleece',
        description: 'Raising, Brushing & Shearing',
        href: '/operator/log/fleece',
        color: 'border-pink-200 hover:border-[#ED1C24]',
        iconBg: 'bg-pink-50 text-pink-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M7 7h10v10H7z" />
                <path d="M9 9h6v6H9z" />
            </svg>
        ),
    },
    {
        key: 'pengujian',
        label: 'Pengujian',
        description: 'Quality Testing & Measurement',
        href: '/operator/log/pengujian',
        color: 'border-amber-200 hover:border-[#ED1C24]',
        iconBg: 'bg-amber-50 text-amber-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M10 2v6l-5 9a4 4 0 003.5 6h7A4 4 0 0019 17l-5-9V2" />
            </svg>
        ),
    },
    {
        key: 'qe',
        label: 'QE',
        description: 'Final Quality Evaluation',
        href: '/operator/log/qe',
        color: 'border-teal-200 hover:border-[#ED1C24]',
        iconBg: 'bg-teal-50 text-teal-600',
        icon: (
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M9 12l2 2 4-4" />
                <path d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
            </svg>
        ),
    },
];

function DivisionCard({ division, auth }) {
    const user = auth.user;
    
    // LOGIKA AKSES: Izinkan jika Superadmin ATAU Divisi User adalah Stenter ATAU Divisi User cocok dengan label
    const isStenterOperator = user.division?.toLowerCase() === 'stenter';
    const stenterGroup = ['Stenter', 'Compactor', 'Heat Setting', 'Relax Dryer', 'Tumbler', 'Fleece'];
    
    // Tambahkan array ini di file DivisionSelector.jsx
    const stenterUnits = [
        { key: 'stenter', label: 'Stenter', description: 'Main Finishing Unit', icon: '🖼️', color: 'border-green-200' },
        { key: 'compactor', label: 'Compactor', description: 'Shrinkage Control', icon: '⚙️', color: 'border-purple-200' },
        { key: 'heat-setting', label: 'Heat Setting', description: 'Fabric Stabilization', icon: '🔥', color: 'border-orange-200' },
        { key: 'relax-dryer', label: 'Relax Dryer', description: 'Tensionless Drying', icon: '🌬️', color: 'border-emerald-200' },
        { key: 'tumbler', label: 'Tumbler', description: 'Softening Process', icon: '🌀', color: 'border-sky-200' },
        { key: 'fleece', label: 'Fleece', description: 'Raising & Brushing', icon: '🧶', color: 'border-pink-200' },
    ];
    const hasAccess = 
        user.role === 'superadmin' || 
        user.division === division.label ||
        (isStenterOperator && stenterGroup.includes(division.label));

    return (
        <Link
            href={hasAccess ? division.href : '#'}
            className={[
                'group relative flex h-full flex-col rounded-2xl border bg-white p-6 shadow-sm transition',
                hasAccess 
                    ? `hover:shadow-md hover:-translate-y-0.5 ${division.color}` 
                    : 'opacity-50 grayscale cursor-not-allowed',
            ].join(' ')}
            onClick={(e) => {
                if (!hasAccess) {
                    e.preventDefault();
                    alert(`Akses Ditolak! Divisi Anda (${user.division || 'Belum Diatur'}) tidak memiliki otoritas untuk ${division.label}`);
                }
            }}
        >
            <div className="flex items-start justify-between">
                <div className={['p-3 rounded-xl', division.iconBg].join(' ')}>
                    {division.icon}
                </div>
                <span className={`p-2 rounded-full ${hasAccess ? 'bg-red-50 text-[#ED1C24]' : 'bg-gray-100 text-gray-400'}`}>
                    {hasAccess ? '→' : '🔒'}
                </span>
            </div>

            <div className="mt-5">
                <div className="font-black uppercase italic text-gray-900 flex items-center gap-2">
                    {division.label}
                    {(user.role === 'superadmin' || (isStenterOperator && stenterGroup.includes(division.label) && user.division !== division.label)) && (
                        <span className="text-[8px] bg-red-600 text-white px-2 py-0.5 rounded-full not-italic uppercase font-bold">
                            {user.role === 'superadmin' ? 'ADMIN' : 'STENTER PATH'}
                        </span>
                    )}
                </div>
                <div className="mt-1 text-[10px] text-gray-500 font-medium">
                    {division.description}
                </div>
            </div>
        </Link>
    );
}

export default function DivisionSelector({ auth }) {
    const user = auth.user;
    const isStenterOperator = user.division?.toLowerCase() === 'stenter';

    // Pilih data yang akan ditampilkan berdasarkan role/divisi
    const displayItems = isStenterOperator ? stenterUnits : divisions;

    return (
        <AuthenticatedLayout user={auth.user}>
            <div className="py-8 bg-slate-50 min-h-screen">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 className="text-2xl font-black uppercase italic mb-6">
                        {isStenterOperator ? 'Pilih Unit Mesin Finishing' : 'Operator Division Selection'}
                    </h2>
                    
                    <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {displayItems.map((item) => (
                            <Link
                                key={item.key}
                                href={route('log.create', { division: item.key })}
                                className={`bg-white p-6 rounded-3xl border-2 shadow-sm transition-all hover:scale-105 ${item.color || 'border-gray-100'}`}
                            >
                                <div className="text-4xl mb-4">{item.icon}</div>
                                <h3 className="font-black uppercase italic text-lg">{item.label}</h3>
                                <p className="text-xs text-gray-500 uppercase font-bold tracking-tighter">
                                    {item.description}
                                </p>
                                <div className="mt-4 py-2 bg-red-600 text-white text-center rounded-xl text-[10px] font-black italic uppercase">
                                    Buka Logbook
                                </div>
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
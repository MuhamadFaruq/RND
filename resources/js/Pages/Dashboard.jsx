import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth, displayStats }) {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Admin Dashboard" />

            <div className="py-12 bg-slate-50 min-h-screen">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* Welcome Section */}
                    <div className="mb-10">
                        <h2 className="text-4xl font-black uppercase italic tracking-tighter text-slate-800 leading-none">
                            Selamat Datang, <span className="text-red-600">{auth.user.name}</span>
                        </h2>
                        <p className="mt-2 text-xs font-bold text-slate-400 uppercase tracking-[0.3em]">
                            Pusat Kendali Operasional Duniatex Group
                        </p>
                    </div>

                    {/* Stats Grid - Inilah yang tadi Error */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {displayStats.map((stat, index) => (
                            <div key={index} className="bg-white p-8 rounded-[2.5rem] shadow-xl border-t-8 border-slate-900 transition-all hover:scale-105">
                                <div className="text-4xl mb-4">{stat.icon}</div>
                                <h3 className="text-[10px] font-black uppercase text-slate-400 tracking-widest">{stat.label}</h3>
                                <p className={`text-5xl font-black italic tracking-tighter my-2 ${stat.color}`}>
                                    {stat.value}
                                </p>
                                <p className="text-[9px] font-bold text-slate-300 uppercase italic">
                                    {stat.desc}
                                </p>
                            </div>
                        ))}
                    </div>

                    {/* Quick Access / Tutorial Section */}
                    <div className="mt-12 bg-[#ED1C24] p-10 rounded-[3rem] text-white shadow-2xl relative overflow-hidden">
                        <div className="relative z-10 max-w-2xl">
                            <h3 className="text-2xl font-black uppercase italic tracking-tighter mb-4 italic">Butuh memantau produksi secara detail?</h3>
                            <p className="text-sm font-medium opacity-90 mb-8 leading-relaxed">
                                Halaman Dashboard ini hanya menampilkan ringkasan. Untuk melihat monitoring teknis, tabel SAP, dan status QC tiap order, silakan akses menu Monitoring di atas.
                            </p>
                            <a href={route('monitoring.dashboard')} className="px-8 py-3 bg-white text-red-600 font-black rounded-2xl text-xs uppercase italic tracking-widest hover:bg-slate-900 hover:text-white transition-colors">
                                Buka Monitoring Sekarang →
                            </a>
                        </div>
                        <div className="absolute right-[-50px] bottom-[-50px] text-[200px] opacity-10 font-black italic">DTX</div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
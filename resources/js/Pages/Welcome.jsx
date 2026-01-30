import { Link, Head } from '@inertiajs/react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Welcome to DUNIATEX" />
            
            <div className="relative min-h-screen bg-slate-900 flex flex-col items-center justify-center overflow-hidden font-sans">
                {/* Background Image with Darker Overlay */}
                <div className="absolute inset-0 z-0">
                    <div className="absolute inset-0 bg-gradient-to-b from-slate-900/90 via-slate-900/40 to-slate-900 z-10"></div>
                    <img 
                        src="/images/bg.jpg" // Pastikan gambar ini ada di public/images/
                        className="w-full h-full object-cover opacity-50 scale-105"
                        alt="Factory Background"
                    />
                </div>

                {/* Navbar Landing */}
                <nav className="absolute top-0 w-full p-8 flex justify-between items-center z-20">
                    <div className="flex items-center gap-4">
                        <div className="bg-white p-2 rounded-xl shadow-lg">
                            <img src="/images/lg.png" className="h-8 w-auto" alt="Logo" />
                        </div>
                        <div className="text-white">
                            <h1 className="font-black italic text-xl tracking-tighter leading-none uppercase">DUNIATEX</h1>
                            <p className="text-[9px] uppercase font-bold tracking-[0.2em] opacity-60">Manufacturing System</p>
                        </div>
                    </div>
                </nav>

                {/* Main Content */}
                <div className="relative z-10 text-center px-4 max-w-4xl">
                    <span className="inline-block px-4 py-1.5 bg-red-600/20 border border-red-600/30 text-red-500 text-[10px] font-black uppercase tracking-[0.3em] rounded-full mb-6 italic">
                        Pusat Kendali R&D Terintegrasi
                    </span>
                    
                    <h2 className="text-5xl md:text-7xl font-black text-white italic tracking-tighter leading-none mb-6">
                        Sistem Monitoring Produksi <br/>
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-red-500 to-red-700">Real-Time & Akurat</span>
                    </h2>

                    <p className="text-slate-300 text-sm md:text-lg font-medium max-w-2xl mx-auto leading-relaxed mb-10 opacity-80">
                        Solusi manajemen manufaktur Duniatex untuk efisiensi produksi yang lebih baik, mulai dari permintaan marketing hingga pemantauan deviasi teknis secara instan.
                    </p>

                    <div className="flex flex-col sm:flex-row items-center justify-center gap-6">
                        <Link 
                            href={auth.user ? route('dashboard') : route('login')}
                            className="px-10 py-4 bg-red-600 text-white font-black italic uppercase tracking-widest rounded-2xl shadow-[0_10px_40px_-10px_rgba(220,38,38,0.5)] hover:bg-red-700 hover:-translate-y-1 transition-all active:scale-95"
                        >
                            Mulai Monitoring
                        </Link>
                        
                        <div className="flex items-center gap-3 bg-white/5 backdrop-blur-md border border-white/10 p-2 rounded-2xl">
                            <div className="flex -space-x-2 p-1">
                                <div className="w-8 h-8 rounded-full bg-red-600 border-2 border-slate-900 flex items-center justify-center text-[8px] text-white font-bold">M</div>
                                <div className="w-8 h-8 rounded-full bg-blue-600 border-2 border-slate-900 flex items-center justify-center text-[8px] text-white font-bold">P</div>
                                <div className="w-8 h-8 rounded-full bg-slate-600 border-2 border-slate-900 flex items-center justify-center text-[8px] text-white font-bold">Q</div>
                            </div>
                            <p className="text-[10px] font-bold text-white uppercase italic tracking-tighter pr-4">
                                Jalur Aktif: Mkt, Prod, & QC
                            </p>
                        </div>
                    </div>
                </div>

                {/* Footer Decor */}
                <div className="absolute bottom-10 text-center z-10 w-full px-8 flex justify-between items-center">
                    <p className="text-[9px] font-bold text-white/20 uppercase tracking-[0.5em]">
                        Precision • Quality • Integrated
                    </p>
                    <p className="text-[9px] font-bold text-white/20 uppercase tracking-[0.2em]">
                        © 2026 PT. Duniatex Group
                    </p>
                </div>
            </div>
        </>
    );
}
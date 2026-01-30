import { useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden font-sans">
            
            {/* Layer Background Image - Dibuat sedikit lebih gelap agar form menonjol */}
            <div className="absolute inset-0 z-0">
                <div className="absolute inset-0 bg-gradient-to-tr from-slate-950 via-slate-900/60 to-slate-800/80 z-10"></div>
                <img 
                    src="/images/bg.jpg" 
                    className="w-full h-full object-cover opacity-60 scale-105 animate-pulse-slow"
                    style={{ animation: 'pulse 10s infinite alternate' }}
                    alt="Factory Background"
                />
            </div>

            <Head title="Log in - Duniatex" />

            {/* Card Login - Perbaikan Glassmorphism & Border */}
            <div className="w-full sm:max-w-md mt-6 px-10 py-12 bg-white/80 backdrop-blur-md rounded-[40px] shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-white/20 border-t-8 border-t-slate-900 z-10 transform transition-all duration-500 hover:shadow-red-500/10">
                
                {/* Logo & Header Section */}
                <div className="flex flex-col items-center mb-10">
                    <div className="bg-white p-4 rounded-3xl shadow-xl mb-4 border border-slate-100 transform transition-transform hover:rotate-3">
                        <img src="/images/lg.png" alt="Duniatex Logo" className="h-16 w-auto object-contain" />
                    </div>
                    <h1 className="text-2xl font-black italic text-slate-900 tracking-tighter uppercase leading-none">
                        Manufacturing System
                    </h1>
                    <div className="h-1 w-12 bg-red-600 mt-2 rounded-full"></div>
                    <p className="text-[9px] font-black text-slate-500 uppercase tracking-[0.4em] mt-3 text-center">
                        Duniatex Group Portal
                    </p>
                </div>

                {status && <div className="mb-4 font-bold text-sm text-green-600 text-center animate-bounce">{status}</div>}

                <form onSubmit={submit} className="space-y-5">
                    <div className="group">
                        <label className="text-[10px] font-black uppercase text-slate-600 ml-1 italic tracking-widest group-focus-within:text-red-600 transition-colors">
                            Email Address
                        </label>
                        <input
                            type="email"
                            value={data.email}
                            className="mt-1 block w-full border-slate-200 bg-white/50 focus:border-red-600 focus:ring-red-600 rounded-2xl shadow-sm font-bold text-sm py-3 px-4 transition-all focus:bg-white"
                            placeholder="Enter your email"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        {errors.email && <p className="mt-1 text-[10px] text-red-600 font-black uppercase italic animate-shake">{errors.email}</p>}
                    </div>

                    <div className="group">
                        <label className="text-[10px] font-black uppercase text-slate-600 ml-1 italic tracking-widest group-focus-within:text-red-600 transition-colors">
                            Security Password
                        </label>
                        <input
                            type="password"
                            value={data.password}
                            className="mt-1 block w-full border-slate-200 bg-white/50 focus:border-red-600 focus:ring-red-600 rounded-2xl shadow-sm font-bold text-sm py-3 px-4 transition-all focus:bg-white"
                            placeholder="••••••••"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        {errors.password && <p className="mt-1 text-[10px] text-red-600 font-black uppercase italic">{errors.password}</p>}
                    </div>

                    <div className="flex items-center justify-between mt-2">
                        <label className="flex items-center cursor-pointer group">
                            <input
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                className="rounded border-slate-300 text-red-600 shadow-sm focus:ring-red-600 cursor-pointer"
                                onChange={(e) => setData('remember', e.target.checked)}
                            />
                            <span className="ms-2 text-[10px] font-black text-slate-500 uppercase italic group-hover:text-slate-800 transition-colors">Ingat Saya</span>
                        </label>
                    </div>

                    <div className="mt-8">
                        <button
                            type="submit"
                            className={`w-full py-4 bg-slate-900 text-white rounded-2xl text-[12px] font-black italic uppercase tracking-[0.3em] transition-all flex items-center justify-center gap-3 hover:bg-red-600 hover:shadow-[0_10px_30px_-10px_rgba(220,38,38,0.5)] hover:-translate-y-1 active:scale-95 ${processing && 'opacity-50 cursor-not-allowed'}`}
                            disabled={processing}
                        >
                            {processing ? (
                                <svg className="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle><path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            ) : (
                                <>🚀 Autentikasi Masuk</>
                            )}
                        </button>
                    </div>
                </form>

                <div className="mt-12 text-center border-t border-slate-200 pt-6">
                    <p className="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                        © 2026 PT. Duniatex Group. <br/>
                        <span className="text-red-600/50">Precision • Integrated • Excellence</span>
                    </p>
                </div>
            </div>
        </div>
    );
}
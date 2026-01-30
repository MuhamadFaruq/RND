import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import Swal from 'sweetalert2';

export default function DivisionSelection({ auth }) {
    const divisions = [
        { id: 'knitting', name: 'Knitting', desc: 'Knitting Machine Production Log', icon: '🧶' },
        { id: 'dyeing', name: 'SCR/Dyeing', desc: 'Dyeing Process & Color Application', icon: '💧' },
        { id: 'stenter', name: 'Stenter', desc: 'Stenter Finishing (Belah)', icon: '🖼️' },
        { id: 'qc', name: 'Quality Control', desc: 'QC Evaluation & Lab Testing', icon: '🔬' },
    ];

    const userDivRaw = auth.user.division || "";
    const myDivision = userDivRaw.trim().toLowerCase();

    const handleVisit = (divId) => {
        const targetId = divId.toLowerCase();

        if (myDivision === targetId) {
            router.visit(route('log.create', divId));
        } else {
            Swal.fire({
                title: '<span style="color: #ED1C24; font-weight: 900; font-style: italic;">AKSES DITOLAK!</span>',
                html: `
                    <div style="text-align: left; font-family: sans-serif; border-top: 1px solid #eee; padding-top: 15px;">
                        <div style="background: #f8fafc; padding: 10px; border-radius: 12px; margin-bottom: 10px;">
                            <p style="margin: 0; font-size: 10px; font-weight: bold; color: #94a3b8; text-transform: uppercase;">Divisi Anda di Sistem</p>
                            <p style="margin: 0; font-size: 16px; font-weight: 900; color: #1e293b;">"${userDivRaw}"</p>
                        </div>
                        <div style="background: #fff1f2; padding: 10px; border-radius: 12px;">
                            <p style="margin: 0; font-size: 10px; font-weight: bold; color: #f43f5e; text-transform: uppercase;">Tujuan Klik</p>
                            <p style="margin: 0; font-size: 16px; font-weight: 900; color: #be123c;">"${divId}"</p>
                        </div>
                    </div>
                `,
                icon: 'error',
                confirmButtonColor: '#ED1C24',
                customClass: {
                    popup: 'rounded-[2rem] border-b-8 border-red-600 shadow-2xl',
                }
            });
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-black text-xl text-slate-800 uppercase italic">Operator Division Selection</h2>}
        >
            <Head title="Division Selection" />

            <div className="py-12 bg-slate-50 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    <div className="mb-8 p-4 bg-white border-2 border-dashed border-slate-200 rounded-2xl text-center">
                        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">System Diagnostic</p>
                        <p className="text-sm font-black text-red-600 uppercase">
                            Logged in as: {auth.user.name} | Division: "{userDivRaw}"
                        </p>
                    </div>

                    {/* Menggunakan lg:grid-cols-4 agar 4 kartu sejajar */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {divisions.map((div) => {
                            const isMyDivision = myDivision === div.id;
                            // DEFINISIKAN isQC DI SINI AGAR TIDAK ERROR
                            const isQC = div.id === 'qc';

                            return (
                                <div
                                    key={div.id}
                                    onClick={() => handleVisit(div.id)}
                                    className={`group p-8 rounded-[2.5rem] bg-white border-4 transition-all shadow-xl flex flex-col items-center text-center relative overflow-hidden ${
                                        isMyDivision 
                                        ? (isQC ? 'border-blue-500 cursor-pointer hover:scale-105 shadow-blue-100' : 'border-red-500 cursor-pointer hover:scale-105 shadow-red-100') 
                                        : 'border-slate-100 opacity-50 grayscale hover:grayscale-0'
                                    }`}
                                >
                                    <div className="text-5xl mb-4 group-hover:scale-110 transition-transform">{div.icon}</div>
                                    <h3 className="text-xl font-black text-slate-900 uppercase italic leading-none">{div.name}</h3>
                                    <p className="text-[10px] text-slate-400 font-bold uppercase mt-3">{div.desc}</p>
                                    
                                    {isMyDivision ? (
                                        <div className={`mt-5 text-white text-[10px] font-black px-5 py-2 rounded-2xl uppercase italic animate-pulse ${isQC ? 'bg-blue-600' : 'bg-red-600'}`}>
                                            Masuk Logbook
                                        </div>
                                    ) : (
                                        <div className="mt-5 text-[9px] font-black text-slate-400 uppercase italic bg-slate-100 px-4 py-1 rounded-lg">
                                            Akses Terkunci
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
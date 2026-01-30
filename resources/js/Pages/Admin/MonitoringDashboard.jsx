import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState, useEffect, useMemo } from 'react'; // Tambahkan useMemo
import axios from 'axios';

export default function MonitoringDashboard({ auth, stats, orders, weeklyTrends }) {
    const [realTimeData, setRealTimeData] = useState({ knitting: 0, dyeing: 0, stenter: 0, qc: 0, activities: [] });
    const [searchTerm, setSearchTerm] = useState('');
    const [filterStatus, setFilterStatus] = useState('all'); // State baru untuk filter status

    // Logika Filter Gabungan (Pencarian + Status)
    const filteredOrders = useMemo(() => {
        return orders.filter(order => {
            const matchesSearch = 
                order.sap_no?.toString().toLowerCase().includes(searchTerm.toLowerCase()) ||
                order.pelanggan?.toLowerCase().includes(searchTerm.toLowerCase()) ||
                order.art_no?.toLowerCase().includes(searchTerm.toLowerCase());
            
            const matchesStatus = 
                filterStatus === 'all' || 
                order.status?.toLowerCase() === filterStatus.toLowerCase();

            return matchesSearch && matchesStatus;
        });
    }, [searchTerm, filterStatus, orders]);

    const fetchStats = async () => {
        try {
            const response = await axios.get(route('api.monitoring.stats'));
            setRealTimeData(response.data.data);
        } catch (error) {
            console.error("Gagal mengambil data monitoring", error);
        }
    };

    useEffect(() => {
        fetchStats();
        const interval = setInterval(fetchStats, 30000);
        return () => clearInterval(interval);
    }, []);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Real-Time Production Monitoring" />
            
            <div className="py-6 bg-slate-50 min-h-screen">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    
                    {/* Header & Search */}
                    <div className="bg-[#ED1C24] rounded-[2rem] p-8 mb-4 text-white flex justify-between items-center shadow-xl">
                        <div className="flex items-center gap-3">
                            <span className="text-3xl">📊</span>
                            <h2 className="text-3xl font-black uppercase italic tracking-tighter">DUNIATEX </h2>
                        </div>
                        <div className="relative">
                            <input 
                                type="text"
                                placeholder="Cari SAP, Pelanggan..."
                                className="pl-10 pr-6 py-3 rounded-2xl border-none text-slate-800 text-xs font-bold w-72 shadow-inner focus:ring-2 focus:ring-slate-900"
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                            />
                            <span className="absolute left-4 top-3.5 text-slate-400">🔍</span>
                        </div>
                    </div>

                    {/* --- LETAKKAN DEBUGGER DI SINI --- */}
                    <div className="bg-black text-lime-400 p-4 rounded-2xl mb-6 font-mono text-[10px] shadow-2xl border-2 border-lime-900/50">
                        <p className="font-black uppercase mb-1 border-b border-lime-900 pb-1">🔧 System Data Debugger</p>
                        <pre>weeklyTrends: {JSON.stringify(weeklyTrends, null, 2)}</pre>
                    </div>
                    {/* --- AKHIR DEBUGGER --- */}

                    {/* Filter Status */}
                    <div className="flex gap-2 mb-8 overflow-x-auto pb-2">
                        {['all', 'pending', 'in-progress', 'completed', 'overdue'].map((status) => (
                            <button
                                key={status}
                                onClick={() => setFilterStatus(status)}
                                className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all ${
                                    filterStatus === status 
                                    ? 'bg-slate-900 text-white shadow-lg scale-105' 
                                    : 'bg-white text-slate-400 hover:bg-slate-100'
                                }`}
                            >
                                {status === 'all' ? 'Tampilkan Semua' : status}
                            </button>
                        ))}
                    </div>

                    {/* STATS GRID REAL-TIME */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <StatCard title="Input Knitting" value={realTimeData.knitting} color="border-l-blue-500" icon="🧶" />
                        <StatCard title="Input Dyeing" value={realTimeData.dyeing} color="border-l-indigo-500" icon="🧪" />
                        <StatCard title="Input Stenter" value={realTimeData.stenter} color="border-l-orange-500" icon="🔥" />
                        <StatCard title="Input QC" value={realTimeData.qc} color="border-l-yellow-500" icon="🔍" />
                    </div>

                    {/* --- TAMBAHKAN DI SINI: MODUL GRAFIK TREN MINGGUAN --- */}
                    {/* --- MODUL GRAFIK TREN MINGGUAN --- */}
<div className="bg-white rounded-[2rem] p-8 mb-8 shadow-sm border border-slate-100">
    <div className="mb-6">
        <h3 className="text-sm font-black uppercase italic tracking-tighter text-slate-800">
            📈 Productivity Trends
        </h3>
        <p className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Aktivitas input 7 hari terakhir</p>
    </div>

    <div className="flex items-end justify-between h-48 gap-3 px-4 bg-slate-50/50 rounded-3xl p-6">
        {Array.isArray(weeklyTrends) && weeklyTrends.length > 0 ? (
            weeklyTrends.map((data, idx) => {
                // Kalkulasi Max untuk skala (Proteksi agar tidak bagi nol)
                const maxTotal = Math.max(...weeklyTrends.map(d => d.total || 0)) || 1;
                const barHeight = ((data.total || 0) / maxTotal) * 100;
                
                return (
                    <div key={idx} className="flex-1 flex flex-col items-center gap-3 group">
                        <div className="relative w-full flex flex-col items-center justify-end h-full">
                            {/* Tooltip Angka yang muncul saat hover */}
                            <div className="absolute -top-10 bg-slate-900 text-white text-[10px] px-2 py-1 rounded shadow-xl opacity-0 group-hover:opacity-100 transition-opacity font-bold z-10 whitespace-nowrap">
                                {data.total} INPUTS
                            </div>
                            
                            {/* Batang Grafik */}
                            <div 
                                className="w-full max-w-[40px] rounded-2xl transition-all duration-700 shadow-inner"
                                style={{ 
                                    height: `${barHeight}%`, 
                                    minHeight: '4px',
                                    backgroundColor: data.total > 0 ? '#ED1C24' : '#e2e8f0' 
                                }}
                            ></div>
                        </div>
                        <span className="text-[9px] font-black uppercase text-slate-400 italic">
                            {data.day}
                        </span>
                    </div>
                );
            })
        ) : (
            <div className="w-full flex flex-col items-center justify-center text-slate-300 italic py-10">
                <span className="text-4xl mb-2">📊</span>
                <p className="text-xs font-bold uppercase tracking-widest">Data Tren Belum Tersedia</p>
            </div>
        )}
    </div>
</div>
                    {/* --- AKHIR MODUL GRAFIK --- */}

                    {/* GRID CONTENT: TABLE & LIVE FEED */}
                    <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                        
                        {/* Tabel Monitoring */}
                        <div className="lg:col-span-3">
                            <div className="bg-white rounded-[2rem] shadow-sm border border-slate-100 overflow-hidden">
                                <div className="bg-[#ED1C24] p-4 flex justify-between items-center px-8">
                                    <div className="flex gap-12 text-[10px] font-black text-white uppercase italic">
                                        <span className="w-20">SAP NO</span>
                                        <span className="w-24">ART NO</span>
                                        <span className="flex-1">PELANGGAN</span>
                                        <span className="w-24">STATUS</span>
                                    </div>
                                </div>
                                
                                <div className="divide-y divide-slate-50">
                                    {filteredOrders.length > 0 ? filteredOrders.map((order) => (
                                        <div key={order.id} className="flex gap-12 p-5 px-8 items-center text-[11px] font-bold text-slate-700 hover:bg-slate-50 transition-colors animate-fade-in">
                                            <span className="w-20 text-blue-600">{order.sap_no}</span>
                                            <span className="w-24 uppercase">{order.art_no}</span>
                                            <span className="flex-1 uppercase">{order.pelanggan}</span>
                                            <span className={`w-24 px-4 py-1 text-center rounded-full text-[9px] uppercase font-black tracking-tighter text-white ${getStatusColor(order.status)}`}>
                                                {order.status}
                                            </span>
                                        </div>
                                    )) : (
                                        <div className="p-10 text-center text-slate-400 italic text-sm font-bold uppercase">
                                            Data tidak ditemukan...
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Live Activity Feed */}
                        <div className="lg:col-span-1">
                            <div className="bg-slate-900 rounded-[2rem] p-6 shadow-xl sticky top-24">
                                <h3 className="text-white font-black italic uppercase text-xs mb-6 flex items-center gap-2">
                                    <span className="flex h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
                                    Live Activity
                                </h3>
                                <div className="space-y-6">
                                    {(realTimeData.activities || []).map((activity) => (
                                        <div key={activity.id} className="relative pl-6 border-l border-white/10 py-1">
                                            <div className="absolute left-[-5px] top-2 w-2 h-2 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.8)]"></div>
                                            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-tighter">
                                                {activity.time} — {activity.division}
                                            </p>
                                            <p className="text-xs text-white font-black italic mt-0.5 uppercase tracking-tighter">
                                                {activity.operator}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

// Helper untuk warna status
function getStatusColor(status) {
    const s = status?.toLowerCase();
    if (s === 'completed') return 'bg-green-500';
    if (s === 'qc' || s === 'pengujian') return 'bg-yellow-500';
    if (s === 'pending') return 'bg-slate-400';
    return 'bg-amber-400'; // Default untuk in-progress
}

function StatCard({ title, value, color, icon }) {
    return (
        <div className={`bg-white p-6 rounded-[1.5rem] border-l-8 ${color} shadow-sm flex justify-between items-center transition-transform hover:scale-105`}>
            <div>
                <p className="text-[10px] font-black text-slate-400 uppercase tracking-tighter mb-1">{title}</p>
                <p className="text-3xl font-black text-slate-800 italic leading-none">{value}</p>
            </div>
            <div className="text-2xl opacity-80">{icon}</div>
        </div>
    );
}
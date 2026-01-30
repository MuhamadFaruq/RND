import React, { useState, useEffect, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';

const DUNIATEX_RED = '#ED1C24';

export default function Monitoring({ auth, orders: initialOrders = [], summaryStats = [] }) {
    const [orders, setOrders] = useState(initialOrders);
    const [autoRefresh, setAutoRefresh] = useState(true);
    const [filterStatus, setFilterStatus] = useState('all');
    const [searchTerm, setSearchTerm] = useState('');
    const [exporting, setExporting] = useState(false);
    const [selectedDetail, setSelectedDetail] = useState(null);
    
    // MODUL BARU: State untuk navigasi antar monitoring
    const [activeView, setActiveView] = useState('executive'); // executive, rajut, warna

    useEffect(() => {
        setOrders(initialOrders || []);
    }, [initialOrders]);

    useEffect(() => {
        if (!autoRefresh) return;
        const interval = setInterval(() => {
            router.reload({ 
                only: ['orders', 'summaryStats'],
                preserveState: true,
                preserveScroll: true 
            });
        }, 10000);
        return () => clearInterval(interval);
    }, [autoRefresh]);

    const handleExportExcel = async () => {
        setExporting(true);
        try {
            const response = await axios.get('/dashboard/export', { responseType: 'blob' });
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'Laporan_Produksi.xlsx');
            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (error) {
            console.error('Export error:', error);
            alert('Gagal mengekspor data.');
        } finally {
            setExporting(false);
        }
    };

    const filteredOrders = useMemo(() => {
        return (orders || []).filter((order) => {
            const matchesStatus = filterStatus === 'all' || order.status === filterStatus;
            const matchesSearch =
                (order.sap_no?.toString() || '').includes(searchTerm) ||
                (order.pelanggan?.toLowerCase() || '').includes(searchTerm.toLowerCase()) ||
                (order.art_no?.toLowerCase() || '').includes(searchTerm.toLowerCase());
            return matchesStatus && matchesSearch;
        });
    }, [orders, filterStatus, searchTerm]);

    const getStatusBadge = (status) => {
        const statusMap = {
            pending: { color: 'bg-gray-500', label: 'Pending', icon: '⏳' },
            knitting: { color: 'bg-blue-500', label: 'Knitting', icon: '🧵' },
            dyeing: { color: 'bg-indigo-500', label: 'Dyeing', icon: '🎨' },
            finishing: { color: 'bg-purple-500', label: 'Finishing', icon: '✨' },
            qc: { color: 'bg-yellow-500', label: 'QC', icon: '🔍' },
            completed: { color: 'bg-green-500', label: 'Completed', icon: '✅' },
        };
        return statusMap[status] || statusMap.pending;
    };

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Production Monitoring Dashboard" />

            <div className="py-8 px-4 bg-gray-50 min-h-screen font-sans antialiased text-gray-900 relative">
                <div className="max-w-7xl mx-auto">
                    {/* Header Section */}
                    <div className="mb-6 rounded-xl shadow-lg overflow-hidden transition-all duration-300" style={{ backgroundColor: DUNIATEX_RED }}>
                        <div className="px-6 py-8 flex flex-col md:flex-row justify-between items-center gap-4">
                            <div>
                                <h1 className="text-2xl md:text-3xl font-extrabold text-white flex items-center">
                                    <span className="mr-3">📊</span> DUNIATEX - Real Time Production Monitoring
                                </h1>
                                <p className="text-red-100 mt-2 font-medium uppercase tracking-widest text-[10px]">
                                    Master Dashboard • Total Orders: {orders.length}
                                </p>
                            </div>
                            
                            {/* Filter akses tombol bagi selain operator */}
                            <div className="flex gap-2">
                                {auth.user.role !== 'operator' && (
                                    <button
                                        onClick={handleExportExcel}
                                        disabled={exporting}
                                        className="flex items-center gap-2 bg-white text-red-600 px-4 py-2 rounded-full font-bold text-[10px] shadow-sm hover:bg-gray-50 transition"
                                    >
                                        {exporting ? 'MEMPROSES...' : 'DOWNLOAD EXCEL'}
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* MODUL BARU: NAVIGATION TABS */}
                    <div className="flex bg-gray-200 p-1 rounded-2xl mb-8 w-fit shadow-inner">
                        <button onClick={() => setActiveView('executive')} className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all ${activeView === 'executive' ? 'bg-white text-red-600 shadow-md' : 'text-gray-500 hover:text-gray-700'}`}>Summary</button>
                        <button onClick={() => setActiveView('rajut')} className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all ${activeView === 'rajut' ? 'bg-blue-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700'}`}>🧶 Rajut</button>
                        <button onClick={() => setActiveView('warna')} className={`px-6 py-2 rounded-xl text-[10px] font-black uppercase transition-all ${activeView === 'warna' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:text-gray-700'}`}>🎨 Warna</button>
                    </div>

                    {/* RENDER EXECUTIVE VIEW (Isi Dashboard Asli Anda) */}
                    {activeView === 'executive' && (
                        <div className="animate-in fade-in slide-in-from-bottom duration-500">
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                {(summaryStats || []).map((stat, idx) => (
                                    <div key={idx} className="bg-white p-6 rounded-xl shadow-sm border-l-4 border-[#ED1C24]">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="text-[10px] font-bold text-gray-500 uppercase tracking-widest">{stat.label}</p>
                                                <p className={`text-2xl font-black mt-1 ${stat.color}`}>{stat.value}</p>
                                            </div>
                                            <div className={`p-3 rounded-xl bg-gray-50 ${stat.color}`}>
                                                <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={stat.icon} />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Kontrol & Tabel Utama Anda */}
                            <ControlsSection searchTerm={searchTerm} setSearchTerm={setSearchTerm} filterStatus={filterStatus} setFilterStatus={setFilterStatus} autoRefresh={autoRefresh} setAutoRefresh={setAutoRefresh} />
                            <MainProductionTable orders={filteredOrders} setSelectedDetail={setSelectedDetail} getStatusBadge={getStatusBadge} />
                        </div>
                    )}

                    {/* RENDER MODUL MONITORING RAJUT */}
                    {activeView === 'rajut' && (
                        <div className="animate-in fade-in duration-500">
                             <TableMonitoringRajut orders={filteredOrders} />
                        </div>
                    )}

                    {/* RENDER MODUL MONITORING WARNA */}
                    {activeView === 'warna' && (
                        <div className="animate-in fade-in duration-500">
                             <TableMonitoringWarna orders={filteredOrders} />
                        </div>
                    )}
                </div>

                {/* MODAL DETAIL VIEW (Tetap ada seperti sebelumnya) */}
                {selectedDetail && <DetailModal selectedDetail={selectedDetail} setSelectedDetail={setSelectedDetail} />}
            </div>
        </AuthenticatedLayout>
    );
}

// --- SUB-KOMPONEN BARU UNTUK MONITORING RAJUT ---
const TableMonitoringRajut = ({ orders }) => (
    <div className="bg-white rounded-xl shadow-xl overflow-hidden border border-blue-100">
        <div className="p-4 bg-blue-600 text-white font-black text-xs uppercase italic tracking-widest">🧶 Data Monitoring Rajut (Knitting)</div>
        <div className="overflow-x-auto">
            <table className="w-full">
                <thead>
                    <tr className="bg-gray-900 text-white text-[9px] uppercase font-black tracking-widest text-left">
                        <th className="px-4 py-4">SAP / ART</th>
                        <th className="px-4 py-4">Pelanggan / MKT</th>
                        <th className="px-4 py-4">Konstruksi Greige</th>
                        <th className="px-4 py-4">Mesin / Kelompok</th>
                        <th className="px-4 py-4">T. Lebar / B-B / T. GSM</th>
                        <th className="px-4 py-4">Hasil (ROLL / KG)</th>
                        <th className="px-4 py-4 bg-red-600 italic">Timeline DPF3</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 uppercase text-[9px] font-bold text-gray-700">
                    {orders.map((o, i) => (
                        <tr key={i} className="hover:bg-blue-50 transition-colors border-b">
                            <td className="px-4 py-3">
                                <div className="text-blue-600 font-black">{o.sap_no}</div>
                                <div className="text-gray-400">{o.art_no}</div>
                            </td>
                            <td className="px-4 py-3">
                                <div>{o.pelanggan}</div>
                                <div className="text-gray-400 text-[8px]">{o.mkt || 'N/A'}</div>
                            </td>
                            <td className="px-4 py-3">{o.konstruksi_greige || '-'}</td>
                            <td className="px-4 py-3">
                                <div>{o.mesin_rajut || '-'}</div>
                                <div className="text-gray-400 italic text-[8px]">{o.kelompok_mesin || '-'}</div>
                            </td>
                            <td className="px-4 py-3 italic">
                                {o.target_lebar} CM | {o.belah_bulat || '-'} | {o.target_gramasi} GSM
                            </td>
                            <td className="px-4 py-3">
                                <span className="bg-gray-100 px-2 py-1 rounded-md mr-1">{o.roll || 0} R</span>
                                <span className="bg-blue-100 px-2 py-1 rounded-md text-blue-700">{o.kg || 0} KG</span>
                            </td>
                            <td className="px-4 py-3 font-black text-red-600 italic bg-gray-50 text-center">
                                {o.timeline || 'TBA'}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    </div>
);

// --- SUB-KOMPONEN BARU UNTUK MONITORING WARNA ---
const TableMonitoringWarna = ({ orders }) => (
    <div className="bg-white rounded-xl shadow-xl overflow-hidden border border-indigo-100">
        <div className="p-4 bg-indigo-600 text-white font-black text-xs uppercase italic tracking-widest">🎨 Data Monitoring Warna (Dyeing)</div>
        <div className="overflow-x-auto">
            <table className="w-full">
                <thead>
                    <tr className="bg-gray-900 text-white text-[9px] uppercase font-black tracking-widest text-left">
                        <th className="px-4 py-4">SAP / ART</th>
                        <th className="px-4 py-4">Warna / Handfeel</th>
                        <th className="px-4 py-4">Target (L/G/BB)</th>
                        <th className="px-4 py-4">Kg / Roll Aktual</th>
                        <th className="px-4 py-4 text-center bg-indigo-800">Logistik Flow</th>
                        <th className="px-4 py-4 bg-red-600 italic">Timeline</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100 uppercase text-[9px] font-bold text-gray-700">
                    {orders.map((o, i) => (
                        <tr key={i} className="hover:bg-indigo-50 transition-colors border-b">
                            <td className="px-4 py-3">
                                <div className="text-indigo-700 font-black">{o.sap_no}</div>
                                <div className="text-gray-400 font-normal italic">{o.pelanggan}</div>
                            </td>
                            <td className="px-4 py-3">
                                <div className="text-gray-900 font-black">{o.warna}</div>
                                <div className="text-[8px] bg-gray-100 px-1 inline-block rounded uppercase tracking-widest text-gray-500">
                                    HF: {o.handfeel || '-'}
                                </div>
                            </td>
                            <td className="px-4 py-3 italic text-gray-500">
                                {o.target_lebar} / {o.target_gramasi} / {o.belah_bulat}
                            </td>
                            <td className="px-4 py-3 font-black text-indigo-700">
                                {o.kg || 0} KG <span className="text-gray-400 font-normal">({o.roll || 0} R)</span>
                            </td>
                            <td className="px-0 py-0 bg-gray-50 border-r border-l">
                                <div className="grid grid-cols-3 h-full text-[8px] text-center font-black uppercase items-center">
                                    <div className="py-3 border-r">
                                        <span className="text-gray-400 block mb-0.5 font-normal">Kirim DDT2</span>
                                        {o.kirim_ddt2 || '-'}
                                    </div>
                                    <div className="py-3 border-r">
                                        <span className="text-gray-400 block mb-0.5 font-normal">Terima DPF3</span>
                                        {o.terima_dpf3 || '-'}
                                    </div>
                                    <div className="py-3 text-green-600">
                                        <span className="text-gray-400 block mb-0.5 font-normal">Finish</span>
                                        {o.tgl_selesai || '-'}
                                    </div>
                                </div>
                            </td>
                            <td className="px-4 py-3 font-black text-red-600 italic text-center bg-gray-100">
                                {o.timeline || 'TBA'}
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    </div>
);

// --- KOMPONEN LAMA ANDA YANG DIPISAH AGAR RAPI ---
const ControlsSection = ({ searchTerm, setSearchTerm, filterStatus, setFilterStatus, autoRefresh, setAutoRefresh }) => (
    <div className="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="md:col-span-2">
                <label className="block text-[10px] font-black text-gray-700 mb-2 uppercase tracking-tighter">🔍 Pencarian Cepat</label>
                <input type="text" placeholder="Cari..." value={searchTerm} onChange={(e) => setSearchTerm(e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-xs" />
            </div>
            <div>
                <label className="block text-[10px] font-black text-gray-700 mb-2 uppercase tracking-tighter">Filter Tahapan</label>
                <select value={filterStatus} onChange={(e) => setFilterStatus(e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-xs font-bold">
                    <option value="all">Semua Status</option>
                    <option value="pending">Pending</option><option value="completed">Completed</option>
                </select>
            </div>
            <div>
                <label className="block text-[10px] font-black text-gray-700 mb-2 uppercase tracking-tighter">Auto Refresh</label>
                <button onClick={() => setAutoRefresh(!autoRefresh)} className={`w-full px-4 py-2 rounded-lg font-bold text-xs ${autoRefresh ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-800'}`}>
                    {autoRefresh ? '🟢 AKTIF' : '🔴 MATI'}
                </button>
            </div>
        </div>
    </div>
);

const MainProductionTable = ({ orders, setSelectedDetail, getStatusBadge }) => {
    // Fungsi untuk mengecek selisih (Tolerance: 2cm untuk Lebar, 5gsm untuk GSM)
    const getAlertClass = (target, actual, tolerance) => {
        if (!actual) return 'text-gray-400';
        const diff = Math.abs(parseFloat(target) - parseFloat(actual));
        return diff > tolerance ? 'text-red-600 font-black animate-pulse' : 'text-green-600 font-bold';
    };

    return (
        <div className="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-100">
            <div className="overflow-x-auto">
                <table className="w-full">
                    <thead>
                        <tr className="text-white text-left text-[10px] uppercase font-black tracking-widest" style={{ backgroundColor: DUNIATEX_RED }}>
                            <th className="px-4 py-5">SAP No</th>
                            <th className="px-4 py-5">Art No</th>
                            <th className="px-4 py-5">Pelanggan</th>
                            <th className="px-4 py-5 text-center">Status</th>
                            <th className="px-4 py-5 text-center italic">Lebar (T vs A)</th>
                            <th className="px-4 py-5 text-center italic">GSM (T vs A)</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 uppercase text-[10px] font-bold">
                        {orders.map((order, idx) => {
                            const statusInfo = getStatusBadge(order.status);
                            return (
                                <tr key={idx} className="hover:bg-red-50/50 transition-colors cursor-pointer" onClick={() => setSelectedDetail(order)}>
                                    <td className="px-4 py-4 font-black text-blue-600">{order.sap_no}</td>
                                    <td className="px-4 py-4 text-gray-600">{order.art_no || '-'}</td>
                                    <td className="px-4 py-4 text-gray-800">{order.pelanggan}</td>
                                    <td className="px-4 py-4 text-center">
                                        <span className={`px-3 py-1 rounded-full text-white ${statusInfo.color}`}>{statusInfo.label}</span>
                                    </td>
                                    {/* ALERT LEBAR: Merah berkedip jika selisih > 2cm */}
                                    <td className="px-4 py-4 text-center border-l bg-gray-50/30">
                                        <div className="text-gray-400 text-[8px]">Target: {order.target_lebar}</div>
                                        <div className={getAlertClass(order.target_lebar, order.knitting_width, 2)}>
                                            {order.knitting_width ? `ACT: ${order.knitting_width}` : 'WAITING...'}
                                        </div>
                                    </td>
                                    {/* ALERT GSM: Merah berkedip jika selisih > 5gsm */}
                                    <td className="px-4 py-4 text-center border-l bg-gray-50/30">
                                        <div className="text-gray-400 text-[8px]">Target: {order.target_gramasi}</div>
                                        <div className={getAlertClass(order.target_gramasi, order.knitting_gsm, 5)}>
                                            {order.knitting_gsm ? `ACT: ${order.knitting_gsm}` : 'WAITING...'}
                                        </div>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

const DetailModal = ({ selectedDetail, setSelectedDetail }) => (
    <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-sm z-[999] flex items-center justify-center p-4">
        <div className="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            {/* Header Modal */}
            <div className="bg-[#ED1C24] p-6 text-white flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h3 className="text-xl font-black uppercase tracking-tighter">Detail Spesifikasi Order</h3>
                    <p className="text-[10px] font-bold opacity-80 uppercase tracking-widest">SAP: {selectedDetail.sap_no} • ART: {selectedDetail.art_no}</p>
                </div>
                <button onClick={() => setSelectedDetail(null)} className="hover:rotate-90 transition-transform duration-300 font-black text-2xl">✕</button>
            </div>

            <div className="p-8 space-y-8 uppercase text-[10px] font-bold">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
                    
                    {/* I. INFORMASI MARKETING & PELANGGAN */}
                    <div className="space-y-4">
                        <h4 className="text-red-600 border-b-2 border-red-100 pb-2 font-black flex items-center gap-2">
                            <span>📋</span> I. INFORMASI UMUM (MARKETING)
                        </h4>
                        <div className="space-y-3">
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">PELANGGAN:</span> 
                                <span className="text-gray-900">{selectedDetail.pelanggan}</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">MARKETING (MKT):</span> 
                                <span className="text-gray-900">{selectedDetail.mkt || '-'}</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">WARNA:</span> 
                                <span className="text-gray-900">{selectedDetail.warna}</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1 text-red-600">
                                <span className="font-black">TIMELINE TARGET:</span> 
                                <span className="font-black italic">{selectedDetail.tanggal || '-'}</span>
                            </div>
                        </div>
                    </div>

                    {/* II. SPESIFIKASI TEKNIS TARGET */}
                    <div className="space-y-4">
                        <h4 className="text-red-600 border-b-2 border-red-100 pb-2 font-black flex items-center gap-2">
                            <span>⚙️</span> II. TARGET PRODUKSI & SPESIFIKASI
                        </h4>
                        <div className="space-y-3">
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">KONSTRUKSI GREIGE:</span> 
                                <span className="text-gray-900">{selectedDetail.konstruksi_greige || '-'}</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">JENIS (BELAH/BULAT):</span> 
                                <span className="text-gray-900">{selectedDetail.belah_bulat || '-'}</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">TARGET LEBAR:</span> 
                                <span className="text-gray-900 font-black">{selectedDetail.target_lebar} CM</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">TARGET GSM:</span> 
                                <span className="text-gray-900 font-black">{selectedDetail.target_gramasi} GSM</span>
                            </div>
                            <div className="flex justify-between border-b border-gray-100 pb-1">
                                <span className="text-gray-400">TARGET QUANTITY:</span> 
                                <span className="text-blue-600 font-black">
                                    {selectedDetail.roll_target} ROLL / {selectedDetail.kg_target} KG
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* III. STATUS TRACKING */}
                <div className="bg-gray-50 p-6 rounded-2xl border border-gray-100">
                    <h4 className="text-gray-400 mb-4 font-black tracking-widest text-[9px]">III. CURRENT PRODUCTION STATUS</h4>
                    <div className="flex items-center gap-4">
                        <div className="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                className="h-full bg-green-500 transition-all duration-1000" 
                                style={{ width: selectedDetail.status === 'completed' ? '100%' : '50%' }}
                            ></div>
                        </div>
                        <span className="text-green-600 font-black">{selectedDetail.status.toUpperCase()}</span>
                    </div>
                </div>

                <button 
                    onClick={() => setSelectedDetail(null)} 
                    className="w-full py-4 bg-gray-900 text-white rounded-2xl font-black hover:bg-black transition-all shadow-lg active:scale-[0.98]"
                >
                    TUTUP DETAIL INFORMASI
                </button>
            </div>
        </div>
    </div>
);

const TableMonitoringQE = ({ orders }) => {
    const [rejectReason, setRejectReason] = useState({});

    const processAction = (id, action) => {
        const msg = action === 'approve' ? 'Approve barang ini?' : 'Reject barang ini?';
        if (confirm(msg)) {
            router.post(`/dashboard/qe-action/${id}`, {
                action: action,
                reason: rejectReason[id] || 'Kualitas tidak sesuai standar'
            });
        }
    };

    return (
        <div className="bg-white rounded-xl shadow-xl overflow-hidden border border-emerald-100">
            <div className="p-4 bg-emerald-600 text-white font-black text-xs uppercase italic tracking-widest flex justify-between">
                <span>🛡️ Final Quality Evaluation Control</span>
                <span className="opacity-70">Industrial Standard SOP</span>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-[9px] font-bold uppercase">
                    <thead className="bg-gray-900 text-white">
                        <tr>
                            <th className="p-4">SAP / Pelanggan</th>
                            <th className="p-4 text-center italic">Spec Target vs Actual</th>
                            <th className="p-4 text-center">Hasil Final QE</th>
                            <th className="p-4 text-center bg-emerald-800">Decision Center</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {orders.filter(o => o.status !== 'completed').map((o) => (
                            <tr key={o.id} className="hover:bg-gray-50 transition-colors">
                                <td className="p-4">
                                    <div className="text-emerald-700 font-black">#{o.sap_no}</div>
                                    <div className="text-gray-400">{o.pelanggan}</div>
                                </td>
                                <td className="p-4 text-center">
                                    <div className="text-red-500">T: {o.target_lebar} / {o.target_gramasi}</div>
                                    <div className="text-emerald-600">A: {o.lebar_qe} / {o.gramasi_qe}</div>
                                </td>
                                <td className="p-4">
                                    <div className="normal-case italic text-gray-500 mb-2">{o.note_qe || 'Tidak ada catatan'}</div>
                                    <input 
                                        type="text" 
                                        placeholder="Tulis alasan jika reject..." 
                                        className="w-full p-1 text-[8px] border rounded"
                                        onChange={(e) => setRejectReason({...rejectReason, [o.id]: e.target.value})}
                                    />
                                </td>
                                <td className="p-4 bg-emerald-50/30 text-center">
                                    <div className="flex flex-col gap-2">
                                        <button 
                                            onClick={() => processAction(o.id, 'approve')}
                                            className="bg-emerald-600 text-white py-2 rounded-lg font-black hover:bg-emerald-700 transition-all active:scale-95 shadow-sm"
                                        >
                                            ✅ APPROVE
                                        </button>
                                        <button 
                                            onClick={() => processAction(o.id, 'reject')}
                                            className="bg-red-100 text-red-600 py-2 rounded-lg font-black hover:bg-red-200 transition-all active:scale-95"
                                        >
                                            ❌ REJECT / REWORK
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};


import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useState, useEffect, useMemo } from 'react';
import axios from 'axios';

export default function Dashboard({ auth, orders = [], weeklyTrends = [] }) { 
    const [exporting, setExporting] = useState(false);
    
    // Filter akses berdasarkan role
    const isOperator = auth.user.role === 'operator';

    const getStatusBadge = (status) => {
        const statusConfig = {
            pending: { label: 'Pending', color: 'bg-gray-100 text-gray-800' },
            knitting: { label: 'In Knitting', color: 'bg-yellow-100 text-yellow-800' },
            dyeing: { label: 'In Dyeing', color: 'bg-blue-100 text-blue-800' },
            finishing: { label: 'In Finishing', color: 'bg-purple-100 text-purple-800' },
            qc: { label: 'In QC', color: 'bg-indigo-100 text-indigo-800' },
            completed: { label: 'Completed', color: 'bg-green-100 text-green-800' },
        };
        const config = statusConfig[status] || statusConfig.pending;
        return (
            <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${config.color}`}>
                {config.label}
            </span>
        );
    };

    const handleExport = async () => {
        setExporting(true);
        try {
            const response = await axios.get(route('dashboard.export'), { responseType: 'blob' });
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `DUNIATEX-RND-${new Date().toISOString().split('T')[0]}.xlsx`);
            document.body.appendChild(link);
            link.click();
            link.remove();
        } catch (error) {
            alert('Gagal mengekspor data.');
        } finally {
            setExporting(false);
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div>
                    <h2 className="text-2xl font-extrabold tracking-tight text-gray-900 uppercase italic">
                        Master Monitoring Dashboard - LIVE
                    </h2>
                    <p className="mt-1 text-sm text-gray-600">Pusat Kendali Produksi R&D Duniatex</p>
                </div>
            }
        >
            <Head title="Master Monitoring Dashboard" />

            <div className="py-6 bg-slate-50 min-h-screen">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    
                    {/* 1. Debugger Data (Hapus jika sudah muncul grafiknya) */}
                    <div className="bg-black text-lime-400 p-3 rounded-xl mb-6 font-mono text-[10px]">
                        PREVIEW DATA: {JSON.stringify(weeklyTrends)}
                    </div>

                    {/* 2. Summary Stats */}
                    <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div className="text-xs font-black uppercase tracking-widest text-gray-400">Total Orders</div>
                            <div className="mt-1 text-3xl font-black text-gray-900 italic">{orders.length}</div>
                        </div>
                        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div className="text-xs font-black uppercase tracking-widest text-gray-400">In Progress</div>
                            <div className="mt-1 text-3xl font-black text-yellow-600 italic">
                                {orders.filter((o) => ['knitting', 'dyeing', 'finishing', 'qc'].includes(o.status)).length}
                            </div>
                        </div>
                        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                            <div className="text-xs font-black uppercase tracking-widest text-gray-400">Completed</div>
                            <div className="mt-1 text-3xl font-black text-green-600 italic">
                                {orders.filter((o) => o.status === 'completed').length}
                            </div>
                        </div>
                        <div className="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm border-l-4 border-l-red-500">
                            <div className="text-xs font-black uppercase tracking-widest text-red-500">Overdue</div>
                            <div className="mt-1 text-3xl font-black text-red-600 italic">
                                {orders.filter((o) => o.is_overdue).length}
                            </div>
                        </div>
                    </div>

                    {/* 3. MODUL GRAFIK TREN 7 HARI */}
                    <div className="bg-white rounded-[2rem] p-8 mb-8 shadow-sm border border-slate-200">
                        <h3 className="text-sm font-black uppercase italic tracking-tighter text-slate-800 mb-6">
                            📈 Productivity Trends (Last 7 Days)
                        </h3>
                        <div className="flex items-end justify-between h-32 gap-3 px-2">
                            {weeklyTrends.length > 0 ? weeklyTrends.map((data, idx) => {
                                const maxVal = Math.max(...weeklyTrends.map(d => d.total)) || 1;
                                return (
                                    <div key={idx} className="flex-1 flex flex-col items-center gap-2 group">
                                        <div 
                                            className="w-full max-w-[30px] bg-slate-100 rounded-t-lg transition-all hover:bg-[#ED1C24] relative"
                                            style={{ height: `${(data.total / maxVal) * 100}%`, minHeight: '4px' }}
                                        >
                                            <div className="absolute -top-6 left-1/2 -translate-x-1/2 text-[9px] font-bold opacity-0 group-hover:opacity-100 transition-opacity">
                                                {data.total}
                                            </div>
                                        </div>
                                        <span className="text-[9px] font-bold text-slate-400 uppercase">{data.day}</span>
                                    </div>
                                );
                            }) : (
                                <div className="w-full text-center text-slate-300 italic text-xs">Menunggu data tren...</div>
                            )}
                        </div>
                    </div>

                    {/* 4. Table Monitoring */}
                    <div className="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-xl">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-[#ED1C24]">
                                    <tr>
                                        <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-white">Info Pesanan</th>
                                        <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-white text-center">Status</th>
                                        <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-white">Knitting Actuals</th>
                                        <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-white">Dyeing Actuals</th>
                                        <th className="px-6 py-4 text-left text-xs font-black uppercase tracking-widest text-white">Lead Time</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {orders.length === 0 ? (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-12 text-center text-sm font-bold text-gray-400 uppercase italic">
                                                Belum ada data monitoring produksi.
                                            </td>
                                        </tr>
                                    ) : (
                                        orders.map((order) => (
                                            <tr key={order.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="whitespace-nowrap px-6 py-4">
                                                    <div className="text-sm">
                                                        <div className="font-black text-gray-900 italic tracking-tight">
                                                            SAP: {order.sap_no}
                                                        </div>
                                                        <div className="text-gray-500 font-bold text-[10px] uppercase">ART: {order.art_no}</div>
                                                        <div className="text-red-600 font-black text-xs uppercase tracking-tighter mt-1">
                                                            {order.pelanggan}
                                                        </div>
                                                        <div className="mt-2 flex items-center gap-2">
                                                            <div
                                                                className="h-3 w-3 rounded-full border border-gray-200 shadow-sm"
                                                                style={{
                                                                    backgroundColor: order.warna?.toLowerCase().includes('navy') ? '#001f3f' : 
                                                                                     order.warna?.toLowerCase().includes('black') ? '#000000' : 
                                                                                     order.warna?.toLowerCase().includes('red') ? '#ff0000' : '#e5e7eb'
                                                                }}
                                                            />
                                                            <span className="text-[10px] font-black uppercase text-gray-400">
                                                                {order.warna}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="whitespace-nowrap px-6 py-4 text-center">
                                                    {getStatusBadge(order.status)}
                                                </td>

                                                <td className="px-6 py-4">
                                                    <div className="text-[11px] font-bold">
                                                        <div className="flex justify-between border-b border-gray-50 pb-1">
                                                            <span className="text-gray-400 uppercase tracking-tighter">Lebar:</span>
                                                            {highlightDeviation(order.knitting_width, order.width_deviation, order.target_lebar)}
                                                        </div>
                                                        <div className="flex justify-between pt-1">
                                                            <span className="text-gray-400 uppercase tracking-tighter">GSM:</span>
                                                            {highlightDeviation(order.knitting_gsm, order.gsm_deviation, order.target_gramasi)}
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="px-6 py-4">
                                                    <div className="text-[11px] font-bold">
                                                        <div className="flex justify-between border-b border-gray-50 pb-1">
                                                            <span className="text-gray-400 uppercase tracking-tighter">Warna:</span>
                                                            <span className="text-gray-900">{order.dyeing_color_code || '-'}</span>
                                                        </div>
                                                        <div className="flex justify-between pt-1">
                                                            <span className="text-gray-400 uppercase tracking-tighter">Suhu:</span>
                                                            <span className="text-gray-900">{order.dyeing_temp ? `${order.dyeing_temp}°C` : '-'}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td className="whitespace-nowrap px-6 py-4">
                                                    {formatLeadTime(order.lead_time_days, order.is_overdue)}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Legend */}
                    <div className="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-6">
                        <div className="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 mb-4 italic">Keterangan Monitoring</div>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 text-[10px] font-black uppercase">
                            <div className="flex items-center gap-3">
                                <span className="h-3 w-3 rounded-full bg-yellow-100 border border-yellow-300 shadow-sm"></span>
                                <span className="tracking-tight">Proses Berjalan (Yellow)</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <span className="h-3 w-3 rounded-full bg-green-100 border border-green-300 shadow-sm"></span>
                                <span className="tracking-tight">Selesai (Green)</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <span className="text-red-600 italic">Red Text</span>
                                <span className="tracking-tight">Deviasi &gt; 5%</span>
                            </div>
                            <div className="flex items-center gap-3">
                                <span className="bg-red-100 text-red-800 px-2 py-0.5 rounded-full shadow-sm">Red Badge</span>
                                <span className="tracking-tight">Lead Time &gt; 3 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
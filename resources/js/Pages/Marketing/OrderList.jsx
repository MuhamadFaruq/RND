import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react'; // Penambahan useState & useEffect
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal';

export default function OrderList({ auth, orders, flash }) {
    // State untuk Modal Hapus
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [selectedOrder, setSelectedOrder] = useState(null);
    const [showToast, setShowToast] = useState(false);

    // Logika menampilkan notifikasi flash saat ada data masuk
    useEffect(() => {
        if (flash?.success) {
            setShowToast(true);
            const timer = setTimeout(() => setShowToast(false), 5000);
            return () => clearTimeout(timer);
        }
    }, [flash]);

    const getStatusColor = (status) => {
        const colors = {
            pending: 'bg-gray-100 text-gray-800',
            knitting: 'bg-yellow-100 text-yellow-800',
            dyeing: 'bg-blue-100 text-blue-800',
            finishing: 'bg-purple-100 text-purple-800',
            qc: 'bg-indigo-100 text-indigo-800',
            completed: 'bg-green-100 text-green-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    // Fungsi Handler Hapus
    const openDeleteModal = (order) => {
        setSelectedOrder(order);
        setIsModalOpen(true);
    };

    const confirmDelete = () => {
        router.delete(route('marketing.orders.destroy', selectedOrder.id), {
            onSuccess: () => setIsModalOpen(false)
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-bold text-xl text-gray-800 leading-tight">Daftar Order & Monitoring Marketing</h2>}
        >
            <Head title="Order List" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    
                    {/* NOTIFIKASI VALIDASI EDIT/SIMPAN */}
                    {showToast && (
                        <div className="mb-4 flex items-center p-4 text-green-800 rounded-xl bg-green-50 border border-green-200 shadow-sm animate-fade-in-down">
                            <div className="ms-3 text-sm font-bold uppercase tracking-wide">
                                ✅ {flash.success}
                            </div>
                            <button onClick={() => setShowToast(false)} className="ms-auto font-bold opacity-50">✕</button>
                        </div>
                    )}

                    <div className="bg-white shadow-sm sm:rounded-lg p-6 border-t-4 border-blue-600">
                        <div className="flex justify-between items-center mb-6">
                            <h3 className="text-lg font-bold">Status Pesanan Aktif</h3>
                            <a 
                                href={route('marketing.orders.export')} 
                                className="bg-green-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-green-700 transition flex items-center gap-2"
                            >
                                <span className="text-lg">Excel</span>
                            </a>
                            <Link 
                                href={route('marketing.orders.create')} 
                                className="bg-blue-600 text-white px-4 py-2 rounded text-sm font-bold hover:bg-blue-700 transition"
                            >
                                + Input Order Baru
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">SAP / Artikel</th>
                                        <th className="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Pelanggan</th>
                                        <th className="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider">Warna</th>
                                        <th className="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Status Produksi</th>
                                        <th className="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider">Target (L/G)</th>
                                        <th className="px-4 py-3 text-center text-xs font-bold uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 uppercase text-xs">
                                    {orders && orders.length > 0 ? (
                                        orders.map((order) => (
                                            <tr key={order.id} className="hover:bg-gray-50 transition">
                                                <td className="px-4 py-4 whitespace-nowrap">
                                                    <div className="font-bold text-blue-600">{order.sap_no}</div>
                                                    <div className="text-gray-500">{order.art_no}</div>
                                                </td>
                                                <td className="px-4 py-4">{order.pelanggan}</td>
                                                <td className="px-4 py-4">{order.warna}</td>
                                                <td className="px-4 py-4 text-center">
                                                    <span className={`px-3 py-1 rounded-full font-bold shadow-sm ${getStatusColor(order.status)}`}>
                                                        {order.status}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-4 text-right">
                                                    {order.target_lebar} cm / {order.target_gramasi} gsm
                                                </td>
                                                <td className="px-4 py-4 text-center font-bold">
                                                    <Link 
                                                        href={route('marketing.orders.edit', order.id)} 
                                                        className="text-indigo-600 hover:text-indigo-900 mr-4 inline-block bg-indigo-50 px-2 py-1 rounded"
                                                    >
                                                        EDIT
                                                    </Link>
                                                    <button 
                                                        onClick={() => openDeleteModal(order)} 
                                                        className="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded"
                                                    >
                                                        HAPUS
                                                    </button>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="6" className="px-4 py-8 text-center text-gray-500 font-medium italic">
                                                Belum ada data order ditemukan.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {/* MODAL KONFIRMASI HAPUS INDUSTRIAL STYLE */}
            <ConfirmDeleteModal 
                isOpen={isModalOpen} 
                onClose={() => setIsModalOpen(false)} 
                onConfirm={confirmDelete} 
                itemTitle={selectedOrder?.sap_no} 
            />
        </AuthenticatedLayout>
    );
}
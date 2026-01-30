import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function EditOrder({ auth, order }) {
    // Inisialisasi form dengan data order yang ada
    const { data, setData, put, processing, errors } = useForm({
        sap_no: order.sap_no || '',
        art_no: order.art_no || '',
        tanggal: order.tanggal || '',
        pelanggan: order.pelanggan || '',
        mkt: order.mkt || '',
        keperluan: order.keperluan || '',
        konstruksi_greige: order.konstruksi_greige || '',
        material: order.material || '',
        benang: order.benang || '',
        kelompok_kain: order.kelompok_kain || '',
        target_lebar: order.target_lebar || '',
        belah_bulat: order.belah_bulat || '',
        target_gramasi: order.target_gramasi || '',
        warna: order.warna || '',
        handfeel: order.handfeel || '',
        treatment_khusus: order.treatment_khusus || '',
        roll_target: order.roll_target || '',
        kg_target: order.kg_target || '',
        keterangan_artikel: order.keterangan_artikel || '',
    });

    const submit = (e) => {
        e.preventDefault();
        // Menggunakan metode PUT untuk update data
        put(route('marketing.orders.update', order.id));
    };

    return (
        <AuthenticatedLayout 
            user={auth.user} 
            header={<h2 className="font-bold text-xl text-gray-800 leading-tight">Edit Permintaan Marketing: SAP {order.sap_no}</h2>}
        >
            <Head title="Edit Order" />
            <div className="py-12 bg-gray-50">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        
                        <div className="mb-8">
                            <h3 className="text-red-600 font-bold mb-4 border-b pb-2">I. IDENTITAS ORDER (SAP Tidak Dapat Diubah)</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">SAP NO</label>
                                    <input type="text" value={data.sap_no} className="w-full rounded-lg border-gray-200 bg-gray-100 cursor-not-allowed" disabled />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">ART (INT)</label>
                                    <input type="text" value={data.art_no} onChange={e => setData('art_no', e.target.value)} className="w-full rounded-lg border-gray-300" required />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Tanggal</label>
                                    <input type="date" value={data.tanggal} onChange={e => setData('tanggal', e.target.value)} className="w-full rounded-lg border-gray-300" required />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Pelanggan</label>
                                    <input type="text" value={data.pelanggan} onChange={e => setData('pelanggan', e.target.value)} className="w-full rounded-lg border-gray-300" required />
                                </div>
                            </div>
                        </div>

                        <div className="mb-8">
                            <h3 className="text-red-600 font-bold mb-4 border-b pb-2">II. KLASIFIKASI & MATERIAL</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">MKT</label>
                                    <select value={data.mkt} onChange={e => setData('mkt', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="MKT-A">MKT-A</option>
                                        <option value="MKT-B">MKT-B</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Keperluan</label>
                                    <select value={data.keperluan} onChange={e => setData('keperluan', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="Sample">Sample</option>
                                        <option value="Produksi">Produksi</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Material</label>
                                    <select value={data.material} onChange={e => setData('material', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="Cotton">Cotton</option>
                                        <option value="Polyester">Polyester</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Benang</label>
                                    <input type="number" value={data.benang} onChange={e => setData('benang', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 mb-8">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Target Lebar</label>
                                    <input type="number" value={data.target_lebar} onChange={e => setData('target_lebar', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Target Gramasi</label>
                                    <input type="number" value={data.target_gramasi} onChange={e => setData('target_gramasi', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Warna</label>
                                    <input type="text" value={data.warna} onChange={e => setData('warna', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">KG Target</label>
                                    <input type="number" value={data.kg_target} onChange={e => setData('kg_target', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                        </div>

                        <div className="flex justify-between pt-6 border-t">
                            <button type="button" onClick={() => window.history.back()} className="text-gray-600 font-bold uppercase text-xs">Batal</button>
                            <button type="submit" disabled={processing} className="bg-blue-600 text-white px-12 py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                {processing ? 'MEMPROSES...' : 'UPDATE ORDER'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
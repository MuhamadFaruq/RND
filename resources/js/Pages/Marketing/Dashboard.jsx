import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

export default function MarketingDashboard({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        sap_no: '',
        art_no: '',
        pelanggan: '',
        warna: '',
        target_lebar: '',
        target_gramasi: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('marketing.orders.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-bold text-xl text-gray-800 leading-tight">Marketing Portal - Input Order Baru</h2>}
        >
            <Head title="Marketing Dashboard" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-blue-600 p-6">
                        <h3 className="text-lg font-bold mb-4 text-blue-700 underline">Form Input Marketing Order (MO)</h3>
                        
                        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <label className="block font-medium text-gray-700 uppercase">Nomor SAP</label>
                                <input type="number" value={data.sap_no} onChange={e => setData('sap_no', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                                {errors.sap_no && <div className="text-red-500 text-xs mt-1">{errors.sap_no}</div>}
                            </div>
                            <div>
                                <label className="block font-medium text-gray-700 uppercase">Artikel (Art No)</label>
                                <input type="text" value={data.art_no} onChange={e => setData('art_no', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div className="md:col-span-2">
                                <label className="block font-medium text-gray-700 uppercase">Nama Pelanggan</label>
                                <input type="text" value={data.pelanggan} onChange={e => setData('pelanggan', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label className="block font-medium text-gray-700 uppercase">Warna</label>
                                <input type="text" value={data.warna} onChange={e => setData('warna', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label className="block font-medium text-gray-700 uppercase">Target Lebar (cm)</label>
                                <input type="number" value={data.target_lebar} onChange={e => setData('target_lebar', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>
                            <div>
                                <label className="block font-medium text-gray-700 uppercase">Target Gramasi (GSM)</label>
                                <input type="number" value={data.target_gramasi} onChange={e => setData('target_gramasi', e.target.value)} className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required />
                            </div>

                            <div className="md:col-span-2 pt-4">
                                <button type="submit" disabled={processing} className="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition shadow-lg">
                                    {processing ? 'Menyimpan...' : 'TERBITKAN ORDER KE PRODUKSI'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
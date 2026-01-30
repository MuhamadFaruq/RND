import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import axios from 'axios';

export default function CreateOrder({ auth }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        sap_no: '', art_no: '', tanggal: '', pelanggan: '',
        mkt: '', keperluan: '', konstruksi_greige: '',
        material: '', benang: '', kelompok_kain: '',
        target_lebar: '', belah_bulat: '', target_gramasi: '',
        warna: '', handfeel: '', treatment_khusus: '',
        roll_target: '', kg_target: '', keterangan_artikel: ''
    });

    // Fungsi Validasi Real-time SAP
    const checkSap = async (value) => {
        if (value.length > 2) {
            try {
                const response = await axios.get(route('api.check-sap', value));
                if (response.data.exists) {
                    setSapError('Nomor SAP ini sudah terdaftar di sistem!');
                } else {
                    setSapError('');
                }
            } catch (err) {
                console.error("Gagal cek SAP", err);
            }
        }
    };

    const submit = (e) => {
        e.preventDefault();
        if (sapError) return alert('Perbaiki nomor SAP sebelum mengirim!');
        
        post(route('marketing.orders.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout user={auth.user} header={<h2 className="font-bold text-xl text-gray-800">Permintaan Marketing Baru</h2>}>
            <Head title="Input Order Baru" />
            <div className="py-12 bg-gray-50">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                        
                        {/* SECTION 1: IDENTITAS ORDER */}
                        <div className="mb-8">
                            <h3 className="text-red-600 font-bold mb-4 border-b pb-2">I. IDENTITAS ORDER</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">SAP NO (INT)</label>
                                    <input type="number" value={data.sap_no} onChange={e => setData('sap_no', e.target.value)} className="w-full rounded-lg border-gray-300 focus:ring-red-500" required />
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

                        {/* SECTION 2: MARKETING & MATERIAL (DD Section) */}
                        <div className="mb-8">
                            <h3 className="text-red-600 font-bold mb-4 border-b pb-2">II. KLASIFIKASI & MATERIAL</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">MKT (DD)</label>
                                    <select value={data.mkt} onChange={e => setData('mkt', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="">Pilih MKT</option>
                                        <option value="Sales 1">Sales 1</option>
                                        <option value="Sales 2">Sales 2</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Keperluan (DD)</label>
                                    <select value={data.keperluan} onChange={e => setData('keperluan', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="">Pilih Keperluan</option>
                                        <option value="Sample">Sample</option>
                                        <option value="Repeat Order">Repeat Order</option>
                                        <option value="New Order">New Order</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Material (DD)</label>
                                    <select value={data.material} onChange={e => setData('material', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="">Pilih Material</option>
                                        <option value="Cotton Combed">Cotton Combed</option>
                                        <option value="CVC">CVC</option>
                                        <option value="Polyester">Polyester</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Benang (INT)</label>
                                    <input type="number" value={data.benang} onChange={e => setData('benang', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                            </div>
                        </div>

                        {/* SECTION 3: SPESIFIKASI TEKNIS */}
                        <div className="mb-8">
                            <h3 className="text-red-600 font-bold mb-4 border-b pb-2">III. SPESIFIKASI TEKNIS KAIN</h3>
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Konstruksi Greige</label>
                                    <input type="text" value={data.konstruksi_greige} onChange={e => setData('konstruksi_greige', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Kelompok Kain (DD)</label>
                                    <select value={data.kelompok_kain} onChange={e => setData('kelompok_kain', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="">Pilih Kelompok</option>
                                        <option value="Single Knit">Single Knit</option>
                                        <option value="Double Knit">Double Knit</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Belah/Bulat (DD)</label>
                                    <select value={data.belah_bulat} onChange={e => setData('belah_bulat', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="">Pilih Tipe</option>
                                        <option value="Belah">Belah (Open Width)</option>
                                        <option value="Bulat">Bulat (Tubular)</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Handfeel (DD)</label>
                                    <select value={data.handfeel} onChange={e => setData('handfeel', e.target.value)} className="w-full rounded-lg border-gray-300">
                                        <option value="">Pilih Handfeel</option>
                                        <option value="Soft">Soft</option>
                                        <option value="Normal">Normal</option>
                                        <option value="Hard">Hard</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Target Lebar (INT)</label>
                                    <input type="number" value={data.target_lebar} onChange={e => setData('target_lebar', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Target Gramasi (INT)</label>
                                    <input type="number" value={data.target_gramasi} onChange={e => setData('target_gramasi', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Warna (Text)</label>
                                    <input type="text" value={data.warna} onChange={e => setData('warna', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Treatment Khusus</label>
                                    <input type="text" value={data.treatment_khusus} onChange={e => setData('treatment_khusus', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                </div>
                            </div>
                        </div>

                        {/* SECTION 4: TARGET QUANTITY & KETERANGAN */}
                        <div className="mb-8">
                            <h3 className="text-red-600 font-bold mb-4 border-b pb-2">IV. TARGET QUANTITY & KETERANGAN</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-bold text-gray-500 uppercase">Roll (INT)</label>
                                        <input type="number" value={data.roll_target} onChange={e => setData('roll_target', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold text-gray-500 uppercase">KG (INT)</label>
                                        <input type="number" value={data.kg_target} onChange={e => setData('kg_target', e.target.value)} className="w-full rounded-lg border-gray-300" />
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-xs font-bold text-gray-500 uppercase">Keterangan Artikel</label>
                                    <textarea value={data.keterangan_artikel} onChange={e => setData('keterangan_artikel', e.target.value)} className="w-full rounded-lg border-gray-300" rows="1"></textarea>
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end pt-6 border-t">
                            <button type="submit" disabled={processing} className="bg-[#ED1C24] text-white px-12 py-3 rounded-xl font-bold hover:bg-red-700 transition shadow-lg shadow-red-200">
                                {processing ? 'MENYIMPAN...' : 'PUBLISH ORDER'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
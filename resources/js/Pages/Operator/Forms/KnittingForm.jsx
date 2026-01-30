import React from 'react';

// Tambahkan fetchSapDetail dan isSearching ke dalam props
export default function KnittingForm({ formData = {}, onInputChange, onSubmit, fetchSapDetail, isSearching }) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            {/* SEKSI BARU: DATA ORDER (SAP) */}
            <div className="bg-slate-50 p-4 rounded-xl border-2 border-dashed border-slate-200">
                <h4 className="text-slate-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-slate-200 pb-2">DATA ORDER</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="relative">
                        <label className="text-[10px] font-bold text-gray-500 uppercase">No. SAP (Ketik & Klik Luar)</label>
                        <input 
                            type="number" 
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)} 
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Memicu pencarian otomatis
                            className={`w-full px-4 py-2 border rounded-lg text-sm font-black ${isSearching ? 'border-yellow-400 animate-pulse bg-yellow-50' : 'border-gray-200 focus:ring-[#ED1C24]'}`}
                            placeholder="Contoh: 1002345"
                            required 
                        />
                        {isSearching && <span className="absolute right-3 top-7 text-[9px] font-bold text-yellow-600">Mencari...</span>}
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Nama Pelanggan (Otomatis)</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly // Tidak bisa diedit manual
                            className="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 font-bold"
                            placeholder="Akan terisi otomatis..."
                        />
                    </div>
                </div>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Warna (Otomatis)</label>
                        <input type="text" value={formData.warna || ''} readOnly className="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 font-bold" />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Jenis Kain (Otomatis)</label>
                        <input type="text" value={formData.jenis_kain || ''} readOnly className="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-sm text-gray-500 font-bold" />
                    </div>
                </div>
            </div>

            {/* SEKSI I: MESIN (Lanjutkan dengan kode lama Anda) */}
            <div className="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <h4 className="text-blue-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-blue-200 pb-2">I. MESIN</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Tanggal Produksi</label>
                        <input type="date" value={formData.tanggal_mesin || ''} onChange={(e) => onInputChange('tanggal_mesin', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" required />
                    </div>
                    {/* ... Sisa input No. Mesin, Type Mesin, dll sama seperti kode Anda ... */}
                </div>
            </div>

            {/* SEKSI II: HASIL GREIGE */}
            <div className="bg-green-50/50 p-4 rounded-xl border border-green-100">
                <h4 className="text-green-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-green-200 pb-2">II. HASIL GREIGE</h4>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div><label className="text-[10px] font-bold text-gray-500 uppercase">Lebar</label>
                    <input type="number" step="0.1" value={formData.lebar || ''} onChange={(e) => onInputChange('lebar', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required /></div>
                    <div><label className="text-[10px] font-bold text-gray-500 uppercase">Gramasi</label>
                    <input type="number" value={formData.gramasi || ''} onChange={(e) => onInputChange('gramasi', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required /></div>
                    <div><label className="text-[10px] font-bold text-gray-500 uppercase">Berat (KG)</label>
                    <input type="number" value={formData.kg || ''} onChange={(e) => onInputChange('kg', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm font-black" required /></div>
                    <div><label className="text-[10px] font-bold text-gray-500 uppercase">Roll</label>
                    <input type="number" value={formData.roll || ''} onChange={(e) => onInputChange('roll', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm font-black" required /></div>
                </div>
            </div>

            {/* SEKSI III: PENGGUNAAN BENANG */}
            <div className="bg-yellow-50/50 p-4 rounded-xl border border-yellow-100">
                <h4 className="text-yellow-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-yellow-200 pb-2">III. PENGGUNAAN BENANG</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {[1, 2, 3, 4].map(n => (
                        <div key={n} className="flex gap-2 items-center bg-white p-2 rounded-lg border border-yellow-100">
                            <input type="text" placeholder={`Benang ${n} (%)`} value={formData[`benang_${n}`] || ''} onChange={(e) => onInputChange(`benang_${n}`, e.target.value)} className="flex-1 border-none text-xs font-bold focus:ring-0" />
                            <div className="w-px h-8 bg-gray-100"></div>
                            <input type="number" placeholder={`YL ${n}`} value={formData[`yl_${n}`] || ''} onChange={(e) => onInputChange(`yl_${n}`, e.target.value)} className="w-16 border-none text-xs font-bold focus:ring-0 text-center" />
                        </div>
                    ))}
                </div>
            </div>

            {/* TOTAL PRODUKSI */}
            <div className="bg-white p-4 rounded-xl border-2 border-red-100 shadow-inner">
                <label className="block text-sm font-black text-red-600 uppercase mb-2 tracking-tighter">Produksi / Day (KG)</label>
                <input type="number" value={formData.produksi_day || ''} onChange={(e) => onInputChange('produksi_day', e.target.value)} className="w-full px-4 py-4 bg-red-50 border-none rounded-xl text-2xl font-black text-red-600 focus:ring-2 focus:ring-red-500" required />
            </div>

            <div className="pt-6 border-t">
                <button type="submit" className="w-full py-5 bg-gradient-to-r from-[#ED1C24] to-red-700 text-white font-black rounded-2xl shadow-xl hover:shadow-red-200 transition-all uppercase tracking-widest text-sm active:scale-95">
                    🚀 Kirim Laporan Produksi
                </button>
            </div>
        </form>
    );
}
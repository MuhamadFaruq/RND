import React from 'react';

export default function RelaxDryerForm({ 
    formData = {}, // Definisikan default object agar tidak layar putih
    onInputChange, 
    onSubmit,
    fetchSapDetail, // Ambil fungsi pencarian dari props LogBookForm
    isSearching     // Indikator loading pencarian
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            
            {/* SEKSI BARU: IDENTITAS ORDER (SAP LOOKUP) */}
            <div className="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <h4 className="text-slate-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-slate-200 pb-2">O. IDENTITAS ORDER</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="relative">
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Nomor SAP</label>
                        <input 
                            type="number" 
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)}
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger cari saat pindah kolom
                            className={`w-full px-4 py-2 border rounded-lg text-sm font-black ${isSearching ? 'border-yellow-400 animate-pulse' : 'border-gray-200'}`} 
                            placeholder="Ketik SAP..."
                            required 
                        />
                        {isSearching && <span className="absolute right-2 top-8 text-[8px] text-yellow-600 font-bold">Mencari...</span>}
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pelanggan (Auto)</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly 
                            className="w-full px-4 py-2 bg-gray-100 border-none rounded-lg text-sm text-gray-500 font-bold" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Jenis Kain (Auto)</label>
                        <input 
                            type="text" 
                            value={formData.jenis_kain || ''} 
                            readOnly 
                            className="w-full px-4 py-2 bg-gray-100 border-none rounded-lg text-sm text-gray-500 font-bold" 
                        />
                    </div>
                </div>
            </div>
            {/* SEKSI: PARAMETER MESIN & PROSES */}
            <div className="bg-emerald-50/50 p-4 rounded-xl border border-emerald-100">
                <h4 className="text-emerald-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-emerald-200 pb-2">I. PARAMETER MESIN & PROSES</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Tanggal (DATE)</label>
                        <input 
                            type="date" 
                            value={formData.tanggal_relax || ''} 
                            onChange={(e) => onInputChange('tanggal_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Mesin (DD)</label>
                        <select 
                            value={formData.mesin_relax || ''} 
                            onChange={(e) => onInputChange('mesin_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required
                        >
                            <option value="">Pilih Mesin</option>
                            <option value="RD-01">RD-01</option>
                            <option value="RD-02">RD-02</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Handfeel (DD)</label>
                        <select 
                            value={formData.handfeel_relax || ''} 
                            onChange={(e) => onInputChange('handfeel_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                        >
                            <option value="">Pilih Jenis</option>
                            <option value="Soft">Soft</option>
                            <option value="Dry">Dry</option>
                            <option value="Normal">Normal</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Overfeed (INT)</label>
                        <input 
                            type="number" 
                            value={formData.overfeed || ''} 
                            onChange={(e) => onInputChange('overfeed', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Temperatur (°C)</label>
                        <input 
                            type="number" 
                            value={formData.temperatur_relax || ''} 
                            onChange={(e) => onInputChange('temperatur_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Speed (m/min)</label>
                        <input 
                            type="number" 
                            value={formData.speed_relax || ''} 
                            onChange={(e) => onInputChange('speed_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI: HASIL PRODUKSI & CHEMICAL */}
            <div className="bg-orange-50/50 p-4 rounded-xl border border-orange-100">
                <h4 className="text-orange-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-orange-200 pb-2">II. HASIL PRODUKSI & CHEMICAL</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Hasil Lebar (VARCHAR)</label>
                        <input 
                            type="text" 
                            value={formData.hasil_lebar_relax || ''} 
                            onChange={(e) => onInputChange('hasil_lebar_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Hasil Gramasi (VARCHAR)</label>
                        <input 
                            type="text" 
                            value={formData.hasil_gramasi_relax || ''} 
                            onChange={(e) => onInputChange('hasil_gramasi_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Shrinkage (V x H) (FLOAT)</label>
                        <input 
                            type="number" 
                            step="0.01"
                            placeholder="Contoh: 1.5"
                            value={formData.shrinkage || ''} 
                            onChange={(e) => onInputChange('shrinkage', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Chemical (TEXT)</label>
                        <textarea 
                            rows="2"
                            value={formData.chemical_relax || ''} 
                            onChange={(e) => onInputChange('chemical_relax', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                            placeholder="Input detail penggunaan chemical..."
                        />
                    </div>
                </div>
            </div>

            <div className="pt-6 border-t text-right">
                <button type="submit" className="w-full py-5 bg-gradient-to-r from-[#ED1C24] to-red-700 text-white font-black rounded-2xl shadow-xl hover:shadow-red-200 transition-all uppercase tracking-widest text-sm active:scale-95">
                    🚀 Kirim Laporan Relax Dryer
                </button>
            </div>
        </form>
    );
}
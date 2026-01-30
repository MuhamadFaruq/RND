import React from 'react';

// Menambahkan fetchSapDetail dan isSearching ke dalam props
export default function CompactorForm({ 
    formData = {}, // Default object agar tidak error undefined
    onInputChange, 
    onSubmit,
    fetchSapDetail, // Fungsi pencarian dari LogBookForm
    isSearching     // Status loading pencarian
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            {/* SEKSI 0: PENCARIAN ORDER (SAP) */}
            <div className="bg-red-50 p-4 rounded-xl border border-red-100">
                <h4 className="text-red-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-red-200 pb-2">DATA ORDER MARKETING</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="relative">
                        <label className="text-[10px] font-black text-gray-500 uppercase">Nomor SAP</label>
                        <input 
                            type="number" 
                            placeholder="Ketik SAP..."
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)} 
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger cari saat pindah kolom
                            className={`w-full px-4 py-2 border rounded-lg text-sm font-black ${isSearching ? 'border-yellow-500 animate-pulse' : 'border-gray-200'}`} 
                            required 
                        />
                        {isSearching && <span className="absolute right-2 top-7 text-[8px] text-yellow-600 font-bold">Mencari...</span>}
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase">Pelanggan (Auto)</label>
                        <input type="text" value={formData.pelanggan || ''} readOnly className="w-full px-4 py-2 bg-gray-100 border-none rounded-lg text-sm text-gray-500" />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase">Jenis Kain (Auto)</label>
                        <input type="text" value={formData.jenis_kain || ''} readOnly className="w-full px-4 py-2 bg-gray-100 border-none rounded-lg text-sm text-gray-500" />
                    </div>
                </div>
            </div>

            {/* SEKSI I: PARAMETER MESIN */}
            <div className="bg-teal-50/50 p-4 rounded-xl border border-teal-100">
                <h4 className="text-teal-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-teal-200 pb-2">I. PARAMETER MESIN</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Tanggal</label>
                        <input type="date" value={formData.tanggal_compactor || ''} onChange={(e) => onInputChange('tanggal_compactor', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">No. Mesin</label>
                        <select value={formData.no_mesin || ''} onChange={(e) => onInputChange('no_mesin', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required>
                            <option value="">Pilih Mesin</option>
                            <option value="CP-01">CP-01</option>
                            <option value="CP-02">CP-02</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Rangka</label>
                        <select value={formData.rangka || ''} onChange={(e) => onInputChange('rangka', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required>
                            <option value="">Pilih Rangka</option>
                            <option value="R-01">R-01</option>
                            <option value="R-02">R-02</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Temperature</label>
                        <input type="text" placeholder="Input suhu..." value={formData.temperature || ''} onChange={(e) => onInputChange('temperature', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Speed (m/min)</label>
                        <input type="number" placeholder="0" value={formData.speed || ''} onChange={(e) => onInputChange('speed', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Overfeed (%)</label>
                        <input type="number" placeholder="0" value={formData.overfeed || ''} onChange={(e) => onInputChange('overfeed', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" />
                    </div>
                </div>
            </div>

            {/* SEKSI II: SETTING & OUTPUT */}
            <div className="bg-cyan-50/50 p-4 rounded-xl border border-cyan-100">
                <h4 className="text-cyan-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-cyan-200 pb-2">II. SETTING & HASIL OUTPUT</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="grid grid-cols-2 gap-2">
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Felt</label>
                            <select value={formData.felt || ''} onChange={(e) => onInputChange('felt', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm">
                                <option value="">Pilih</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                            </select>
                        </div>
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Delivery Speed</label>
                            <input type="number" value={formData.delivery_speed || ''} onChange={(e) => onInputChange('delivery_speed', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Folding Speed</label>
                            <input type="number" value={formData.folding_speed || ''} onChange={(e) => onInputChange('folding_speed', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" />
                        </div>
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Shrinkage (V x H)</label>
                            <input type="number" value={formData.shrinkage || ''} onChange={(e) => onInputChange('shrinkage', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" />
                        </div>
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Hasil Lebar</label>
                        <input type="number" value={formData.hasil_lebar || ''} onChange={(e) => onInputChange('hasil_lebar', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Hasil Gramasi</label>
                        <input type="number" value={formData.hasil_gramasi || ''} onChange={(e) => onInputChange('hasil_gramasi', e.target.value)} className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" required />
                    </div>
                </div>
            </div>

            <div className="pt-6 border-t">
                <button type="submit" className="w-full py-5 bg-gradient-to-r from-[#ED1C24] to-red-700 text-white font-black rounded-2xl shadow-xl hover:shadow-red-200 transition-all uppercase tracking-widest text-sm active:scale-95">
                    🚀 Submit Laporan Compactor
                </button>
            </div>
        </form>
    );
}
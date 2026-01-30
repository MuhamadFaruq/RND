import React from 'react';

export default function HeatSettingForm({ 
    formData = {}, // Penyelamat agar tidak layar putih
    onInputChange, 
    onSubmit,
    fetchSapDetail, // Fungsi pencarian dari LogBookForm
    isSearching     // Status loading animasi
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            
            {/* SEKSI 0: IDENTIFIKASI ORDER (SAP SEARCH) */}
            <div className="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <h4 className="text-slate-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-slate-200 pb-2">Identifikasi Order Marketing</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="relative">
                        <label className="text-[10px] font-black text-gray-500 uppercase italic">Nomor SAP</label>
                        <input 
                            type="number" 
                            placeholder="Ketik 7 Digit SAP..."
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)}
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger cari saat pindah kolom
                            className={`w-full px-4 py-2 border-2 rounded-lg text-sm font-black transition-all ${isSearching ? 'border-yellow-400 animate-pulse' : 'border-gray-200 focus:border-red-500'}`} 
                            required 
                        />
                        {isSearching && <span className="absolute right-2 top-8 text-[8px] font-bold text-yellow-600">Mencari...</span>}
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase italic">Nama Pelanggan (Otomatis)</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly 
                            className="w-full px-4 py-2 bg-slate-100 border-none rounded-lg text-sm text-slate-500 font-bold italic" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase italic">Jenis Kain (Otomatis)</label>
                        <input 
                            type="text" 
                            value={formData.jenis_kain || ''} 
                            readOnly 
                            className="w-full px-4 py-2 bg-slate-100 border-none rounded-lg text-sm text-slate-500 font-bold italic" 
                        />
                    </div>
                </div>
            </div>
            {/* SEKSI I: PARAMETER MESIN */}
            <div className="bg-orange-50/50 p-4 rounded-xl border border-orange-100">
                <h4 className="text-orange-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-orange-200 pb-2">I. PARAMETER MESIN HEAT SETTING</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Tanggal</label>
                        <input 
                            type="date" 
                            value={formData.tanggal_hs || ''} 
                            onChange={(e) => onInputChange('tanggal_hs', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">No. Mesin (DD)</label>
                        <select 
                            value={formData.no_mesin_hs || ''} 
                            onChange={(e) => onInputChange('no_mesin_hs', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm"
                            required
                        >
                            <option value="">Pilih Mesin</option>
                            <option value="HS-01">HS-01</option>
                            <option value="HS-02">HS-02</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Rangka (DD)</label>
                        <select 
                            value={formData.rangka_hs || ''} 
                            onChange={(e) => onInputChange('rangka_hs', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm"
                            required
                        >
                            <option value="">Pilih Rangka</option>
                            <option value="R-PE-01">R-PE-01</option>
                            <option value="R-PE-02">R-PE-02</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Temperatur (°C)</label>
                        <input 
                            type="number" 
                            placeholder="0" 
                            value={formData.temperature_hs || ''} 
                            onChange={(e) => onInputChange('temperature_hs', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Speed (m/min)</label>
                        <input 
                            type="number" 
                            placeholder="0" 
                            value={formData.speed_hs || ''} 
                            onChange={(e) => onInputChange('speed_hs', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-500 uppercase">Overfeed (%)</label>
                        <input 
                            type="number" 
                            placeholder="0" 
                            value={formData.overfeed_hs || ''} 
                            onChange={(e) => onInputChange('overfeed_hs', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI II: SPEED CONTROL & HASIL */}
            <div className="bg-amber-50/50 p-4 rounded-xl border border-amber-100">
                <h4 className="text-amber-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-amber-200 pb-2">II. SPEED CONTROL & HASIL AKHIR</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div className="grid grid-cols-2 gap-2">
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Delivery Speed</label>
                            <input 
                                type="number" 
                                value={formData.delivery_speed || ''} 
                                onChange={(e) => onInputChange('delivery_speed', e.target.value)} 
                                className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                            />
                        </div>
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Folding Speed</label>
                            <input 
                                type="number" 
                                value={formData.folding_speed || ''} 
                                onChange={(e) => onInputChange('folding_speed', e.target.value)} 
                                className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm" 
                            />
                        </div>
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Hasil Lebar</label>
                            <input 
                                type="number" 
                                value={formData.hasil_lebar_hs || ''} 
                                onChange={(e) => onInputChange('hasil_lebar_hs', e.target.value)} 
                                className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm font-bold" 
                                required
                            />
                        </div>
                        <div>
                            <label className="text-[10px] font-black text-gray-500 uppercase">Hasil Gramasi</label>
                            <input 
                                type="number" 
                                value={formData.hasil_gramasi_hs || ''} 
                                onChange={(e) => onInputChange('hasil_gramasi_hs', e.target.value)} 
                                className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm font-bold" 
                                required
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div className="pt-6 border-t">
                <button type="submit" className="w-full py-5 bg-gradient-to-r from-[#ED1C24] to-red-700 text-white font-black rounded-2xl shadow-xl hover:shadow-red-200 transition-all uppercase tracking-widest text-sm active:scale-95">
                    🚀 Submit Laporan Heat Setting
                </button>
            </div>
        </form>
    );
}
import React from 'react';

// TAMBAHKAN 'processing' ke dalam daftar props agar tidak error di baris bawah
export default function DyeingForm({ 
    formData = {}, 
    onInputChange, 
    onSubmit,
    fetchSapDetail, 
    isSearching,
    processing // <--- Tambahkan ini
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            
            {/* --- SEKSI BARU: VALIDASI ORDER (SAP) --- */}
            <div className="bg-amber-50 p-4 rounded-xl border-2 border-amber-200">
                <h4 className="text-amber-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-amber-200 pb-2 flex items-center gap-2">
                    🔍 Validasi Order Marketing
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="relative">
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Nomor SAP (INT)</label>
                        <input 
                            type="number" 
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)} 
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger pencarian saat pindah kolom
                            className={`w-full px-4 py-2 border rounded-lg text-sm font-black focus:ring-amber-500 ${isSearching ? 'animate-pulse border-amber-500' : 'border-gray-200'}`} 
                            placeholder="Ketik No. SAP..."
                            required 
                        />
                        {isSearching && <span className="absolute right-2 top-8 text-[8px] font-bold text-amber-600 animate-bounce">Mengecek...</span>}
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-400 uppercase">Pelanggan (Auto)</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly 
                            className="w-full px-4 py-2 bg-white/50 border border-gray-100 rounded-lg text-sm text-gray-400 font-bold" 
                            placeholder="Terisi otomatis..."
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-400 uppercase">Target Warna Marketing (Auto)</label>
                        <input 
                            type="text" 
                            value={formData.warna || ''} 
                            readOnly 
                            className="w-full px-4 py-2 bg-white/50 border border-gray-100 rounded-lg text-sm text-red-500 font-black uppercase" 
                            placeholder="Terisi otomatis..."
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI: CEK GREIGE */}
            <div className="bg-purple-50/50 p-4 rounded-xl border border-purple-100">
                <h4 className="text-purple-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-purple-200 pb-2">I. CEK GREIGE</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Lebbar/Gramasi (INT)</label>
                        <input 
                            type="number" 
                            value={formData.lebar_gramasi || ''} 
                            onChange={(e) => onInputChange('lebar_gramasi', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Tanggal (DATE)</label>
                        <input 
                            type="date" 
                            value={formData.tanggal_dyeing || ''} 
                            onChange={(e) => onInputChange('tanggal_dyeing', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required 
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI: MESIN */}
            <div className="bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                <h4 className="text-blue-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-blue-200 pb-2">II. INFORMASI MESIN</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Jenis Mesin (DD)</label>
                        <select 
                            value={formData.jenis_mesin || ''} 
                            onChange={(e) => onInputChange('jenis_mesin', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required
                        >
                            <option value="">Pilih Jenis</option>
                            <option value="Overflow">Overflow</option>
                            <option value="Jet Dyeing">Jet Dyeing</option>
                            <option value="Winches">Winches</option>
                        </select>
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">No. Mesin (INT)</label>
                        <input 
                            type="number" 
                            value={formData.no_mesin_dyeing || ''} 
                            onChange={(e) => onInputChange('no_mesin_dyeing', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            required 
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI: WARNA & CHEMICAL */}
            <div className="bg-red-50/50 p-4 rounded-xl border border-red-100">
                <h4 className="text-red-700 font-black text-xs mb-4 uppercase italic tracking-widest border-b border-red-200 pb-2">III. PROSES DYEING & CHEMICAL</h4>
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Warna Aktual (TEXT)</label>
                        <input 
                            type="text" 
                            value={formData.warna_aktual || ''} 
                            onChange={(e) => onInputChange('warna_aktual', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm font-black uppercase focus:ring-[#ED1C24]" 
                            placeholder="Input warna hasil celup..."
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Kode Warna (VARCHAR)</label>
                        <input 
                            type="text" 
                            value={formData.kode_warna || ''} 
                            onChange={(e) => onInputChange('kode_warna', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Dye System (VARCHAR)</label>
                        <input 
                            type="text" 
                            placeholder="Contoh: Exhaust / Pad-Batch"
                            value={formData.dye_system || ''} 
                            onChange={(e) => onInputChange('dye_system', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-bold text-gray-500 uppercase">Treatment (Chemical) (TEXT)</label>
                        <textarea 
                            rows="2"
                            value={formData.treatment_chemical || ''} 
                            onChange={(e) => onInputChange('treatment_chemical', e.target.value)} 
                            className="w-full px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-[#ED1C24]" 
                            placeholder="Input detail chemical..."
                        />
                    </div>
                </div>
            </div>

            <div className="pt-6 border-t">
                <button 
                    type="submit" 
                    disabled={processing}
                    className={`w-full py-5 bg-gradient-to-r from-[#ED1C24] to-red-700 text-white font-black rounded-2xl shadow-xl transition-all uppercase tracking-widest text-sm ${processing ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-red-200 active:scale-95'}`}
                >
                    {processing ? '⌛ Menyimpan Data...' : '🚀 Submit Hasil Dyeing'}
                </button>
            </div>
        </form>
    );
}
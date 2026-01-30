import React from 'react';

export default function TumblerForm({ 
    formData = {}, // Defensif terhadap undefined
    onInputChange, 
    onSubmit,
    fetchSapDetail, // Diambil dari props LogBookForm
    isSearching     // Status loading pencarian
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            
            {/* SEKSI 0: IDENTITAS ORDER (PENYAMBUNG SAP) */}
            <div className="bg-white p-6 rounded-3xl border-2 border-red-500 shadow-sm mb-6">
                <h4 className="text-red-600 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-red-100 pb-2">
                    IDENTITAS ORDER (SAP AUTO-FILL)
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="relative">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Nomor SAP *</label>
                        <input 
                            type="number" 
                            placeholder="Ketik 7 digit SAP..."
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)}
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger cari data
                            className={`w-full px-4 py-3 border-2 rounded-xl font-black transition-all ${isSearching ? 'border-yellow-400 animate-pulse' : 'border-red-100 focus:border-red-500'}`} 
                            required 
                        />
                        {isSearching && <span className="absolute right-3 top-10 text-[8px] font-black text-yellow-600 animate-bounce">MENCARI...</span>}
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Pelanggan</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly // Data otomatis tidak boleh diubah manual
                            placeholder="Otomatis..."
                            className="w-full px-4 py-3 bg-slate-50 border-none rounded-xl font-bold text-slate-500" 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Jenis Kain</label>
                        <input 
                            type="text" 
                            value={formData.jenis_kain || ''} 
                            readOnly // Data otomatis tidak boleh diubah manual
                            placeholder="Otomatis..."
                            className="w-full px-4 py-3 bg-slate-50 border-none rounded-xl font-bold text-slate-500" 
                        />
                    </div>
                </div>
            </div>
            {/* SEKSI I: PARAMETER PROSES TUMBLER */}
            <div className="bg-indigo-50/50 p-6 rounded-3xl border border-indigo-100 shadow-sm">
                <h4 className="text-indigo-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-indigo-200 pb-2">
                    I. PARAMETER PROSES TUMBLER DRY
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div className="col-span-1 md:col-span-2 lg:col-span-1">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Tanggal (DATE) *</label>
                        <input 
                            type="date" 
                            value={formData.tanggal_tumbler || ''} 
                            onChange={(e) => onInputChange('tanggal_tumbler', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-bold" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Temperature (°C) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.temperature || ''} 
                            onChange={(e) => onInputChange('temperature', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-bold" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Steam Inject *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.steam_inject || ''} 
                            onChange={(e) => onInputChange('steam_inject', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-bold" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Hotwind *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.hotwind || ''} 
                            onChange={(e) => onInputChange('hotwind', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-bold" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Coldwind *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.coldwind || ''} 
                            onChange={(e) => onInputChange('coldwind', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 font-bold" 
                            required 
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI II: HASIL AKHIR & PENYUSUTAN */}
            <div className="bg-sky-50/50 p-6 rounded-3xl border border-sky-100 shadow-sm">
                <h4 className="text-sky-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-sky-200 pb-2">
                    II. HASIL AKHIR & SHRINKAGE
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Hasil Lebar (INT) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.hasil_lebar || ''} 
                            onChange={(e) => onInputChange('hasil_lebar', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-bold text-sky-700" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Hasil Gramasi (INT) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.hasil_gramasi || ''} 
                            onChange={(e) => onInputChange('hasil_gramasi', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-bold text-sky-700" 
                            required 
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block">Shrinkage (V x H) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.shrinkage || ''} 
                            onChange={(e) => onInputChange('shrinkage', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-bold text-red-600" 
                            required 
                        />
                    </div>
                </div>
            </div>

            {/* BUTTON SUBMIT */}
            <div className="pt-6 border-t border-gray-100">
                <button 
                    type="submit" 
                    className="w-full py-5 bg-gradient-to-r from-indigo-600 to-sky-600 text-white font-black rounded-2xl shadow-xl shadow-indigo-100 hover:shadow-indigo-200 transition-all uppercase tracking-widest text-sm active:scale-[0.98] border-b-4 border-indigo-800"
                >
                    🚀 Kirim Laporan Tumbler Dry
                </button>
            </div>
        </form>
    );
}
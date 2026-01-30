import React from 'react';

// Tambahkan fetchSapDetail dan isSearching ke dalam props
export default function QEForm({ formData = {}, onInputChange, onSubmit, fetchSapDetail, isSearching }) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            
            {/* SEKSI: PENCARIAN SAP (INTEGRASI BARU) */}
            <div className="bg-slate-50 p-6 rounded-3xl border border-slate-200 shadow-sm">
                <label className="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest italic">Pencarian Database Marketing (SAP)</label>
                <div className="relative">
                    <input 
                        type="number" 
                        placeholder="Masukkan 7 Digit Nomor SAP..."
                        value={formData.sap_no || ''} 
                        onChange={(e) => onInputChange('sap_no', e.target.value)} 
                        onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger pencarian otomatis
                        className={`w-full px-4 py-4 border-2 rounded-2xl font-black text-xl transition-all ${
                            isSearching ? 'border-yellow-400 animate-pulse bg-yellow-50' : 'border-emerald-200 focus:border-emerald-500'
                        }`} 
                    />
                    {isSearching && (
                        <span className="absolute right-4 top-4 text-[10px] font-black text-yellow-600 animate-bounce">MENCARI DATA...</span>
                    )}
                </div>
                
                {/* Info Otomatis dari SAP */}
                <div className="grid grid-cols-2 gap-4 mt-4">
                    <div className="bg-white p-3 rounded-xl border border-slate-100">
                        <p className="text-[9px] font-bold text-slate-400 uppercase">Pelanggan</p>
                        <p className="font-black text-slate-700 uppercase italic text-xs">{formData.pelanggan || '-'}</p>
                    </div>
                    <div className="bg-white p-3 rounded-xl border border-slate-100">
                        <p className="text-[9px] font-bold text-slate-400 uppercase">Jenis Kain</p>
                        <p className="font-black text-slate-700 uppercase italic text-xs">{formData.jenis_kain || '-'}</p>
                    </div>
                </div>
            </div>

            {/* SEKSI: FINAL QUALITY EVALUATION */}
            <div className="bg-emerald-50/50 p-6 rounded-3xl border border-emerald-100 shadow-sm">
                <h4 className="text-emerald-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-emerald-200 pb-2 flex justify-between">
                    <span>QE (FINAL QUALITY EVALUATION)</span>
                    <span className="text-[9px] bg-emerald-200 px-2 py-0.5 rounded text-emerald-800 not-italic">FINAL GATE</span>
                </h4>
                
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div className="md:col-span-2">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Fabric Name (TEXT) *</label>
                        <input 
                            type="text" 
                            placeholder="Masukkan nama kain final..."
                            value={formData.fabric_name_qe || ''} 
                            onChange={(e) => onInputChange('fabric_name_qe', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold uppercase" 
                            required 
                        />
                    </div>
                    
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Lebar Final (INT) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.lebar_qe || ''} 
                            onChange={(e) => onInputChange('lebar_qe', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Gramasi Final (INT) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.gramasi_qe || ''} 
                            onChange={(e) => onInputChange('gramasi_qe', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Shrinkage Final (INT) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.shrinkage_qe || ''} 
                            onChange={(e) => onInputChange('shrinkage_qe', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold text-red-600" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Note / Keterangan (VARCHAR) *</label>
                        <input 
                            type="text" 
                            placeholder="Catatan hasil akhir..."
                            value={formData.note_qe || ''} 
                            onChange={(e) => onInputChange('note_qe', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 font-bold" 
                            required 
                        />
                    </div>
                </div>
            </div>

            {/* BUTTON SUBMIT */}
            <div className="pt-6 border-t border-gray-100">
                <button 
                    type="submit" 
                    disabled={isSearching} // Disable jika sedang mencari data
                    className={`w-full py-5 text-white font-black rounded-2xl shadow-xl transition-all uppercase tracking-widest text-sm active:scale-[0.98] border-b-4 ${
                        isSearching 
                        ? 'bg-slate-400 border-slate-600 cursor-not-allowed' 
                        : 'bg-gradient-to-r from-emerald-600 to-green-600 border-emerald-800 hover:shadow-emerald-200'
                    }`}
                >
                    {isSearching ? '⏳ MEMPROSES DATA SAP...' : '🚀 Selesaikan Order & Kirim Laporan QE'}
                </button>
            </div>
        </form>
    );
}
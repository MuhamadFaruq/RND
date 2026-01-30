import React from 'react';

// Tambahkan fetchSapDetail dan isSearching ke dalam props
export default function PengujianForm({ 
    formData = {}, // Gunakan default parameter agar tidak crash
    onInputChange, 
    onSubmit,
    fetchSapDetail, 
    isSearching 
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            
            {/* SEKSI 0: IDENTITAS ORDER (PENCARIAN SAP) */}
            <div className="bg-blue-50/50 p-6 rounded-3xl border border-blue-100 shadow-sm mb-4">
                <h4 className="text-blue-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-blue-200 pb-2">
                    IDENTITAS ORDER (SAP LOOKUP)
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="relative">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Nomor SAP *</label>
                        <input 
                            type="number"
                            placeholder="Ketik SAP..."
                            value={formData.sap_no || ''}
                            onChange={(e) => onInputChange('sap_no', e.target.value)}
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger pencarian saat kursor pindah
                            className={`w-full px-4 py-3 border rounded-xl font-bold transition-all ${
                                isSearching ? 'border-blue-400 animate-pulse' : 'border-gray-200'
                            }`}
                            required
                        />
                        {isSearching && (
                            <span className="absolute right-3 top-10 text-[9px] font-bold text-blue-600">Mencari...</span>
                        )}
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Pelanggan (AUTO)</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly 
                            className="w-full px-4 py-3 bg-white/50 border border-gray-100 rounded-xl text-gray-500 font-bold italic"
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Jenis Kain (AUTO)</label>
                        <input 
                            type="text" 
                            value={formData.jenis_kain || ''} 
                            readOnly 
                            className="w-full px-4 py-3 bg-white/50 border border-gray-100 rounded-xl text-gray-500 font-bold italic"
                        />
                    </div>
                </div>
            </div>

            {/* SEKSI: HASIL PENGUJIAN QC & LAB */}
            <div className="bg-amber-50/50 p-6 rounded-3xl border border-amber-100 shadow-sm">
                <h4 className="text-amber-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-amber-200 pb-2 flex justify-between">
                    <span>HASIL PENGUJIAN FISIK</span>
                    <span className="text-[9px] bg-amber-200 px-2 py-0.5 rounded text-amber-800 not-italic uppercase font-black">Lab Test</span>
                </h4>
                
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {/* Input Tanggal & Parameter lainnya tetap sama seperti kode Anda sebelumnya */}
                    <div className="col-span-1 md:col-span-2 lg:col-span-1">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Tanggal Pengujian *</label>
                        <input 
                            type="date" 
                            value={formData.tanggal_uji || ''} 
                            onChange={(e) => onInputChange('tanggal_uji', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 font-bold" 
                            required 
                        />
                    </div>
                    
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Lebar (Inch) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.lebar_uji || ''} 
                            onChange={(e) => onInputChange('lebar_uji', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Gramasi (GSM) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.gramasi_uji || ''} 
                            onChange={(e) => onInputChange('gramasi_uji', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Shrinkage (%) *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.shrinkage_uji || ''} 
                            onChange={(e) => onInputChange('shrinkage_uji', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Spirality *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.spirality_uji || ''} 
                            onChange={(e) => onInputChange('spirality_uji', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold" 
                            required 
                        />
                    </div>

                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-2 block tracking-widest">Skewness *</label>
                        <input 
                            type="number" 
                            placeholder="0"
                            value={formData.skewness_uji || ''} 
                            onChange={(e) => onInputChange('skewness_uji', e.target.value)} 
                            className="w-full px-4 py-3 border border-gray-200 rounded-xl font-bold" 
                            required 
                        />
                    </div>
                </div>
            </div>

            {/* BUTTON SUBMIT */}
            <div className="pt-6 border-t border-gray-100">
                <button 
                    type="submit" 
                    className="w-full py-5 bg-gradient-to-r from-amber-600 to-yellow-500 text-white font-black rounded-2xl shadow-xl shadow-amber-100 hover:shadow-amber-200 transition-all uppercase tracking-widest text-sm active:scale-[0.98] border-b-4 border-amber-800"
                >
                    🚀 Submit Hasil Pengujian Lab
                </button>
            </div>
        </form>
    );
}
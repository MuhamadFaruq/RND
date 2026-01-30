import React from 'react';

// Menambahkan fetchSapDetail dan isSearching ke dalam props
// Menambahkan default parameter {} pada formData agar tidak crash
export default function FleeceForm({ 
    formData = {}, 
    onInputChange, 
    onSubmit, 
    fetchSapDetail, 
    isSearching 
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-8">
            
            {/* SEKSI 0: IDENTIFIKASI ORDER (PENCARIAN SAP) */}
            <div className="bg-slate-50 p-6 rounded-3xl border-2 border-dashed border-slate-200 shadow-inner">
                <h4 className="text-slate-700 font-black text-xs mb-4 uppercase italic tracking-widest">
                    🔍 Identifikasi Order (SAP)
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="relative">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Nomor SAP</label>
                        <input 
                            type="number" 
                            placeholder="Ketik 7 digit SAP..."
                            value={formData.sap_no || ''} 
                            onChange={(e) => onInputChange('sap_no', e.target.value)}
                            onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger pencarian
                            className={`w-full px-4 py-3 border-2 rounded-xl text-sm font-black transition-all ${
                                isSearching ? 'border-yellow-400 animate-pulse' : 'border-slate-200'
                            }`} 
                        />
                        {isSearching && (
                            <span className="absolute right-3 top-9 text-[9px] font-bold text-yellow-600 animate-bounce">Mencari...</span>
                        )}
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Pelanggan</label>
                        <input 
                            type="text" 
                            value={formData.pelanggan || ''} 
                            readOnly 
                            placeholder="Terisi otomatis..."
                            className="w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm font-bold text-slate-500"
                        />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Jenis Kain</label>
                        <input 
                            type="text" 
                            value={formData.jenis_kain || ''} 
                            readOnly 
                            placeholder="Terisi otomatis..."
                            className="w-full px-4 py-3 bg-white/50 border border-slate-200 rounded-xl text-sm font-bold text-slate-500"
                        />
                    </div>
                </div>
            </div>
            
            {/* SEKSI I: RAISING (GARUK) */}
            <div className="bg-rose-50/50 p-6 rounded-3xl border border-rose-100 shadow-sm">
                <h4 className="text-rose-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-rose-200 pb-2 flex justify-between">
                    <span>I. PROSES RAISING</span>
                    <span className="text-[9px] bg-rose-200 px-2 py-0.5 rounded text-rose-800 not-italic">MECHANICAL PROCESS</span>
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Tanggal Raising</label>
                        <input type="date" value={formData.raising_tanggal || ''} onChange={(e) => onInputChange('raising_tanggal', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Standar Bulu (INT)</label>
                        <input type="number" placeholder="0" value={formData.raising_std_bulu || ''} onChange={(e) => onInputChange('raising_std_bulu', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Speed (INT)</label>
                        <input type="number" placeholder="0" value={formData.raising_speed || ''} onChange={(e) => onInputChange('raising_speed', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Cloth Out (FLOAT)</label>
                        <input type="number" step="0.01" placeholder="0.00" value={formData.raising_cloth_out || ''} onChange={(e) => onInputChange('raising_cloth_out', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold text-rose-600" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Bend Pin (INT)</label>
                        <input type="number" placeholder="0" value={formData.raising_bend_pin || ''} onChange={(e) => onInputChange('raising_bend_pin', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Stright Pin (INT)</label>
                        <input type="number" placeholder="0" value={formData.raising_stright_pin || ''} onChange={(e) => onInputChange('raising_stright_pin', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">RPM Drum (INT)</label>
                        <input type="number" placeholder="0" value={formData.raising_rpm_drum || ''} onChange={(e) => onInputChange('raising_rpm_drum', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Lebar/GSM (INT)</label>
                        <input type="number" placeholder="0" value={formData.raising_lebar_gsm || ''} onChange={(e) => onInputChange('raising_lebar_gsm', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div className="md:col-span-3 lg:col-span-4">
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Drum Brush (VARCHAR)</label>
                        <input type="text" placeholder="Detail drum brush..." value={formData.raising_drum_brush || ''} onChange={(e) => onInputChange('raising_drum_brush', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                </div>
            </div>

            {/* SEKSI II: BRUSHING (SIKAT) */}
            <div className="bg-sky-50/50 p-6 rounded-3xl border border-sky-100 shadow-sm">
                <h4 className="text-sky-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-sky-200 pb-2">
                    II. PROSES BRUSHING
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Tanggal Brushing</label>
                        <input type="date" value={formData.brushing_tanggal || ''} onChange={(e) => onInputChange('brushing_tanggal', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Standar Bulu (INT)</label>
                        <input type="number" value={formData.brushing_std_bulu || ''} onChange={(e) => onInputChange('brushing_std_bulu', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Cloth Speed (FLOAT)</label>
                        <input type="number" step="0.1" value={formData.brushing_cloth_speed || ''} onChange={(e) => onInputChange('brushing_cloth_speed', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Cloth Out (FLOAT)</label>
                        <input type="number" step="0.1" value={formData.brushing_cloth_out || ''} onChange={(e) => onInputChange('brushing_cloth_out', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Left Brush (INT)</label>
                        <input type="number" value={formData.brushing_left_brush || ''} onChange={(e) => onInputChange('brushing_left_brush', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Right Brush (INT)</label>
                        <input type="number" value={formData.brushing_right_brush || ''} onChange={(e) => onInputChange('brushing_right_brush', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">RPM Drum (INT)</label>
                        <input type="number" value={formData.brushing_rpm_drum || ''} onChange={(e) => onInputChange('brushing_rpm_drum', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Tension 1/2/3 (INT)</label>
                        <input type="number" value={formData.brushing_tension || ''} onChange={(e) => onInputChange('brushing_tension', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Lebar/Gramasi (INT)</label>
                        <input type="number" value={formData.brushing_lebar_gramasi || ''} onChange={(e) => onInputChange('brushing_lebar_gramasi', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold text-sky-600" required />
                    </div>
                </div>
            </div>

            {/* SEKSI III: SHEARING (PANGKAS) */}
            <div className="bg-amber-50/50 p-6 rounded-3xl border border-amber-100 shadow-sm">
                <h4 className="text-amber-700 font-black text-xs mb-6 uppercase italic tracking-[0.2em] border-b border-amber-200 pb-2">
                    III. PROSES SHEARING
                </h4>
                <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Tanggal Shearing</label>
                        <input type="date" value={formData.shearing_tanggal || ''} onChange={(e) => onInputChange('shearing_tanggal', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Speed (INT)</label>
                        <input type="number" value={formData.shearing_speed || ''} onChange={(e) => onInputChange('shearing_speed', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Cloth Out (INT)</label>
                        <input type="number" value={formData.shearing_cloth_out || ''} onChange={(e) => onInputChange('shearing_cloth_out', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Expending (INT)</label>
                        <input type="number" value={formData.shearing_expending || ''} onChange={(e) => onInputChange('shearing_expending', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Shear (INT)</label>
                        <input type="number" value={formData.shearing_shear || ''} onChange={(e) => onInputChange('shearing_shear', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm" required />
                    </div>
                    <div>
                        <label className="text-[10px] font-black text-gray-400 uppercase mb-1 block">Lebar/Gramasi (INT)</label>
                        <input type="number" value={formData.shearing_lebar_gramasi || ''} onChange={(e) => onInputChange('shearing_lebar_gramasi', e.target.value)} className="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm font-bold text-amber-600" required />
                    </div>
                </div>
            </div>

            <div className="pt-6 border-t border-gray-100">
                <button type="submit" className="w-full py-5 bg-gradient-to-r from-rose-600 to-amber-600 text-white font-black rounded-2xl shadow-xl hover:shadow-rose-200 transition-all uppercase tracking-widest text-sm active:scale-[0.98] border-b-4 border-rose-800">
                    🚀 Kirim Laporan Lengkap Divisi Fleece
                </button>
            </div>
        </form>
    );
}
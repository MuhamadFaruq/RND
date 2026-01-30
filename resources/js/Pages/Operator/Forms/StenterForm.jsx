import React from 'react';

// Tambahkan fetchSapDetail dan isSearching ke dalam props
export default function StenterForm({ 
    formData = {}, // Gunakan default value agar tidak crash
    onInputChange, 
    onSubmit,
    fetchSapDetail,
    isSearching 
}) {
    // Helper untuk memudahkan input berulang
    const phases = ['preset', 'drying', 'finishing'];
    
    return (
        <form onSubmit={onSubmit} className="space-y-6">
            {/* --- BAGIAN BARU: PENCARIAN SAP OTOMATIS --- */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-[2rem] border-2 border-slate-100">
                <div className="relative">
                    <label className="text-[10px] font-black text-slate-400 uppercase italic">Nomor SAP</label>
                    <input 
                        type="number" 
                        value={formData.sap_no || ''} 
                        onChange={(e) => onInputChange('sap_no', e.target.value)}
                        onBlur={(e) => fetchSapDetail(e.target.value)} // Trigger pencarian saat pindah kolom
                        placeholder="Ketik No. SAP..."
                        className={`w-full border-2 rounded-xl font-black ${isSearching ? 'border-yellow-400 animate-pulse' : 'border-white'}`} 
                    />
                    {isSearching && <span className="absolute right-3 top-9 text-[9px] font-bold text-yellow-600">Mencari...</span>}
                </div>
                <div>
                    <label className="text-[10px] font-black text-slate-400 uppercase italic">Pelanggan (Auto)</label>
                    <input 
                        type="text" 
                        value={formData.pelanggan || ''} 
                        readOnly 
                        className="w-full border-none bg-slate-100 rounded-xl font-bold text-slate-500" 
                        placeholder="Terisi otomatis..."
                    />
                </div>
                <div>
                    <label className="text-[10px] font-black text-slate-400 uppercase italic">Jenis Kain (Auto)</label>
                    <input 
                        type="text" 
                        value={formData.jenis_kain || ''} 
                        readOnly 
                        className="w-full border-none bg-slate-100 rounded-xl font-bold text-slate-500" 
                        placeholder="Terisi otomatis..."
                    />
                </div>
            </div>

            {/* INFO UMUM */}
            <div className="bg-emerald-50 p-4 rounded-xl border border-emerald-100 flex gap-4 items-center">
                <div className="flex-1">
                    <label className="text-[10px] font-black text-emerald-700 uppercase italic">Tanggal Proses (DATE)</label>
                    <input 
                        type="date" 
                        value={formData.tanggal_stenter || ''} 
                        onChange={(e) => onInputChange('tanggal_stenter', e.target.value)} 
                        className="w-full border-none bg-transparent text-lg font-black focus:ring-0 p-0" 
                        required 
                    />
                </div>
            </div>

            {/* TABEL PARAMETER STENTER */}
            <div className="overflow-x-auto bg-white rounded-3xl border border-gray-100 shadow-sm">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="bg-gray-900 text-white">
                            <th className="p-4 text-[10px] font-black uppercase tracking-widest border-r border-gray-800">Parameter</th>
                            <th className="p-4 text-[10px] font-black uppercase tracking-widest text-center border-r border-gray-800">Preset</th>
                            <th className="p-4 text-[10px] font-black uppercase tracking-widest text-center border-r border-gray-800">Drying</th>
                            <th className="p-4 text-[10px] font-black uppercase tracking-widest text-center">Finishing</th>
                        </tr>
                    </thead>
                    <tbody className="text-xs font-bold text-gray-600">
                        {[
                            { label: 'Temperature (°C)', field: 'temp' },
                            { label: 'Speed (m/min)', field: 'speed' },
                            { label: 'Padder (Bar)', field: 'padder' },
                            { label: 'Rangka', field: 'rangka' },
                            { label: 'Overfeed A (%)', field: 'overfeed_a' },
                            { label: 'Overfeed B (%)', field: 'overfeed_b' },
                            { label: 'Fan/Blower (Rpm)', field: 'fan' },
                            { label: 'Delivery Speed', field: 'delivery' },
                            { label: 'Folding Speed', field: 'folding' },
                            { label: 'Chemical 1', field: 'chem_1' },
                            { label: 'Chemical 2', field: 'chem_2' },
                            { label: 'Hasil Lebar (Inch)', field: 'lebar' },
                            { label: 'Hasil Gramasi', field: 'gramasi' },
                            { label: 'Shrinkage (%)', field: 'shrinkage' },
                        ].map((item, index) => (
                            <tr key={index} className={index % 2 === 0 ? 'bg-gray-50/50' : 'bg-white'}>
                                <td className="p-3 border-r border-gray-100 bg-gray-50/80 sticky left-0 uppercase tracking-tighter">{item.label}</td>
                                {phases.map(phase => (
                                    <td key={phase} className="p-1 border-r border-gray-100">
                                        <input 
                                            type="text"
                                            placeholder="-"
                                            value={formData[`${phase}_${item.field}`] || ''}
                                            onChange={(e) => onInputChange(`${phase}_${item.field}`, e.target.value)}
                                            className="w-full border-none bg-transparent text-center focus:ring-2 focus:ring-[#ED1C24] rounded-md py-2"
                                        />
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* SUBMIT BUTTON */}
            <div className="pt-4">
                <button 
                    type="submit" 
                    disabled={isSearching}
                    className={`w-full py-5 bg-gradient-to-r from-gray-900 to-black text-white font-black rounded-2xl shadow-xl transition-all uppercase tracking-widest text-sm active:scale-95 border-b-4 border-red-600 ${isSearching ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-red-200'}`}
                >
                    {isSearching ? '⏳ Memproses Data SAP...' : '🚀 Kirim Laporan Komprehensif Stenter'}
                </button>
            </div>
        </form>
    );
}
import React from 'react';

export default function WarnaMonitoring({ dataWarna }) {
    return (
        <div className="p-6 bg-white rounded-3xl shadow-xl border border-gray-100">
            <div className="flex justify-between items-center mb-6 border-b-4 border-purple-600 pb-4">
                <h2 className="text-2xl font-black uppercase italic tracking-tighter text-purple-900">
                    🎨 Monitoring Warna
                </h2>
                <div className="flex gap-4 items-center">
                    <div className="bg-purple-100 px-4 py-1 rounded-full border border-purple-200">
                        <span className="text-[10px] font-black text-purple-600 uppercase tracking-widest italic">Flow: DDT2 → DPF3 → Finish</span>
                    </div>
                </div>
            </div>

            <div className="overflow-x-auto rounded-2xl border border-gray-200 shadow-inner">
                <table className="w-full text-[11px] text-left border-collapse font-bold uppercase">
                    <thead>
                        <tr className="bg-purple-900 text-white text-[9px] uppercase tracking-[0.2em] font-black">
                            <th className="p-3 border-r border-purple-800">SAP / ART</th>
                            <th className="p-3 border-r border-purple-800">MKT & Pelanggan</th>
                            <th className="p-3 border-r border-purple-800">Spek Dasar (K/L/B/G)</th>
                            <th className="p-3 border-r border-purple-800">Warna / Handfeel</th>
                            <th className="p-3 border-r border-purple-800">Keterangan / KG</th>
                            <th className="p-3 border-r border-purple-800 bg-purple-800 text-center uppercase">Logistik / Tracking</th>
                            <th className="p-3 bg-red-600 text-center">Timeline</th>
                        </tr>
                    </thead>
                    <tbody className="text-gray-700">
                        {dataWarna.map((item, i) => (
                            <tr key={i} className="border-b hover:bg-purple-50 transition-colors">
                                <td className="p-3 border-r leading-tight">
                                    <div className="text-purple-700 font-black">{item.sap}</div>
                                    <div className="text-gray-400">{item.art}</div>
                                </td>
                                <td className="p-3 border-r max-w-[200px] truncate">{item.mkt_pelanggan}</td>
                                <td className="p-3 border-r italic text-gray-500 leading-tight">
                                    {item.konstruksi} <br/>
                                    L: {item.target_lebar} | {item.belah_bulat} | G: {item.target_gramasi}
                                </td>
                                <td className="p-3 border-r">
                                    <div className="text-gray-900 font-black">{item.warna}</div>
                                    <div className="text-[9px] bg-gray-100 px-1 inline-block rounded uppercase tracking-widest text-gray-500">HF: {item.handfeel}</div>
                                </td>
                                <td className="p-3 border-r leading-tight">
                                    <div className="text-[9px] mb-1 italic truncate">{item.keterangan}</div>
                                    <div className="flex gap-2">
                                        <span className="text-purple-600 font-black">{item.kg} KG</span>
                                        <span className="text-gray-400">({item.roll} R)</span>
                                    </div>
                                </td>
                                <td className="p-0 border-r bg-gray-50">
                                    <div className="grid grid-cols-3 h-full text-center text-[9px] font-black uppercase">
                                        <div className="p-2 border-r leading-none"><span className="text-gray-400 block mb-1">Kirim DDT2</span>{item.kirim_ddt2}</div>
                                        <div className="p-2 border-r leading-none"><span className="text-gray-400 block mb-1">Terima DPF3</span>{item.terima_dpf3}</div>
                                        <div className="p-2 leading-none font-bold text-green-600 italic"><span className="text-gray-400 block mb-1">TGL SELESAI</span>{item.tgl_selesai}</div>
                                    </div>
                                </td>
                                <td className="p-3 bg-red-50 text-red-700 font-black italic text-center text-sm">{item.timeline}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
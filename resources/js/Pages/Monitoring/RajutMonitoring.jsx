import React from 'react';

export default function RajutMonitoring({ dataRajut }) {
    return (
        <div className="p-6 bg-white rounded-3xl shadow-xl border border-gray-100">
            <div className="flex justify-between items-center mb-6 border-b-4 border-blue-600 pb-4">
                <h2 className="text-2xl font-black uppercase italic tracking-tighter text-blue-900">
                    🧶 Monitoring Rajut
                </h2>
                <div className="text-right">
                    <span className="text-[10px] font-bold text-gray-400 uppercase block leading-none">Divisi Kerja</span>
                    <span className="text-sm font-black text-blue-600 uppercase italic">Knitting Section</span>
                </div>
            </div>

            <div className="overflow-x-auto rounded-2xl border border-gray-200 shadow-inner">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="bg-gray-900 text-white text-[10px] uppercase tracking-widest font-black">
                            <th className="p-4 border-r border-gray-800">SAP</th>
                            <th className="p-4 border-r border-gray-800">ART</th>
                            <th className="p-4 border-r border-gray-800">Pelanggan</th>
                            <th className="p-4 border-r border-gray-800">MKT</th>
                            <th className="p-4 border-r border-gray-800">Konstruksi</th>
                            <th className="p-4 border-r border-gray-800">Gramasi</th>
                            <th className="p-4 border-r border-gray-800">Mesin / Kelp</th>
                            <th className="p-4 border-r border-gray-800">T. Lebar</th>
                            <th className="p-4 border-r border-gray-800">B/B</th>
                            <th className="p-4 border-r border-gray-800">T. Gramasi</th>
                            <th className="p-4 border-r border-gray-800">ROLL/KG</th>
                            <th className="p-4 bg-red-600">Timeline DPF3</th>
                        </tr>
                    </thead>
                    <tbody className="text-[11px] font-bold text-gray-700 uppercase">
                        {dataRajut.map((item, i) => (
                            <tr key={i} className="border-b hover:bg-blue-50 transition-colors">
                                <td className="p-4 border-r font-black text-blue-600">{item.sap}</td>
                                <td className="p-4 border-r">{item.art}</td>
                                <td className="p-4 border-r max-w-[150px] truncate">{item.pelanggan}</td>
                                <td className="p-4 border-r">{item.mkt}</td>
                                <td className="p-4 border-r">{item.konstruksi}</td>
                                <td className="p-4 border-r">{item.gramasi_greige}</td>
                                <td className="p-4 border-r">
                                    <div className="leading-tight">
                                        <div className="text-gray-900">{item.mesin}</div>
                                        <div className="text-[9px] text-gray-400 italic">{item.kelompok}</div>
                                    </div>
                                </td>
                                <td className="p-4 border-r italic">{item.target_lebar}</td>
                                <td className="p-4 border-r font-black">{item.belah_bulat}</td>
                                <td className="p-4 border-r italic">{item.target_gramasi}</td>
                                <td className="p-4 border-r">
                                    <div className="flex gap-1">
                                        <span className="bg-gray-100 px-1 rounded">{item.roll} R</span>
                                        <span className="bg-blue-100 px-1 rounded text-blue-700">{item.kg} K</span>
                                    </div>
                                </td>
                                <td className="p-4 bg-red-50 text-red-700 font-black italic">{item.timeline}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
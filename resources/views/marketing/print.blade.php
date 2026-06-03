<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $order->art_no }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: white; 
            color: black; 
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            margin: 0;
            padding: 0;
        }

        @media print {
            @page { 
                size: A4 portrait; 
                margin: 0; /* Menghilangkan header/footer otomatis browser (URL, Tanggal, dll) */
            }
            body {
                padding: 1.5cm; /* Margin manual agar konten tetap rapi di tengah kertas */
            }
            .no-print { display: none !important; }
            
            html, body {
                height: 100%;
                overflow: hidden;
            }
            .page-container {
                height: 100%; 
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
        }

        .table-standard td { padding: 8px 12px !important; line-height: 1.2; border: 1.5px solid #000; }
        .label-cell { background-color: #f1f5f9; font-size: 10px; font-weight: 800; color: #1e293b; width: 25%; text-transform: uppercase; letter-spacing: 0.05em; }
        .value-cell { font-size: 13px; font-weight: 900; color: #000; text-transform: uppercase; }
        
        .section-header {
            font-size: 11px;
            font-weight: 900;
            background: #000;
            color: #fff;
            padding: 4px 12px;
            display: inline-block;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-style: italic;
        }
    </style>
</head>
<body class="p-8">
    {{-- Floating Print Button --}}
    <div class="fixed top-6 right-6 no-print">
        <button onclick="window.print()" class="bg-[#ED1C24] text-white px-8 py-3 rounded-full font-black shadow-2xl hover:bg-black transition-all flex items-center gap-3 text-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            PRINT SURAT JALAN
        </button>
    </div>

    <div class="page-container mx-auto max-w-4xl">
        
        {{-- HEADER --}}
        <div class="flex justify-between items-center border-b-4 border-black pb-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-[#ED1C24] flex items-center justify-center rounded-xl shadow-lg">
                    <span class="text-white text-3xl font-black italic">D</span>
                </div>
                <div>
                    <h1 class="text-3xl font-[900] italic tracking-tighter uppercase leading-none text-black">DUNIATEX <span class="text-[#ED1C24]">RND</span></h1>
                    <p class="text-[10px] font-black tracking-[0.4em] text-slate-400 mt-1 uppercase italic">Production Monitoring & Control</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-[900] italic uppercase text-black leading-none">SURAT JALAN</h2>
                <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-2 italic font-mono">ID: SJ/{{ date('Y') }}/{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

        {{-- VERTICAL SECTIONS --}}
        <div class="flex-grow space-y-6">
            
            {{-- POINT I --}}
            <div class="space-y-2">
                <div class="section-header">I. Identitas Pesanan</div>
                <table class="w-full table-standard border-collapse">
                    <tr>
                        <td class="label-cell">Nomor Artikel</td>
                        <td class="value-cell text-red-600 font-black text-lg">{{ $order->art_no }}</td>
                        <td class="label-cell">Nomor SAP</td>
                        <td class="value-cell">{{ $order->sap_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Pelanggan</td>
                        <td class="value-cell">{{ $order->pelanggan }}</td>
                        <td class="label-cell">Tanggal Order</td>
                        <td class="value-cell">{{ \Carbon\Carbon::parse($order->tanggal)->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>
            </div>

            {{-- POINT II --}}
            <div class="space-y-2">
                <div class="section-header">II. Spesifikasi Target</div>
                <table class="w-full table-standard border-collapse">
                    <tr>
                        <td class="label-cell">Target Produksi</td>
                        <td class="value-cell">{{ $order->kg_target }} KG / {{ $order->roll_target }} ROLL</td>
                        <td class="label-cell">Target Warna</td>
                        <td class="value-cell text-red-600 italic font-black">{{ $order->warna }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Lebar Kain</td>
                        <td class="value-cell">{{ $order->target_lebar }} INCH</td>
                        <td class="label-cell">Gramasi Kain</td>
                        <td class="value-cell">{{ $order->target_gramasi }} GSM</td>
                    </tr>
                </table>
            </div>

            {{-- POINT III --}}
            <div class="space-y-2">
                <div class="section-header">III. Detail Teknis & Parameter</div>
                <table class="w-full table-standard border-collapse">
                    <tr>
                        <td class="label-cell">Konstruksi Greige</td>
                        <td class="value-cell" colspan="3">{{ $order->konstruksi_greige ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Material</td>
                        <td class="value-cell">{{ $order->material ?: '-' }}</td>
                        <td class="label-cell">Jenis Benang</td>
                        <td class="value-cell">{{ $order->benang ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Handfeel</td>
                        <td class="value-cell">{{ $order->handfeel ?: '-' }}</td>
                        <td class="label-cell">Kelompok Kain</td>
                        <td class="value-cell">{{ $order->kelompok_kain ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Treatment Khusus</td>
                        <td class="value-cell" colspan="3">{{ $order->treatment_khusus ?: 'Standard Treatment' }}</td>
                    </tr>
                    <tr>
                        <td class="label-cell">Instruksi Khusus</td>
                        <td class="value-cell normal-case italic font-semibold text-slate-700 leading-relaxed text-xs" colspan="3">
                            "{{ $order->keterangan_artikel ?: 'Produksi sesuai dengan parameter standar RND Duniatex.' }}"
                        </td>
                    </tr>
                </table>
            </div>

            {{-- POINT IV: MARKETING --}}
            <div class="space-y-2">
                <div class="section-header">IV. Penanggung Jawab</div>
                <table class="w-full table-standard border-collapse">
                    <tr>
                        <td class="label-cell">Marketing / Sales</td>
                        <td class="value-cell font-black italic underline decoration-red-600 decoration-2 underline-offset-4">{{ $order->mkt }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- SIGNATURE SECTION --}}
        <div class="mt-12">
            <div class="grid grid-cols-3 gap-8 text-center">
                <div class="space-y-16">
                    <p class="text-[10px] font-black uppercase text-slate-500 tracking-widest border-b-2 border-slate-200 pb-2">Dibuat Oleh</p>
                    <p class="text-sm font-black uppercase italic">{{ $order->mkt }}</p>
                </div>
                <div class="space-y-16">
                    <p class="text-[10px] font-black uppercase text-slate-500 tracking-widest border-b-2 border-slate-200 pb-2">Plant Manager</p>
                    <p class="text-sm font-black uppercase italic">( ........................... )</p>
                </div>
                <div class="space-y-16">
                    <p class="text-[10px] font-black uppercase text-slate-500 tracking-widest border-b-2 border-slate-200 pb-2">Produksi</p>
                    <p class="text-sm font-black uppercase italic">( ........................... )</p>
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="flex justify-between items-center text-[8px] font-black text-slate-400 uppercase tracking-[0.5em] italic mt-12 pt-4 border-t border-slate-100">
                <p>Digitalized Document &middot; RND SYSTEM 2026</p>
                <p>Generated At: {{ now()->format('d/m/Y H:i:s') }}</p>
            </div>
        </div>

    </div>

</body>
</html>

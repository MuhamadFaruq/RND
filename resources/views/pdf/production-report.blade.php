<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Production Report - DUNIATEX RND</title>
    <style>
        @page { size: A4 portrait; margin: 1cm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; font-size: 9px; margin: 0; padding: 0; }
        
        /* Typography */
        h1, h2, h3, p { margin: 0; padding: 0; }
        .text-brand { color: #ED1C24; }
        .font-black { font-weight: 900; }
        .font-bold { font-weight: 700; }
        .italic { font-style: italic; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-xs { font-size: 7px; color: #64748b; }
        
        /* Layout */
        .header-box { border-bottom: 3px solid #1e293b; padding-bottom: 10px; margin-bottom: 20px; }
        .meta-info { font-size: 8px; color: #475569; margin-top: 4px; text-transform: uppercase; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { font-weight: bold; text-transform: uppercase; padding: 6px; font-size: 8px; border: 1px solid #cbd5e1; }
        td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: middle; }
        
        /* Table Colors */
        .th-master { background-color: #0f172a; color: #ffffff; }
        .th-marketing { background-color: #f59e0b; color: #ffffff; }
        .th-knitting { background-color: #2563eb; color: #ffffff; }
        .th-dyeing { background-color: #b91c1c; color: #ffffff; }
        
        /* KPI Cards */
        .kpi-container { width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 10px 0; }
        .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; text-align: center; }
        .kpi-title { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 4px; }
        .kpi-value { font-size: 14px; font-weight: 900; color: #0f172a; }
        
        /* Striping & Highlights */
        tr:nth-child(even) td { background-color: #f8fafc; }
        .val-highlight { font-weight: bold; color: #000; text-align: right; }
    </style>
</head>
<body>

    {{-- 1. HEADER DOKUMEN --}}
    <table style="border: none; margin-bottom: 15px;">
        <tr>
            <td style="border: none; padding: 0;">
                <h1 class="font-black italic" style="font-size: 22px;">DUNIATEX <span class="text-brand">RND</span></h1>
                <h2 class="font-bold" style="font-size: 12px; color: #334155;">PRODUCTION INTELLIGENCE REPORT</h2>
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                <p class="font-black" style="font-size: 14px; text-transform: uppercase;">
                    DIVISI: {{ $selectedDivision == 'all' ? 'MASTER PIPELINE' : ($selectedDivision == 'marketing' ? 'MARKETING' : ($selectedDivision == 'knitting' ? 'KNITTING (RAJUT)' : 'DYEING & FINISHING')) }}
                </p>
                <p class="meta-info">PERIODE: {{ $period }}</p>
                <p class="meta-info">WAKTU CETAK: {{ $generated_at }}</p>
            </td>
        </tr>
    </table>
    <div style="border-bottom: 2px solid #0f172a; margin-bottom: 20px;"></div>

    {{-- 2. KPI SUMMARY --}}
    @php
        $totalKg = collect($activities)->sum('kg');
        $totalRoll = collect($activities)->sum('roll');
        $totalData = count($activities);
    @endphp
    <table class="kpi-container">
        <tr>
            <td class="kpi-card" style="border-top: 3px solid #6366f1;">
                <div class="kpi-title">Total Data Entries</div>
                <div class="kpi-value">{{ number_format($totalData, 0) }}</div>
            </td>
            <td class="kpi-card" style="border-top: 3px solid #10b981;">
                <div class="kpi-title">Total Production (KG)</div>
                <div class="kpi-value">{{ number_format($totalKg, 2) }} KG</div>
            </td>
            <td class="kpi-card" style="border-top: 3px solid #f59e0b;">
                <div class="kpi-title">Total Volume (Roll)</div>
                <div class="kpi-value">{{ number_format($totalRoll, 0) }} ROLL</div>
            </td>
        </tr>
    </table>

    {{-- 3. DATA TABLES BERDASARKAN DIVISI --}}

    @if($selectedDivision == 'marketing')
        {{-- TABEL MARKETING --}}
        <table>
            <thead>
                <tr>
                    <th class="th-marketing">NO</th>
                    <th class="th-marketing">TANGGAL</th>
                    <th class="th-marketing text-left">NO. ARTIKEL</th>
                    <th class="th-marketing text-left">PELANGGAN</th>
                    <th class="th-marketing">LEBAR/GSM</th>
                    <th class="th-marketing">WARNA</th>
                    <th class="th-marketing text-right">T. ROLL</th>
                    <th class="th-marketing text-right">T. KG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $index => $act)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($act->tanggal)->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ $act->art_no }}</td>
                        <td>{{ $act->pelanggan }}</td>
                        <td class="text-center">{{ $act->target_lebar ?? '-' }} / {{ $act->target_gramasi ?? '-' }}</td>
                        <td class="text-center">{{ $act->warna }}</td>
                        <td class="val-highlight">{{ number_format($act->roll_target ?? 0, 0) }}</td>
                        <td class="val-highlight">{{ number_format($act->kg_target ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data Marketing di periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($selectedDivision == 'knitting')
        {{-- TABEL KNITTING --}}
        <table>
            <thead>
                <tr>
                    <th class="th-knitting">NO</th>
                    <th class="th-knitting">WAKTU PRODUKSI</th>
                    <th class="th-knitting text-left">NO. ARTIKEL</th>
                    <th class="th-knitting text-left">PELANGGAN</th>
                    <th class="th-knitting">MESIN</th>
                    <th class="th-knitting">BENANG UTAMA (YL)</th>
                    <th class="th-knitting text-right">ROLL</th>
                    <th class="th-knitting text-right">KG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $index => $act)
                    @php 
                        $tech = is_string($act->technical_data) ? json_decode($act->technical_data, true) : $act->technical_data; 
                        $benang = ($tech['benang_1'] ?? '-') . ' (YL:' . ($tech['yl_1'] ?? '0') . ')';
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $act->created_at->format('d/m/Y H:i') }}</td>
                        <td class="font-bold">{{ $act->marketingOrder->art_no ?? '-' }}</td>
                        <td>{{ $act->marketingOrder->pelanggan ?? '-' }}</td>
                        <td class="text-center">{{ $tech['no_mesin'] ?? '-' }}</td>
                        <td class="text-center" style="font-size: 7px;">{{ $benang }}</td>
                        <td class="val-highlight">{{ number_format($act->roll ?? 0, 0) }}</td>
                        <td class="val-highlight">{{ number_format($act->kg ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data Knitting di periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

    @elseif($selectedDivision == 'dyeing')
        {{-- TABEL DYEING & FINISHING --}}
        <table>
            <thead>
                <tr>
                    <th class="th-dyeing">NO</th>
                    <th class="th-dyeing">WAKTU PRODUKSI</th>
                    <th class="th-dyeing">TAHAPAN</th>
                    <th class="th-dyeing text-left">NO. ARTIKEL</th>
                    <th class="th-dyeing text-left">WARNA KAIN</th>
                    <th class="th-dyeing">MESIN</th>
                    <th class="th-dyeing text-right">ROLL</th>
                    <th class="th-dyeing text-right">KG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $index => $act)
                    @php $tech = is_string($act->technical_data) ? json_decode($act->technical_data, true) : $act->technical_data; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $act->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center font-bold">{{ strtoupper(str_replace('-', ' ', $act->division_name)) }}</td>
                        <td class="font-bold">{{ $act->marketingOrder->art_no ?? '-' }}</td>
                        <td>{{ $act->marketingOrder->warna ?? '-' }}</td>
                        <td class="text-center">{{ $tech['no_mesin'] ?? '-' }}</td>
                        <td class="val-highlight">{{ number_format($act->roll ?? 0, 0) }}</td>
                        <td class="val-highlight">{{ number_format($act->kg ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data Dyeing & Finishing di periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>

    @else
        {{-- TABEL MASTER PIPELINE --}}
        <table>
            <thead>
                <tr>
                    <th class="th-master">NO</th>
                    <th class="th-master">WAKTU</th>
                    <th class="th-master">DIVISI / PROSES</th>
                    <th class="th-master text-left">NO. ARTIKEL</th>
                    <th class="th-master text-left">PELANGGAN</th>
                    <th class="th-master">OPERATOR</th>
                    <th class="th-master text-right">ROLL</th>
                    <th class="th-master text-right">KG</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities->take(200) as $index => $act) {{-- Batasi agar PDF tidak terlalu berat/lambat --}}
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $act->created_at->format('d/m/y H:i') }}</td>
                        <td class="text-center font-bold">{{ strtoupper(str_replace('-', ' ', $act->division_name)) }}</td>
                        <td class="font-bold">{{ $act->marketingOrder->art_no ?? '-' }}</td>
                        <td>{{ $act->marketingOrder->pelanggan ?? '-' }}</td>
                        <td class="text-center">{{ $act->operator_name ?? ($act->user->name ?? '-') }}</td>
                        <td class="val-highlight">{{ number_format($act->roll ?? 0, 0) }}</td>
                        <td class="val-highlight">{{ number_format($act->kg ?? 0, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center" style="padding: 20px;">Tidak ada data produksi untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if(count($activities) > 200)
            <p class="text-xs text-center" style="margin-top: 5px;">*Menampilkan 200 data terakhir. Unduh format Excel (XLS) untuk melihat seluruh data secara lengkap.</p>
        @endif
    @endif

    {{-- FOOTER TANDA TANGAN --}}
    <table style="border: none; margin-top: 40px; text-align: center;">
        <tr>
            <td style="border: none; width: 33%;">
                <p style="margin-bottom: 60px;">Disiapkan Oleh,</p>
                <p class="font-bold">{{ $admin_name }}</p>
                <p class="text-xs">Administrator Sistem</p>
            </td>
            <td style="border: none; width: 33%;">
                <p style="margin-bottom: 60px;">Diketahui Oleh,</p>
                <p class="font-bold">( ................................ )</p>
                <p class="text-xs">Kepala Produksi</p>
            </td>
            <td style="border: none; width: 33%;">
                <p style="margin-bottom: 60px;">Disetujui Oleh,</p>
                <p class="font-bold">( ................................ )</p>
                <p class="text-xs">Plant Manager</p>
            </td>
        </tr>
    </table>

</body>
</html>
<style>
    body { font-family: sans-serif; color: #333; }
    .header { border-bottom: 2px solid #ed1c24; padding-bottom: 10px; margin-bottom: 20px; }
    .title { font-size: 20px; font-weight: bold; text-transform: uppercase; }
    .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    .kpi-table td { padding: 15px; border: 1px solid #eee; text-align: center; }
    .label { font-size: 10px; color: #888; text-transform: uppercase; }
    .value { font-size: 14px; font-weight: bold; color: #ed1c24; }
    
    /* Style untuk Heatmap di PDF */
    .heatmap-box {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 10px;
    }
    .heatmap-box td {
        border: 1px solid #fff;
        height: 25px;
        text-align: center;
        font-size: 7px;
        font-weight: bold;
        color: #fff;
    }
    .hour-label { font-size: 7px; color: #999; text-align: center; margin-top: 4px; }
    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        font-size: 8px; 
        margin-top: 10px; 
    }
    .data-table th { 
        background-color: #f8f9fa; 
        border: 1px solid #eee; 
        padding: 8px; 
        text-align: left; 
        text-transform: uppercase;
    }
    .data-table td { 
        border: 1px solid #eee; 
        padding: 6px; 
    }
</style>

{{-- HEADER TETAP SAMA SEPERTI MILIK ANDA --}}
<div class="header">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 60%; vertical-align: bottom;">
                <div class="title">Production Report: {{ $selectedDivision == 'all' ? 'Global' : ucfirst($selectedDivision) }}</div>
                <div style="font-size: 10px; color: #666; margin-top: 5px;">
                    Periode: {{ ucfirst($period) }} | Dicetak: {{ $generated_at }}
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/lg.png'))) }}" style="height: 50px;">
            </td>
        </tr>
    </table>
</div>

{{-- KPI SECTION --}}
<table class="kpi-table">
    <tr>
        @foreach($divisionLeadTimes as $div => $time)
            <td>
                <div class="label">{{ strtoupper($div) }}</div>
                <div class="value">{{ $time > 0 ? $time . ' Days' : '0.0' }}</div>
            </td>
        @endforeach
    </tr>
</table>

{{-- VISUALISASI 1: PRODUCTION TREND (SVG) --}}
<div style="margin-top: 20px; padding: 15px; border: 1px solid #eee; border-radius: 10px;">
    <p style="font-size: 11px; font-weight: bold; margin-bottom: 10px; text-transform: uppercase;">📈 Production Trend Line (KG)</p>
    @php
        $maxVal = collect($trends)->max('total') ?: 1;
        $width = 700; $height = 100;
        $points = "";
        foreach($trends as $i => $d) {
            $x = ($i / (max(count($trends) - 1, 1))) * $width;
            $y = $height - (($d['total'] / $maxVal) * ($height - 20) + 10);
            $points .= "$x,$y ";
        }
    @endphp
    <svg width="{{ $width }}" height="{{ $height }}" style="overflow: visible;">
        <polyline fill="none" stroke="#ed1c24" stroke-width="2" points="{{ $points }}" />
        @foreach($trends as $i => $d)
            @php 
                $cx = ($i / (max(count($trends) - 1, 1))) * $width;
                $cy = $height - (($d['total'] / $maxVal) * ($height - 20) + 10);
            @endphp
            <circle cx="{{ $cx }}" cy="{{ $cy }}" r="3" fill="#ed1c24" />
        @endforeach
    </svg>
    <table style="width: 100%; margin-top: 5px;">
        <tr>
            @foreach($trends as $d)
                <td style="font-size: 8px; color: #999; text-align: center; width: {{ 100/count($trends) }}%;">{{ $d['day'] }}</td>
            @endforeach
        </tr>
    </table>
</div>

{{-- VISUALISASI 2: HOURLY HEATMAP --}}
<div style="margin-top: 20px;">
    <p style="font-size: 11px; font-weight: bold; text-transform: uppercase;">🕒 Hourly Activity Heatmap (Kepadatan Input)</p>
    <table class="heatmap-box">
        <tr>
            @php $maxHourly = collect($hourlyActivity)->max() ?: 1; @endphp
            @for($i = 0; $i < 24; $i++)
                @php 
                    $count = $hourlyActivity[$i] ?? 0;
                    $opacity = $count > 0 ? max(($count / $maxHourly), 0.2) : 0;
                    $bgColor = $count > 0 ? "rgba(237, 28, 36, $opacity)" : "#f5f5f5";
                @endphp
                <td style="background-color: {{ $bgColor }}; color: {{ $count > 0 ? '#fff' : '#ccc' }};">
                    {{ $count }}
                </td>
            @endfor
        </tr>
        <tr>
            @for($i = 0; $i < 24; $i++)
                <td class="hour-label" style="color: #999; border: none;">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</td>
            @endfor
        </tr>
    </table>
</div>
{{-- Di dalam file resources/views/pdf/production-report.blade.php --}}

{{-- LOGIKA TABEL DINAMIS --}}
@if($selectedDivision == 'knitting')
    <div style="margin-top: 20px;">
        <p style="font-size: 11px; font-weight: bold; color: #ed1c24; border-left: 3px solid #ed1c24; padding-left: 10px; text-transform: uppercase;">
            Detail Teknis Produksi: KNITTING (RAJUT)
        </p>
        <table class="data-table"> {{-- Gunakan CSS tabel Anda --}}
            <thead>
                <tr>
                    <th>SAP NO</th>
                    <th>ARTIKEL</th>
                    <th>BENANG 1 & YL</th>
                    <th>BENANG 2 & YL</th>
                    <th>TOTAL KG</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $act)
                    <tr>
                        <td>{{ $act->marketingOrder->sap_no ?? '-' }}</td>
                        <td>{{ $act->marketingOrder->art_no ?? '-' }}</td>
                        <td>{{ $act->technical_data['benang_1'] ?? '-' }} ({{ $act->technical_data['yl_1'] ?? 0 }})</td>
                        <td>{{ $act->technical_data['benang_2'] ?? '-' }} ({{ $act->technical_data['yl_2'] ?? 0 }})</td>
                        <td style="font-weight: bold;">{{ number_format($act->kg, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@elseif($selectedDivision == 'dyeing' || $selectedDivision == 'finishing')
    <div style="margin-top: 20px;">
        <p style="font-size: 11px; font-weight: bold; color: #00529b; border-left: 3px solid #00529b; padding-left: 10px; text-transform: uppercase;">
            Detail Teknis Produksi: WARNA ({{ strtoupper($selectedDivision) }})
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>SAP NO</th>
                    <th>WARNA / HANDFEEL</th>
                    <th>TARGET LEBAR/GRAMASI</th>
                    <th>HASIL KG</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities as $act)
                    <tr>
                        <td>{{ $act->marketingOrder->sap_no ?? '-' }}</td>
                        <td>{{ $act->warna }} / {{ $act->handfeel }}</td>
                        <td>{{ $act->target_lebar }} / {{ $act->target_gramasi }}</td>
                        <td style="font-weight: bold;">{{ number_format($act->kg, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@else {{-- Mode 'ALL' (Tabel Ringkasan) --}}
    <div style="margin-top: 20px;">
        <p style="font-size: 11px; font-weight: bold; color: #333; border-left: 3px solid #333; padding-left: 10px; text-transform: uppercase;">
            Ringkasan Aktivitas Seluruh Unit
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>WAKTU</th>
                    <th>DIVISI</th>
                    <th>SAP NO</th>
                    <th>HASIL (KG)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities->take(50) as $act)
                    <tr>
                        <td>{{ $act->created_at->format('H:i') }}</td>
                        <td>{{ strtoupper($act->division_name) }}</td>
                        <td>{{ $act->marketingOrder->sap_no ?? '-' }}</td>
                        <td>{{ number_format($act->kg, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if($selectedDivision == 'knitting' || $selectedDivision == 'all')
<div style="margin-top: 30px;">
    <p style="font-size: 12px; font-weight: bold; color: #ed1c24; border-left: 3px solid #ed1c24; padding-left: 10px;">
        DETAIL PENGGUNAAN BENANG & YARN LENGTH (KNITTING)
    </p>
    <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin-top: 10px;">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="border: 1px solid #eee; padding: 8px;">SAP NO</th>
                <th style="border: 1px solid #eee; padding: 8px;">ARTIKEL / PELANGGAN</th>
                <th style="border: 1px solid #eee; padding: 8px;">BENANG 1 & YL</th>
                <th style="border: 1px solid #eee; padding: 8px;">BENANG 2 & YL</th>
                <th style="border: 1px solid #eee; padding: 8px;">BENANG 3 & YL</th>
                <th style="border: 1px solid #eee; padding: 8px;">BENANG 4 & YL</th>
                <th style="border: 1px solid #eee; padding: 8px;">TOTAL (KG/ROLL)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($activities->where('division_name', 'knitting') as $act)
            <tr>
                <td style="border: 1px solid #eee; padding: 6px; font-weight: bold;">{{ $act->marketingOrder->sap_no ?? '-' }}</td>
                <td style="border: 1px solid #eee; padding: 6px;">
                    {{ $act->marketingOrder->art_no ?? '-' }} <br>
                    <span style="color: #666;">{{ $act->marketingOrder->pelanggan ?? '-' }}</span>
                </td>
                {{-- Mengambil data dari JSON technical_data --}}
                <td style="border: 1px solid #eee; padding: 6px;">{{ $act->technical_data['benang_1'] ?? '-' }} <br> (YL: {{ $act->technical_data['yl_1'] ?? 0 }})</td>
                <td style="border: 1px solid #eee; padding: 6px;">{{ $act->technical_data['benang_2'] ?? '-' }} <br> (YL: {{ $act->technical_data['yl_2'] ?? 0 }})</td>
                <td style="border: 1px solid #eee; padding: 6px;">{{ $act->technical_data['benang_3'] ?? '-' }} <br> (YL: {{ $act->technical_data['yl_3'] ?? 0 }})</td>
                <td style="border: 1px solid #eee; padding: 6px;">{{ $act->technical_data['benang_4'] ?? '-' }} <br> (YL: {{ $act->technical_data['yl_4'] ?? 0 }})</td>
                <td style="border: 1px solid #eee; padding: 6px; font-weight: bold;">{{ $act->kg }} KG / {{ $act->roll }} Roll</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<p style="font-size: 12px; margin-top: 30px;"><strong>Rincian Volume Produksi (Trend):</strong></p>
<table style="width: 100%; border-collapse: collapse; font-size: 10px;">
    <thead>
        <tr style="background-color: #f8f9fa;">
            <th style="border: 1px solid #eee; padding: 10px; text-align: left;">Tanggal</th>
            <th style="border: 1px solid #eee; padding: 10px; text-align: right;">Total Produksi (KG)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($trends as $t)
            <tr>
                <td style="border: 1px solid #eee; padding: 8px;">{{ $t['day'] }}</td>
                <td style="border: 1px solid #eee; padding: 8px; text-align: right;">{{ number_format($t['total'], 2) }} KG</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 50px; width: 100%;">
    <table style="width: 100%;">
        <tr>
            <td style="width: 70%;"></td>
            <td style="text-align: center; font-size: 11px;">
                Dicetak Oleh,<br><br><br><br>
                <strong>{{ $admin_name }}</strong><br>
                (System Administrator)
            </td>
        </tr>
    </table>
</div>
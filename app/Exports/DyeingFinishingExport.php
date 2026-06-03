<?php
namespace App\Exports;

use App\Models\MarketingOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DyeingFinishingExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomStartCell, WithEvents, WithTitle
{
    protected $start;
    protected $end;
    protected $unit;
    protected $orders;
    protected $sheetTitle;
    private $rowNumber = 0;

    public function __construct($start, $end, $unit, $sheetTitle = 'Dyeing & Finishing')
    {
        $this->start = $start;
        $this->end = $end;
        $this->unit = $unit;
        $this->sheetTitle = $sheetTitle;

        $this->orders = MarketingOrder::query()
            ->with(['productionActivities.user']) 
            ->whereHas('productionActivities', function($q) {
                $q->whereIn('division_name', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece'])
                  ->whereDate('created_at', '>=', $this->start)
                  ->whereDate('created_at', '<=', $this->end);
            })
            ->when($this->unit !== 'SEMUA', fn($q) => $q->where('kelompok_kain', $this->unit))
            ->latest()
            ->get();
    }

    public function collection()
    {
        return $this->orders;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return []; // Headings are built manually in styles() for 3-level precision
    }

    public function map($order): array
    {
        $this->rowNumber++;

        $activities = $order->productionActivities;
        $dye = $activities->where('division_name', 'dyeing')->first();
        $rlx = $activities->where('division_name', 'relax-dryer')->first();
        $cmp = $activities->where('division_name', 'compactor')->first();
        $ht = $activities->where('division_name', 'heat-setting')->first();
        $stn = $activities->where('division_name', 'stenter')->first();
        $tmb = $activities->where('division_name', 'tumbler')->first();
        $flc = $activities->where('division_name', 'fleece')->first();

        $getTech = fn($act) => $act && is_string($act->technical_data) ? json_decode($act->technical_data, true) : ($act ? $act->technical_data : []);

        $dData = $getTech($dye);
        $rData = $getTech($rlx);
        $cData = $getTech($cmp);
        $hData = $getTech($ht);
        $sData = $getTech($stn);
        $tData = $getTech($tmb);
        $fData = $getTech($flc);

        $getS = fn($key, $stage) => $sData[$stage][$key] ?? '-';

        return [
            // 1-10: IDENTITAS & MARKETING (A-J)
            $this->rowNumber,
            $order->art_no ?? '-',
            $order->sap_no ?? '-',
            $order->pelanggan ?? '-',
            $order->warna ?? '-',
            $order->material ?? '-',
            $order->benang ?? '-',
            ($order->target_lebar ?? '-') . ' / ' . ($order->target_gramasi ?? '-'),
            $order->roll_target ?? 0,
            $order->kg_target ?? 0,

            // 11-19: SCR/DYEING (K-S)
            $dData['cek_greige'] ?? '-',
            $dye->operator_name ?? ($dye->user->name ?? '-'),
            $dye ? $dye->created_at->format('d/m/Y') : '-',
            $dData['jenis_mesin'] ?? '-',
            $dData['no_mesin'] ?? '-',
            $dData['warna'] ?? '-',
            $dData['kode_warna'] ?? '-',
            $dData['dye_system'] ?? '-',
            $dData['treatment'] ?? '-',

            // 20-30: RELAX DRYER (T-AD)
            $rlx->operator_name ?? ($rlx->user->name ?? '-'),
            $rlx ? $rlx->created_at->format('d/m/Y') : '-',
            $rData['chemical'] ?? '-',
            $rData['handfeel'] ?? '-',
            $rData['no_mesin'] ?? '-',
            $rData['overfeed'] ?? '-',
            $rData['suhu'] ?? '-',
            $rData['speed'] ?? '-',
            $rData['hasil_lebar'] ?? ($rData['lebar'] ?? '-'),
            $rData['hasil_gramasi'] ?? ($rData['gramasi'] ?? '-'),
            $rData['shrinkage'] ?? '-',

            // 31-43: COMPACTOR (AE-AQ)
            $cmp->operator_name ?? ($cmp->user->name ?? '-'),
            $cmp ? $cmp->created_at->format('d/m/Y') : '-',
            $cData['no_mesin'] ?? '-',
            $cData['rangka'] ?? '-',
            $cData['suhu'] ?? '-',
            $cData['speed'] ?? '-',
            $cData['overfeed'] ?? '-',
            $cData['felt'] ?? '-',
            $cData['delivery_speed'] ?? '-',
            $cData['folding_speed'] ?? '-',
            $cData['hasil_lebar'] ?? ($cData['lebar'] ?? '-'),
            $cData['hasil_gramasi'] ?? ($cData['gramasi'] ?? '-'),
            $cData['shrinkage'] ?? '-',

            // 44-54: HEAT SETTING (AR-BB)
            $ht->operator_name ?? ($ht->user->name ?? '-'),
            $ht ? $ht->created_at->format('d/m/Y') : '-',
            $hData['no_mesin'] ?? '-',
            $hData['rangka'] ?? '-',
            $hData['suhu'] ?? '-',
            $hData['speed'] ?? '-',
            $hData['overfeed'] ?? '-',
            $hData['delivery_speed'] ?? '-',
            $hData['folding_speed'] ?? '-',
            $hData['hasil_lebar'] ?? ($hData['lebar'] ?? '-'),
            $hData['hasil_gramasi'] ?? ($hData['gramasi'] ?? '-'),

            // 55-100: STENTER (BC-CV)
            $stn->operator_name ?? ($stn->user->name ?? '-'),
            $getS('tanggal', 'preset'), $getS('tanggal', 'drying'), $getS('tanggal', 'finishing'), // TANGGAL (BD-BF)
            $getS('temperature', 'preset'), $getS('temperature', 'drying'), $getS('temperature', 'finishing'), // TEMP (BG-BI)
            $getS('speed', 'preset'), $getS('speed', 'drying'), $getS('speed', 'finishing'), // SPEED (BJ-BL)
            $getS('padder', 'preset'), $getS('padder', 'drying'), $getS('padder', 'finishing'), // PADDER (BM-BO)
            $getS('rangka', 'preset'), $getS('rangka', 'drying'), $getS('rangka', 'finishing'), // RANGKA (BP-BR)
            $getS('overfeed_a', 'preset'), $getS('overfeed_a', 'drying'), $getS('overfeed_a', 'finishing'), // OVERFEED A (BS-BU)
            $getS('overfeed_b', 'preset'), $getS('overfeed_b', 'drying'), $getS('overfeed_b', 'finishing'), // OVERFEED B (BV-BX)
            $getS('fan', 'preset'), $getS('fan', 'drying'), $getS('fan', 'finishing'), // FAN (BY-CA)
            $getS('delivery_speed', 'preset'), $getS('delivery_speed', 'drying'), $getS('delivery_speed', 'finishing'), // DEL SPEED (CB-CD)
            $getS('folding_speed', 'preset'), $getS('folding_speed', 'drying'), $getS('folding_speed', 'finishing'), // FOLD SPEED (CE-CG)
            $getS('chem_1', 'preset'), $getS('chem_1', 'drying'), $getS('chem_1', 'finishing'), // CHEM 1 (CH-CJ)
            $getS('chem_2', 'preset'), $getS('chem_2', 'drying'), $getS('chem_2', 'finishing'), // CHEM 2 (CK-CM)
            $getS('hasil_lebar', 'preset'), $getS('hasil_lebar', 'drying'), $getS('hasil_lebar', 'finishing'), // H LEBAR (CN-CP)
            $getS('hasil_gramasi', 'preset'), $getS('hasil_gramasi', 'drying'), $getS('hasil_gramasi', 'finishing'), // H GSM (CQ-CS)
            $getS('shrinkage', 'preset'), $getS('shrinkage', 'drying'), $getS('shrinkage', 'finishing'), // SHRINKAGE (CT-CV)

            // 101-109: TUMBLER (CW-DE)
            $tmb->operator_name ?? ($tmb->user->name ?? '-'),
            $tmb ? $tmb->created_at->format('d/m/Y') : '-',
            $tData['suhu'] ?? '-',
            $tData['steam_inject'] ?? '-',
            $tData['hotwind'] ?? '-',
            $tData['coldwind'] ?? '-',
            $tData['lebar'] ?? '-',
            $tData['gramasi'] ?? '-',
            $tData['shrinkage'] ?? '-',

            // 110-119: FLEECE RAISING (DF-DO)
            $fData['raising']['operator'] ?? '-',
            $fData['raising']['tanggal'] ?? '-',
            $fData['raising']['std_bulu'] ?? '-',
            $fData['raising']['speed'] ?? '-',
            $fData['raising']['cloth_out'] ?? '-',
            $fData['raising']['bend_pin'] ?? '-',
            $fData['raising']['straight_pin'] ?? '-',
            $fData['raising']['rpm_drum'] ?? '-',
            $fData['raising']['lebar_gsm'] ?? '-',
            $fData['raising']['drum_brush'] ?? '-',

            // 120-129: FLEECE BRUSHING (DP-DY)
            $fData['brushing']['operator'] ?? '-',
            $fData['brushing']['tanggal'] ?? '-',
            $fData['brushing']['std_bulu'] ?? '-',
            $fData['brushing']['cloth_speed'] ?? '-',
            $fData['brushing']['cloth_out'] ?? '-',
            $fData['brushing']['left_brush'] ?? '-',
            $fData['brushing']['right_brush'] ?? '-',
            $fData['brushing']['rpm_drum'] ?? '-',
            $fData['brushing']['tension'] ?? '-',
            $fData['brushing']['lebar_gsm'] ?? '-',

            // 130-136: FLEECE SHEARING (DZ-EF)
            $fData['shearing']['operator'] ?? '-',
            $fData['shearing']['tanggal'] ?? '-',
            $fData['shearing']['speed'] ?? '-',
            $fData['shearing']['cloth_out'] ?? '-',
            $fData['shearing']['expending'] ?? '-',
            $fData['shearing']['shear'] ?? '-',
            $fData['shearing']['lebar_gsm'] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 7 + count($this->orders); 
        
        // 1. BRANDING HEADER
        $sheet->setCellValue('A1', 'PT DELTA DUNIA TEKSTILE 2 - HORIZONTAL TRACEABILITY REPORT');
        $sheet->setCellValue('A2', 'SECTION: DYEING & FINISHING WORKFLOWS');
        $periodeLabel = date('d M Y', strtotime($this->start)) . ' - ' . date('d M Y', strtotime($this->end));
        $sheet->setCellValue('A3', 'PERIODE: ' . $periodeLabel . ' | KELOMPOK: ' . $this->unit);
        $sheet->setCellValue('A4', 'WAKTU CETAK: ' . now()->format('d/m/Y H:i:s'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('ED1C24')); 
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1e293b'));
        $sheet->getStyle('A3:A4')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));

        // 2. BUILD MASSIVE 3-LEVEL HEADER (Row 5, 6, 7)
        // ZONE 1: MARKETING (A-J)
        $sheet->setCellValue('A5', 'IDENTITAS PESANAN & TARGET MARKETING'); $sheet->mergeCells('A5:J5');
        $headers1 = ['NO', 'NO. ARTIKEL', 'SAP ID', 'PELANGGAN', 'WARNA', 'MATERIAL', 'BENANG', 'TARGET LBR/GSM', 'TARGET ROLL', 'TARGET KG'];
        foreach($headers1 as $i => $h) { $sheet->setCellValueByColumnAndRow($i+1, 6, $h); $sheet->mergeCellsByColumnAndRow($i+1, 6, $i+1, 7); }

        // ZONE 2: DYEING (K-S)
        $sheet->setCellValue('K5', 'SCR / DYEING'); $sheet->mergeCells('K5:S5');
        $headers2 = ['CEK GREIGE LEBAR/GRAMASI', 'OPERATOR', 'TANGGAL', 'JENIS MESIN', 'NO. MESIN', 'WARNA', 'KODE WARNA', 'DYE SYSTEM', 'TREATMENT (CHEMICAL)'];
        foreach($headers2 as $i => $h) { $sheet->setCellValueByColumnAndRow($i+11, 6, $h); $sheet->mergeCellsByColumnAndRow($i+11, 6, $i+11, 7); }

        // ZONE 3: RELAX DRYER (T-AD)
        $sheet->setCellValue('T5', 'RELAX DRYER'); $sheet->mergeCells('T5:AD5');
        $headers3 = ['OPERATOR', 'TANGGAL', 'CHEMICAL', 'HANDFEEL', 'MESIN', 'OVERFEED', 'TEMPERATUR', 'SPEED', 'HASIL LEBAR', 'HASIL GRAMASI', 'SHRINKAGE (V X H)'];
        foreach($headers3 as $i => $h) { $sheet->setCellValueByColumnAndRow($i+20, 6, $h); $sheet->mergeCellsByColumnAndRow($i+20, 6, $i+20, 7); }

        // ZONE 4: COMPACTOR (AE-AQ)
        $sheet->setCellValue('AE5', 'FINISHING COMPACTOR (BULAT COTTON)'); $sheet->mergeCells('AE5:AQ5');
        $headers4 = ['OPERATOR', 'TANGGAL', 'NO MESIN', 'RANGKA', 'TEMPERATURE', 'SPEED', 'OVERFEED', 'FELT', 'DELIVERY SPEED', 'FOLDING SPEED', 'HASIL LEBAR', 'HASIL GRAMASI', 'SHRINKAGE (V x H)'];
        foreach($headers4 as $i => $h) { $sheet->setCellValueByColumnAndRow($i+31, 6, $h); $sheet->mergeCellsByColumnAndRow($i+31, 6, $i+31, 7); }

        // ZONE 5: HEAT SETTING (AR-BB)
        $sheet->setCellValue('AR5', 'HEAT SETTING (FINISHING BULAT POLIESTER/PE)'); $sheet->mergeCells('AR5:BB5');
        $headers5 = ['OPERATOR', 'TANGGAL', 'NO MESIN', 'RANGKA', 'TEMPERATUR', 'SPEED', 'OVERFEED', 'DELIVERY SPEED', 'FOLDING SPEED', 'HASIL LEBAR', 'HASIL GRAMASI'];
        foreach($headers5 as $i => $h) { $sheet->setCellValueByColumnAndRow($i+44, 6, $h); $sheet->mergeCellsByColumnAndRow($i+44, 6, $i+44, 7); }

        // ZONE 6: STENTER (BC-CV)
        $sheet->setCellValue('BC5', 'STENTER (FINISHING BELAH)'); $sheet->mergeCells('BC5:CV5');
        $sheet->setCellValue('BC6', 'OPERATOR'); $sheet->mergeCells('BC6:BC7'); // Operator is single col
        
        $stenterGroups = [
            'TANGGAL' => 56, 'TEMPERATURE' => 59, 'SPEED' => 62, 'PADDER' => 65, 'RANGKA' => 68, 
            'OVERFEED A' => 71, 'OVERFEED B' => 74, 'FAN/BLOWER' => 77, 'DELIVERY SPEED' => 80, 
            'FOLDING SPEED' => 83, 'CHEMICAL 1' => 86, 'CHEMICAL 2' => 89, 'HASIL LEBAR' => 92, 
            'HASIL GRAMASI' => 95, 'SHRINKAGE' => 98
        ];
        foreach ($stenterGroups as $groupName => $startColIdx) {
            $sheet->setCellValueByColumnAndRow($startColIdx, 6, $groupName);
            $sheet->mergeCellsByColumnAndRow($startColIdx, 6, $startColIdx + 2, 6);
            $sheet->setCellValueByColumnAndRow($startColIdx, 7, 'PRESET');
            $sheet->setCellValueByColumnAndRow($startColIdx + 1, 7, 'DRYING');
            $sheet->setCellValueByColumnAndRow($startColIdx + 2, 7, 'FINISHING');
        }

        // ZONE 7: TUMBLER (CW-DE)
        $sheet->setCellValue('CW5', 'TUMBLER DRY (SHAKER/TUMBLER)'); $sheet->mergeCells('CW5:DE5');
        $headers7 = ['OPERATOR', 'TANGGAL', 'TEMPERATURE', 'STEAM INJECT', 'HOTWIND', 'COLDWIND', 'LEBAR', 'GRAMASI', 'SRINGKAGE (V x H)'];
        foreach($headers7 as $i => $h) { $sheet->setCellValueByColumnAndRow($i+101, 6, $h); $sheet->mergeCellsByColumnAndRow($i+101, 6, $i+101, 7); }

        // ZONE 8: FLEECE (DF-EF)
        $sheet->setCellValue('DF5', 'FLEECE'); $sheet->mergeCells('DF5:EF5');
        
        $sheet->setCellValue('DF6', 'RAISING'); $sheet->mergeCells('DF6:DO6');
        $fleeceRaising = ['OPERATOR', 'TANGGAL', 'STANDAR BULU', 'SPEED', 'CLOTH OUT', 'BEND PIN', 'STRIGHT PIN', 'RPM DRUM', 'LEBAR/GSM', 'DRUM BRUSH'];
        foreach($fleeceRaising as $i => $h) { $sheet->setCellValueByColumnAndRow($i+110, 7, $h); }

        $sheet->setCellValue('DP6', 'BRUSHING'); $sheet->mergeCells('DP6:DY6');
        $fleeceBrushing = ['OPERATOR', 'TANGGAL', 'STANDAR BULU', 'CLOTH SPEED', 'CLOTH OUT', 'LEFT BRUSH', 'RIGHT BRUSH', 'RPM DRUM', 'TENSION 1/2/3', 'LEBAR/GRAMASI'];
        foreach($fleeceBrushing as $i => $h) { $sheet->setCellValueByColumnAndRow($i+120, 7, $h); }

        $sheet->setCellValue('DZ6', 'SHEARING'); $sheet->mergeCells('DZ6:EF6');
        $fleeceShearing = ['OPERATOR', 'TANGGAL', 'SPEED', 'CLOTH OUT', 'EXPENDING', 'SHEAR', 'LEBAR/GRAMASI'];
        foreach($fleeceShearing as $i => $h) { $sheet->setCellValueByColumnAndRow($i+130, 7, $h); }

        // 3. COLOR BANDING
        $sheet->getStyle('A5:EF7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true]
        ]);
        
        $this->colorHeader($sheet, 'A5:J7', '1e293b'); // ORDER Navy
        $this->colorHeader($sheet, 'K5:S7', 'b91c1c'); // DYEING Red
        $this->colorHeader($sheet, 'T5:AD7', 'ea580c'); // RELAX Orange
        $this->colorHeader($sheet, 'AE5:AQ7', 'd97706'); // COMPACTOR Amber
        $this->colorHeader($sheet, 'AR5:BB7', '65a30d'); // HEAT SETTING Lime
        $this->colorHeader($sheet, 'BC5:CV7', '059669'); // STENTER Emerald
        $this->colorHeader($sheet, 'CW5:DE7', '0891b2'); // TUMBLER Cyan
        $this->colorHeader($sheet, 'DF5:EF7', '4f46e5'); // FLEECE Indigo

        // 4. ATURAN BORDER
        $sheet->getStyle("A5:EF{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'cbd5e1']]]
        ]);
        
        $sheet->getStyle("A8:EF{$lastRow}")->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '1e293b']],
            'alignment' => ['vertical' => 'center', 'horizontal' => 'center'] // Default center
        ]);

        // 5. ZEBRA STRIPING
        for($i=8; $i<=$lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(24);
            if($i % 2 == 0) $sheet->getStyle("A{$i}:EF{$i}")->getFill()->setFillType('solid')->getStartColor()->setRGB('f8fafc');
        }

        // 6. COLUMN SIZING
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5);
        for ($col = 2; $col <= 136; $col++) {
            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colString)->setAutoSize(true);
        }

        return [];
    }

    private function colorHeader($sheet, $range, $hex) {
        $sheet->getStyle($range)->getFill()->setFillType('solid')->getStartColor()->setRGB($hex);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('E8'); // Freeze No, Art, SAP, Cust
                $sheet->setShowGridlines(false);
                $sheet->setAutoFilter('A7:EF7');
                $sheet->getRowDimension(5)->setRowHeight(25); 
                $sheet->getRowDimension(6)->setRowHeight(25); 
                $sheet->getRowDimension(7)->setRowHeight(25); 
            },
        ];
    }
}
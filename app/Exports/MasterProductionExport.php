<?php
namespace App\Exports;

use App\Models\MarketingOrder;
use App\Models\ProductionActivity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MasterProductionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomStartCell
{
    protected $start;
    protected $end;
    protected $unit;
    protected $orders;
    private $rowNumber = 0;

    public function __construct($start, $end, $unit)
    {
        $this->start = $start;
        $this->end = $end;
        $this->unit = $unit;

        // Query data order marketing dengan semua jejak aktivitas produksinya
        $this->orders = MarketingOrder::query()
            ->with(['productionActivities.user', 'processingBy']) 
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->when($this->unit !== 'SEMUA', function($q) {
                $q->where('kelompok_kain', $this->unit);
            })
            ->latest()
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->orders;
    }

    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return [
            // BARIS 8: Judul Kolom Induk / Section Header
            'NO', 
            // MARKETING SECTION
            'NO ARTIKEL', 'SAP ID', 'TANGGAL ORDER', 'PELANGGAN', 'MKT', 
            'KEPERLUAN', 'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 
            'KELOMPOK KAIN', 'TARGET LEBAR', 'BELAH/BULAT', 
            'TARGET GRAMASI', 'WARNA', 'HANDFEEL', 'TREATMENT KHUSUS', 
            'ROLL TARGET', 'KG TARGET', 'KETERANGAN ARTIKEL',
            
            // R&D SECTION
            'RND G.GREIGE', 'RND MESIN RAJUT', 'RND TYPE MESIN',

            // KNITTING DIVISION
            'KNT OPERATOR', 'KNT MESIN NO', 'KNT TYPE MESIN', 'KNT GAUGE/INCH', 
            'KNT FEEDER', 'KNT JARUM', 'KNT LEBAR GREIGE', 'KNT GRAMASI GREIGE', 
            'KNT ROLL ACT', 'KNT KG ACT', 'KNT BENANG', 'KNT NOTE', 'KNT TANGGAL',

            // DYEING DIVISION
            'DYE OPERATOR', 'DYE MESIN', 'DYE ROLL ACT', 'DYE KG ACT',

            // RELAX DRYER
            'RLX OPERATOR', 'RLX MESIN', 'RLX ROLL ACT', 'RLX KG ACT',

            // COMPACTOR
            'CMP OPERATOR', 'CMP MESIN', 'CMP ROLL ACT', 'CMP KG ACT',

            // HEAT SETTING
            'HT OPERATOR', 'HT MESIN', 'HT ROLL ACT', 'HT KG ACT',

            // STENTER
            'STN OPERATOR', 'STN MESIN', 'STN ROLL ACT', 'STN KG ACT',

            // TUMBLER
            'TMB OPERATOR', 'TMB MESIN', 'TMB ROLL ACT', 'TMB KG ACT',

            // FLEECE
            'FLC OPERATOR', 'FLC MESIN', 'FLC ROLL ACT', 'FLC KG ACT',

            // PENGUJIAN / LAB
            'LAB OPERATOR', 'LAB TGL UJI', 'LAB LEBAR ACT', 'LAB GRAMASI ACT', 
            'LAB SHRINKAGE', 'LAB SPIRALITY', 'LAB SKEWNESS',

            // QE FINAL APPROVAL
            'QE OPERATOR', 'QE FABRIC NAME', 'QE LEBAR ACT', 'QE GRAMASI ACT', 
            'QE SHRINKAGE', 'QE NOTE'
        ];
    }

    public function map($order): array
    {
        $this->rowNumber++;

        // Helper untuk mencari activity berdasarkan division_name
        $activities = $order->productionActivities;
        
        $knit = $activities->where('division_name', 'knitting')->first();
        $dye = $activities->where('division_name', 'dyeing')->first();
        $rlx = $activities->where('division_name', 'relax-dryer')->first();
        $cmp = $activities->where('division_name', 'compactor')->first();
        $ht = $activities->where('division_name', 'heat-setting')->first();
        $stn = $activities->where('division_name', 'stenter')->first();
        $tmb = $activities->where('division_name', 'tumbler')->first();
        $flc = $activities->where('division_name', 'fleece')->first();
        $lab = $activities->where('division_name', 'pengujian')->first();
        $qe = $activities->where('division_name', 'qe')->first();

        // Parse technical data jika ada
        $kData = $knit ? (is_array($knit->technical_data) ? $knit->technical_data : json_decode($knit->technical_data, true)) : [];
        $lData = $lab ? (is_array($lab->technical_data) ? $lab->technical_data : json_decode($lab->technical_data, true)) : [];
        $qData = $qe ? (is_array($qe->technical_data) ? $qe->technical_data : json_decode($qe->technical_data, true)) : [];

        // Gabungkan benang knitting jika diinput
        $knitBenang = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($kData['benang_' . $i])) {
                $knitBenang[] = $kData['benang_' . $i] . ' (' . ($kData['benang_' . $i . '_percent'] ?? '100') . '%)';
            }
        }
        $knitBenangStr = implode(', ', $knitBenang) ?: '-';

        return [
            $this->rowNumber,
            // MARKETING SECTION
            $order->art_no ?? '-',
            $order->sap_no ?? '-',
            $order->created_at->format('d/m/Y H:i'),
            $order->pelanggan ?? '-',
            $order->mkt ?? '-',
            $order->keperluan ?? '-',
            $order->konstruksi_greige ?? '-',
            $order->material ?? '-',
            $order->benang ?? '-',
            $order->kelompok_kain ?? '-',
            $order->target_lebar ?? '-',
            $order->belah_bulat ?? '-',
            $order->target_gramasi ?? '-',
            $order->warna ?? '-',
            $order->handfeel ?? '-',
            $order->treatment_khusus ?? '-',
            $order->roll_target ?? 0,
            $order->kg_target ?? 0,
            $order->keterangan_artikel ?? '-',

            // R&D SECTION
            $order->rnd_gramasi_greige ?? '-',
            $order->rnd_mesin_rajut ?? '-',
            $order->rnd_jenis_mesin_rajut ?? '-',

            // KNITTING
            $kData['nama_input'] ?? ($knit->user->name ?? '-'),
            $knit->machine_no ?? '-',
            $kData['type_mesin'] ?? '-',
            $kData['gauge_inch'] ?? '-',
            $kData['jml_feeder'] ?? '-',
            $kData['jml_jarum'] ?? '-',
            $kData['lebar'] ?? '-',
            $kData['gramasi'] ?? '-',
            $knit->roll ?? '-',
            $knit->kg ?? '-',
            $knitBenangStr,
            $kData['note'] ?? '-',
            $knit ? $knit->created_at->format('d/m/Y H:i') : '-',

            // DYEING
            $dye->user->name ?? '-',
            $dye->machine_no ?? '-',
            $dye->roll ?? '-',
            $dye->kg ?? '-',

            // RELAX DRYER
            $rlx->user->name ?? '-',
            $rlx->machine_no ?? '-',
            $rlx->roll ?? '-',
            $rlx->kg ?? '-',

            // COMPACTOR
            $cmp->user->name ?? '-',
            $cmp->machine_no ?? '-',
            $cmp->roll ?? '-',
            $cmp->kg ?? '-',

            // HEAT SETTING
            $ht->user->name ?? '-',
            $ht->machine_no ?? '-',
            $ht->roll ?? '-',
            $ht->kg ?? '-',

            // STENTER
            $stn->user->name ?? '-',
            $stn->machine_no ?? '-',
            $stn->roll ?? '-',
            $stn->kg ?? '-',

            // TUMBLER
            $tmb->user->name ?? '-',
            $tmb->machine_no ?? '-',
            $tmb->roll ?? '-',
            $tmb->kg ?? '-',

            // FLEECE
            $flc->user->name ?? '-',
            $flc->machine_no ?? '-',
            $flc->roll ?? '-',
            $flc->kg ?? '-',

            // LAB / PENGUJIAN
            $lData['operator'] ?? ($lab->user->name ?? '-'),
            $lab ? $lab->created_at->format('d/m/Y') : '-',
            $lData['lebar'] ?? '-',
            $lData['gramasi'] ?? '-',
            $lData['shrinkage'] ?? '-',
            $lData['spirality'] ?? '-',
            $lData['skewness'] ?? '-',

            // QE FINAL APPROVAL
            $qData['operator'] ?? ($qe->user->name ?? '-'),
            $qData['fabric_name'] ?? '-',
            $qData['lebar'] ?? '-',
            $qData['gramasi'] ?? '-',
            $qData['shrinkage'] ?? '-',
            $qData['note'] ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. HEADER BRANDING DUNIATEX
        $sheet->setCellValue('A1', 'DUNIATEX RND MASTER TRACEABILITY PIPELINE REPORT');
        $sheet->mergeCells('A1:CE1');
        
        $periodeLabel = date('d F Y', strtotime($this->start)) . ' s/d ' . date('d F Y', strtotime($this->end));
        $sheet->setCellValue('A2', 'Periode: ' . $periodeLabel . ' | Unit Kelompok Kain: ' . $this->unit . ' | Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:CE2');
        
        // 2. KARTU KPI RINGKASAN
        // TOTAL ORDERS
        $sheet->setCellValue('B4', 'TOTAL ORDERS PIPELINE');
        $sheet->mergeCells('B4:C4');
        $sheet->setCellValue('B5', count($this->orders) . ' Orders');
        $sheet->mergeCells('B5:C5');
        
        // TOTAL TARGET VOLUME
        $sheet->setCellValue('E4', 'TOTAL TARGET (KG)');
        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('E5', number_format($this->orders->sum('kg_target'), 2) . ' KG');
        $sheet->mergeCells('E5:F5');
        
        // STYLING BRANDS & KPI
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'ED1C24']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
        ]);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '4B5563']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER]
        ]);

        foreach (['B4:C5', 'E4:F5'] as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
                'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'FCA5A5']]]
            ]);
        }
        foreach (['B4', 'E4'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray(['font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'DC2626']]]);
        }
        foreach (['B5', 'E5'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray(['font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '111827']]]);
        }

        // 3. COLOR-CODED SECTION BANDS (Baris 7 untuk pengelompokan divisi)
        // Kita merge sel di baris 7 lalu beri warna yang harmonis untuk membedakan asal data
        
        // 3.1 Marketing Section (A7:T7) - Kuning Lembut / Soft Gold
        $sheet->setCellValue('A7', 'MARKETING & ORDER DATA');
        $sheet->mergeCells('A7:T7');
        $this->styleSectionBand($sheet, 'A7:T7', 'FEF08A', 'CA8A04'); // Yellow 200 / Yellow 600

        // 3.2 R&D Specifications (U7:W7) - Jingga Lembut / Soft Orange
        $sheet->setCellValue('U7', 'R&D SPECIFICATIONS');
        $sheet->mergeCells('U7:W7');
        $this->styleSectionBand($sheet, 'U7:W7', 'FFEDD5', 'EA580C'); // Orange 100 / Orange 600

        // 3.3 Knitting Division (X7:AL7) - Biru Lembut / Soft Blue
        $sheet->setCellValue('X7', 'KNITTING DIVISION (RAJUT)');
        $sheet->mergeCells('X7:AL7');
        $this->styleSectionBand($sheet, 'X7:AL7', 'DBEAFE', '2563EB'); // Blue 100 / Blue 600

        // 3.4 Dyeing & Finishing (AM7:BN7) - Hijau/Cyan Lembut / Soft Emerald
        $sheet->setCellValue('AM7', 'DYEING & FINISHING WORKFLOWS');
        $sheet->mergeCells('AM7:BN7');
        $this->styleSectionBand($sheet, 'AM7:BN7', 'D1FAE5', '059669'); // Emerald 100 / Emerald 600

        // 3.5 Lab / Pengujian (BO7:BW7) - Merah Lembut / Soft Red
        $sheet->setCellValue('BO7', 'LAB / PENGUJIAN FISIK');
        $sheet->mergeCells('BO7:BW7');
        $this->styleSectionBand($sheet, 'BO7:BW7', 'FEE2E2', 'DC2626'); // Red 100 / Red 600

        // 3.6 QE Quality Control (BX7:CE7) - Ungu Lembut / Soft Purple
        $sheet->setCellValue('BX7', 'QE QUALITY CONTROL & APPROVED');
        $sheet->mergeCells('BX7:CE7');
        $this->styleSectionBand($sheet, 'BX7:CE7', 'F3E8FF', '7C3AED'); // Purple 100 / Purple 600

        // 4. JUDUL KOLOM (Baris 8)
        $headerRange = 'A8:CE8';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '374151']], // Charcoal Gray
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '4B5563']]]
        ]);

        // Row Heights
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(25);
        $sheet->getRowDimension(7)->setRowHeight(25);
        $sheet->getRowDimension(8)->setRowHeight(28);

        // 5. STYLING DATA ROWS & ALIGNMENTS
        $totalRows = count($this->orders);
        $lastRow = 8 + $totalRows;

        for ($row = 9; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
            $bgColor = ($row % 2 === 0) ? 'F9FAFB' : 'FFFFFF';

            $sheet->getStyle("A{$row}:CE{$row}")->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]]
            ]);

            // Posisi Tengah (Center Alignment) untuk ID, Angka, Tanggal, & Unit
            foreach (['A', 'B', 'C', 'D', 'K', 'L', 'M', 'N', 'P', 'R', 'S', 'U', 'V', 'W'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            // Posisi tengah untuk kolom input produksi (timbangan & volume) agar rapi
            // Mulai dari kolom Y sampai CE
            $centerCols = [
                'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AJ', 'AL', // Knitting
                'AN', 'AO', 'AP', // Dyeing
                'AR', 'AS', 'AT', // Rlx
                'AV', 'AW', 'AX', // Cmp
                'AZ', 'BA', 'BB', // Ht
                'BD', 'BE', 'BF', // Stn
                'BH', 'BI', 'BJ', // Tmb
                'BL', 'BM', 'BN', // Flc
                'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', // Lab
                'BY', 'BZ', 'CA', 'CB', 'CC', 'CD' // QE
            ];
            foreach ($centerCols as $col) {
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }
    }

    private function styleSectionBand($sheet, $range, $bgHex, $textHex)
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => $textHex],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $bgHex],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => ['rgb' => $textHex],
                ]
            ]
        ]);
    }
}

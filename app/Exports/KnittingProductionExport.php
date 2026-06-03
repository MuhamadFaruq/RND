<?php
namespace App\Exports;

use App\Models\ProductionActivity;
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

class KnittingProductionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomStartCell, WithEvents, WithTitle
{
    protected $start;
    protected $end;
    protected $unit;
    protected $operator;
    protected $activities;
    protected $sheetTitle;
    private $rowNumber = 0;

    public function __construct($start, $end, $unit, $operator = 'SEMUA', $sheetTitle = 'Knitting Production')
    {
        $this->start = $start;
        $this->end = $end;
        $this->unit = $unit;
        $this->operator = $operator;
        $this->sheetTitle = $sheetTitle;

        $this->activities = ProductionActivity::query()
            ->with(['marketingOrder', 'user']) 
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->where('division_name', 'knitting')
            ->when($this->unit !== 'SEMUA', fn($q) => $q->whereHas('marketingOrder', fn($sq) => $sq->where('kelompok_kain', $this->unit)))
            ->when($this->operator !== 'SEMUA', fn($q) => $q->where('operator_id', $this->operator))
            ->latest()
            ->get();
    }

    public function collection()
    {
        return $this->activities;
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
        return []; // Custom built in styles()
    }

    public function map($activity): array
    {
        $this->rowNumber++;
        $tech = is_string($activity->technical_data) ? json_decode($activity->technical_data, true) : ($activity->technical_data ?? []);

        return [
            $this->rowNumber,
            
            // ORDER / IDENTITAS (B-D)
            $activity->marketingOrder->art_no ?? '-',
            $activity->marketingOrder->sap_no ?? '-',
            $activity->marketingOrder->pelanggan ?? '-',

            // R&D (E-G)
            $activity->marketingOrder->rnd_gramasi_greige ?? '-',
            $activity->marketingOrder->rnd_mesin_rajut ?? '-',
            $activity->marketingOrder->rnd_jenis_mesin_rajut ?? '-',
            
            // KNITTING (H-N)
            $activity->operator_name ?? ($activity->user->name ?? '-'),
            $activity->created_at->format('d/m/Y H:i'),
            $tech['no_mesin'] ?? '-',
            $tech['type_mesin'] ?? '-',
            $tech['gauge_inch'] ?? '-',
            $tech['jml_feeder'] ?? '-',
            $tech['jml_jarum'] ?? '-',
            
            // HASIL GREIGE (O-R)
            $tech['lebar'] ?? '-',
            $tech['gramasi'] ?? '-',
            $activity->kg ?? 0,
            $activity->roll ?? 0,
            
            // PENGGUNAAN BENANG (S-AH)
            $tech['benang_1'] ?? '-', $tech['benang_1_lot'] ?? '-', $tech['benang_1_percent'] ?? '-', $tech['yl_1'] ?? '-',
            $tech['benang_2'] ?? '-', $tech['benang_2_lot'] ?? '-', $tech['benang_2_percent'] ?? '-', $tech['yl_2'] ?? '-',
            $tech['benang_3'] ?? '-', $tech['benang_3_lot'] ?? '-', $tech['benang_3_percent'] ?? '-', $tech['yl_3'] ?? '-',
            $tech['benang_4'] ?? '-', $tech['benang_4_lot'] ?? '-', $tech['benang_4_percent'] ?? '-', $tech['yl_4'] ?? '-',
            
            // LAINNYA (AI-AJ)
            $tech['note'] ?? '-',
            $tech['produksi_per_day'] ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 7 + count($this->activities); 
        
        // 1. BRANDING HEADER
        $sheet->setCellValue('A1', 'PT DELTA DUNIA TEKSTILE 2 - HORIZONTAL TRACEABILITY REPORT');
        $sheet->setCellValue('A2', 'SECTION: KNITTING DIVISION WORKFLOWS');
        $periodeLabel = date('d M Y', strtotime($this->start)) . ' - ' . date('d M Y', strtotime($this->end));
        $sheet->setCellValue('A3', 'PERIODE: ' . $periodeLabel . ' | KELOMPOK: ' . $this->unit . ' | OPERATOR: ' . strtoupper($this->operator));
        $sheet->setCellValue('A4', 'WAKTU CETAK: ' . now()->format('d/m/Y H:i:s'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('ED1C24')); 
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1e293b'));
        $sheet->getStyle('A3:A4')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));

        // 2. BUILD 3-LEVEL HEADER (Row 5, 6, 7)
        // MAIN ZONE
        $sheet->setCellValue('A5', 'NO'); $sheet->mergeCells('A5:A7');
        $sheet->setCellValue('B5', 'IDENTITAS PESANAN'); $sheet->mergeCells('B5:D5');
        $sheet->setCellValue('E5', 'R&D'); $sheet->mergeCells('E5:G5');
        $sheet->setCellValue('H5', 'MESIN KNITTING'); $sheet->mergeCells('H5:N5');
        $sheet->setCellValue('O5', 'HASIL GREIGE'); $sheet->mergeCells('O5:R5');
        $sheet->setCellValue('S5', 'PENGGUNAAN BENANG'); $sheet->mergeCells('S5:AH5');
        $sheet->setCellValue('AI5', 'LAINNYA'); $sheet->mergeCells('AI5:AJ5');

        // IDENTITAS
        $headersId = ['NO. ARTIKEL', 'SAP ID', 'PELANGGAN'];
        foreach($headersId as $i => $h) { $sheet->setCellValueByColumnAndRow($i+2, 6, $h); $sheet->mergeCellsByColumnAndRow($i+2, 6, $i+2, 7); }

        // R&D
        $headersRD = ['GRAMASI GREIGE', 'MESIN RAJUT', 'JENIS MESIN RAJUT'];
        foreach($headersRD as $i => $h) { $sheet->setCellValueByColumnAndRow($i+5, 6, $h); $sheet->mergeCellsByColumnAndRow($i+5, 6, $i+5, 7); }

        // KNITTING
        $headersKn = ['OPERATOR', 'TANGGAL', 'NO MESIN', 'TYPE MESIN', 'GAUGE/INCH', 'JML FEEDER', 'JML JARUM'];
        foreach($headersKn as $i => $h) { $sheet->setCellValueByColumnAndRow($i+8, 6, $h); $sheet->mergeCellsByColumnAndRow($i+8, 6, $i+8, 7); }

        // HASIL GREIGE
        $headersHg = ['LEBAR', 'GRAMASI', 'KG', 'ROLL'];
        foreach($headersHg as $i => $h) { $sheet->setCellValueByColumnAndRow($i+15, 6, $h); $sheet->mergeCellsByColumnAndRow($i+15, 6, $i+15, 7); }

        // PENGGUNAAN BENANG (Sub Groups)
        $sheet->setCellValue('S6', 'BENANG 1'); $sheet->mergeCells('S6:V6');
        $sheet->setCellValue('W6', 'BENANG 2'); $sheet->mergeCells('W6:Z6');
        $sheet->setCellValue('AA6', 'BENANG 3'); $sheet->mergeCells('AA6:AD6');
        $sheet->setCellValue('AE6', 'BENANG 4'); $sheet->mergeCells('AE6:AH6');

        $benangCols = ['S','W','AA','AE'];
        foreach ($benangCols as $startCol) {
            $idx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startCol);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx) . '7', 'JENIS');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx+1) . '7', 'LOT');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx+2) . '7', '%');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx+3) . '7', 'YL');
        }

        // LAINNYA
        $headersLn = ['NOTE', 'PROD / DAY(KG)'];
        foreach($headersLn as $i => $h) { $sheet->setCellValueByColumnAndRow($i+35, 6, $h); $sheet->mergeCellsByColumnAndRow($i+35, 6, $i+35, 7); }

        // 3. COLOR BANDING
        $sheet->getStyle('A5:AJ7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true]
        ]);
        
        $this->colorHeader($sheet, 'A5:A7', '0f172a'); // NO
        $this->colorHeader($sheet, 'B5:D7', '1e293b'); // ID Navy
        $this->colorHeader($sheet, 'E5:G7', 'f59e0b'); // R&D Amber
        $this->colorHeader($sheet, 'H5:N7', '2563eb'); // KNITTING Blue
        $this->colorHeader($sheet, 'O5:R7', 'b91c1c'); // HASIL GREIGE Red
        $this->colorHeader($sheet, 'S5:AH7', '475569'); // PENGGUNAAN BENANG Slate
        $this->colorHeader($sheet, 'AI5:AJ7', '047857'); // NOTE Emerald

        // 4. ATURAN BORDER
        $sheet->getStyle("A5:AJ{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'cbd5e1']]]
        ]);
        
        $sheet->getStyle("A8:AJ{$lastRow}")->applyFromArray([
            'font' => ['size' => 9, 'color' => ['rgb' => '1e293b']],
            'alignment' => ['vertical' => 'center', 'horizontal' => 'center'] // Default center
        ]);

        // 5. ALIGNMENTS KHUSUS
        $leftCols = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'AI'];
        foreach($leftCols as $col) $sheet->getStyle("{$col}8:{$col}{$lastRow}")->getAlignment()->setHorizontal('left')->setIndent(1);

        $rightCols = ['Q', 'R', 'AJ'];
        foreach($rightCols as $col) {
            $sheet->getStyle("{$col}8:{$col}{$lastRow}")->getAlignment()->setHorizontal('right')->setIndent(1);
            $sheet->getStyle("{$col}8:{$col}{$lastRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
        }

        $sheet->getStyle("Q8:Q{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00'); // KG
        $sheet->getStyle("R8:R{$lastRow}")->getNumberFormat()->setFormatCode('#,##0'); // Roll

        // 6. ZEBRA STRIPING
        for($i=8; $i<=$lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(24);
            if($i % 2 == 0) $sheet->getStyle("A{$i}:AJ{$i}")->getFill()->setFillType('solid')->getStartColor()->setRGB('f8fafc');
        }

        // 7. COLUMN SIZING
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5);
        for ($col = 2; $col <= 36; $col++) {
            $colString = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colString)->setAutoSize(true);
        }

        // 8. TOTAL SUMMARY ROW
        if (count($this->activities) > 0) {
            $footerRow = $lastRow + 1;
            $sheet->setCellValue("A{$footerRow}", 'TOTAL AKUMULASI PRODUKSI');
            $sheet->mergeCells("A{$footerRow}:P{$footerRow}");
            
            $sheet->setCellValue("Q{$footerRow}", "=SUM(Q8:Q{$lastRow})");
            $sheet->setCellValue("R{$footerRow}", "=SUM(R8:R{$lastRow})");
            
            $sheet->getStyle("A{$footerRow}:AJ{$footerRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0f172a']], 
                'alignment' => ['vertical' => 'center']
            ]);
            $sheet->getStyle("Q{$footerRow}:R{$footerRow}")->getAlignment()->setHorizontal('right');
            $sheet->getStyle("Q{$footerRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("R{$footerRow}")->getNumberFormat()->setFormatCode('#,##0'); 
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
                $sheet->freezePane('E8'); // Freeze No, Art, SAP, Pelanggan
                $sheet->setShowGridlines(false);
                $sheet->setAutoFilter('A7:AJ7');
                $sheet->getRowDimension(5)->setRowHeight(25); 
                $sheet->getRowDimension(6)->setRowHeight(25); 
                $sheet->getRowDimension(7)->setRowHeight(25); 
            },
        ];
    }
}
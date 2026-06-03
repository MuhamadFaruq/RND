<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class MarketingOrdersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents, WithTitle
{
    protected $orders;
    protected $labelPeriode;
    private $rowNumber = 0;
    protected $sheetTitle = 'Marketing Order';

    public function __construct($orders, $labelPeriode, $sheetTitle = 'Marketing Order') {
        $this->orders = $orders;
        $this->labelPeriode = $labelPeriode;
        $this->sheetTitle = $sheetTitle;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function collection() {
        return $this->orders;
    }

    public function startCell(): string {
        return 'A8';
    }

    public function headings(): array {
        return [
            'NO', 'SAP', 'ART', 'TANGGAL', 'PELANGGAN', 'MKT', 'KEPERLUAN', 
            'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 'KELOMPOK KAIN', 
            'TARGET LEBAR', 'BELAH/BULAT', 'TARGET GRAMASI', 'WARNA', 
            'HANDFEEL', 'TREATMENT KHUSUS', 'ROLL', 'KG', 'KETERANGAN ARTIKEL'
        ];
    }

    public function map($order): array {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $order->sap_no ?: '-',
            $order->art_no,
            $order->tanggal ? Carbon::parse($order->tanggal)->format('d/m/Y') : '-',
            $order->pelanggan,
            $order->mkt,
            $order->keperluan,
            $order->konstruksi_greige ?: '-',
            $order->material ?: '-',
            $order->benang ?: '-',
            $order->kelompok_kain ?: '-',
            $order->target_lebar ?: '-',
            $order->belah_bulat ?: '-',
            $order->target_gramasi ?: '-',
            $order->warna ?: '-',
            $order->handfeel ?: '-',
            $order->treatment_khusus ?: '-',
            $order->roll_target,
            $order->kg_target,
            $order->keterangan_artikel ?: '-',
        ];
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 8 + count($this->orders);
                
                // 1. PENGATURAN KANVAS (Bersih & Dashboard Look)
                $sheet->setShowGridlines(false);
                $sheet->freezePane('A9');

                // 2. HEADER BRANDING RESMI (Row 1-5)
                $sheet->setCellValue('A1', 'PT DELTA DUNIA TEKSTILE 2');
                $sheet->setCellValue('A2', 'LAPORAN MONITORING ORDER R&D');
                $sheet->mergeCells('A1:T1'); $sheet->mergeCells('A2:T2');
                
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(24)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('ED1C24'));
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1e293b'));

                $sheet->setCellValue('A4', 'PERIODE LAPORAN: ' . strtoupper($this->labelPeriode));
                $sheet->setCellValue('A5', 'DICETAK PADA: ' . now()->format('d/m/Y H:i:s'));
                $sheet->getStyle('A4:A5')->getFont()->setBold(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));

                // 3. SECTION BANDS (Grouping Data Secara Visual)
                // Kelompok Identitas (A-G)
                $sheet->setCellValue('A7', '◈ IDENTITAS PESANAN'); $sheet->mergeCells('A7:G7');
                $this->applyBandStyle($sheet, 'A7:G7', '1e293b'); // Navy

                // Kelompok Teknis (H-Q)
                $sheet->setCellValue('H7', '◈ SPESIFIKASI TEKNIS KAIN'); $sheet->mergeCells('H7:Q7');
                $this->applyBandStyle($sheet, 'H7:Q7', '334155'); // Slate

                // Kelompok Target (R-T)
                $sheet->setCellValue('R7', '◈ TARGET OUTPUT'); $sheet->mergeCells('R7:T7');
                $this->applyBandStyle($sheet, 'R7:T7', 'ED1C24'); // Red

                // 4. STYLE HEADER TABEL (Baris 8)
                $sheet->getStyle('A8:T8')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0f172a']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
                ]);
                $sheet->setAutoFilter('A8:T8');
                $sheet->getRowDimension(8)->setRowHeight(25);

                // 5. STANDARISASI PERATAAN (ALIGNMENT) DATA
                $sheet->getStyle("A9:T{$lastRow}")->applyFromArray([
                    'alignment' => ['vertical' => 'center'],
                    'borders' => ['bottom' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'f1f5f9']]]
                ]);

                // Tengah (Identitas Singkat & Kode)
                $centerCols = ['A', 'B', 'C', 'D', 'K', 'L', 'M', 'N'];
                foreach($centerCols as $col) $sheet->getStyle("{$col}9:{$col}{$lastRow}")->getAlignment()->setHorizontal('center');

                // Kiri (Teks & Deskripsi)
                $leftCols = ['E', 'F', 'G', 'H', 'I', 'J', 'O', 'P', 'Q', 'T'];
                foreach($leftCols as $col) {
                    $sheet->getStyle("{$col}9:{$col}{$lastRow}")->getAlignment()->setHorizontal('left');
                    $sheet->getStyle("{$col}9:{$col}{$lastRow}")->getAlignment()->setIndent(1);
                }

                // Kanan (Angka Volume)
                $rightCols = ['R', 'S'];
                foreach($rightCols as $col) {
                    $sheet->getStyle("{$col}9:{$col}{$lastRow}")->getAlignment()->setHorizontal('right');
                    $sheet->getStyle("{$col}9:{$col}{$lastRow}")->getAlignment()->setIndent(1);
                    $sheet->getStyle("{$col}9:{$col}{$lastRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('000000'));
                }

                // Zebra Striping (Baris Selang-seling)
                for($i=9; $i<=$lastRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(22);
                    if($i % 2 == 0) $sheet->getStyle("A{$i}:T{$i}")->getFill()->setFillType('solid')->getStartColor()->setRGB('f8fafc');
                }

                // 6. FOOTER TOTAL AGGREGATE
                $footerRow = $lastRow + 1;
                $sheet->setCellValue("A{$footerRow}", 'TOTAL KESELURUHAN (PIPELINE)');
                $sheet->mergeCells("A{$footerRow}:Q{$footerRow}");
                $sheet->setCellValue("R{$footerRow}", "=SUM(R9:R{$lastRow})");
                $sheet->setCellValue("S{$footerRow}", "=SUM(S9:S{$lastRow})");
                
                $sheet->getStyle("A{$footerRow}:T{$footerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0f172a']],
                    'alignment' => ['vertical' => 'center']
                ]);
                $sheet->getStyle("R{$footerRow}:S{$footerRow}")->getAlignment()->setHorizontal('right');
                $sheet->getStyle("R{$footerRow}:S{$footerRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFF')); // Tetap putih agar kontras dengan background Navy

                // 7. OPTIMASI LEBAR KOLOM
                $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5); // No dibuat sempit
                foreach(range('B','T') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

                // Meta Footer
                $metaRow = $footerRow + 2;
                $sheet->setCellValue("A{$metaRow}", 'DOKUMEN RESMI SISTEM RND DUNIATEX | GENERATED BY: ' . auth()->user()->name);
                $sheet->mergeCells("A{$metaRow}:T{$metaRow}");
                $sheet->getStyle("A{$metaRow}")->getFont()->setItalic(true)->setSize(8)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('94a3b8'));
            },
        ];
    }

    public function styles(Worksheet $sheet) {
        return [];
    }

    private function applyBandStyle($sheet, $range, $bgColor) {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ]);
    }
}
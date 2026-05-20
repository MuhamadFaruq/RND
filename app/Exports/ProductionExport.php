<?php
namespace App\Exports;

use App\Models\ProductionActivity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomStartCell
{
    protected $start;
    protected $end;
    protected $mode;
    protected $unit;
    protected $operator;
    protected $activities;
    private $rowNumber = 0;

    public function __construct($start, $end, $mode, $unit, $operator = 'SEMUA')
    {
        $this->start = $start;
        $this->end = $end;
        $this->mode = $mode;
        $this->unit = $unit;
        $this->operator = $operator;

        // Query data dan simpan dalam collection
        $this->activities = ProductionActivity::query()
            ->with(['marketingOrder', 'user']) 
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->when($this->mode === 'rajut', function($q) {
                $q->where('division_name', 'knitting');
            })
            ->when($this->mode === 'warna', function($q) {
                $q->whereIn('division_name', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece']);
            })
            ->when($this->unit !== 'SEMUA', function($q) {
                $q->whereHas('marketingOrder', fn($sq) => $sq->where('kelompok_kain', $this->unit));
            })
            ->when($this->operator !== 'SEMUA', function($q) {
                $q->where('operator_id', $this->operator);
            })
            ->when($this->operator === 'SEMUA', function($q) {
                $q->whereHas('user', function($u) {
                    $u->where('name', 'NOT LIKE', '%admin%')
                      ->where('name', 'NOT LIKE', '%marketing%');
                });
            })
            ->latest()
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->activities;
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
            'NO', 
            'NO ARTIKEL', 'LEGACY SAP ID', 'TANGGAL & JAM', 'OPERATOR', 'PELANGGAN', 'MKT', 
            'KEPERLUAN', 'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 
            'KELOMPOK KAIN', 'TARGET LEBAR', 'BELAH/BULAT', 
            'TARGET GRAMASI', 'WARNA', 'HANDFEEL', 'TREATMENT KHUSUS', 
            'ROLL', 'KG', 'KETERANGAN ARTIKEL'
        ];
    }

    public function map($activity): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $activity->marketingOrder->art_no ?? '-',
            $activity->marketingOrder->sap_no ?? '-',
            $activity->created_at->format('d/m/Y H:i'),
            $activity->user->name ?? '-', 
            $activity->marketingOrder->pelanggan ?? '-', 
            $activity->marketingOrder->mkt ?? '-', 
            $activity->marketingOrder->keperluan ?? '-',
            $activity->marketingOrder->konstruksi_greige ?? '-',
            $activity->marketingOrder->material ?? '-',
            $activity->marketingOrder->benang ?? '-', 
            $activity->marketingOrder->kelompok_kain ?? '-',
            $activity->marketingOrder->target_lebar ?? '-',
            $activity->marketingOrder->belah_bulat ?? '-',
            $activity->marketingOrder->target_gramasi ?? '-',
            $activity->marketingOrder->warna ?? '-',
            $activity->marketingOrder->handfeel ?? '-',
            $activity->marketingOrder->treatment_khusus ?? '-',
            $activity->roll,
            $activity->kg,
            $activity->marketingOrder->keterangan_artikel ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1. TULIS TITLE & SUBTITLE
        $sheet->setCellValue('A1', 'DUNIATEX RND PRODUCTION REPORT: ' . strtoupper($this->mode === 'rajut' ? 'KNITTING' : ($this->mode === 'warna' ? 'DYEING & FINISHING' : 'ALL SECTIONS')));
        $sheet->mergeCells('A1:U1');
        
        $periodeLabel = date('d F Y', strtotime($this->start)) . ' s/d ' . date('d F Y', strtotime($this->end));
        $sheet->setCellValue('A2', 'Periode: ' . $periodeLabel . ' | Unit: ' . $this->unit . ' | Generated: ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A2:U2');
        
        // 2. KARTU KPI
        // KPI 1: TOTAL INPUT
        $sheet->setCellValue('B4', 'TOTAL INPUT DATA');
        $sheet->mergeCells('B4:C4');
        $sheet->setCellValue('B5', count($this->activities) . ' Data');
        $sheet->mergeCells('B5:C5');
        
        // KPI 2: TOTAL PRODUCTION (KG)
        $sheet->setCellValue('E4', 'TOTAL PRODUCTION (KG)');
        $sheet->mergeCells('E4:F4');
        $sheet->setCellValue('E5', number_format($this->activities->sum('kg'), 2) . ' KG');
        $sheet->mergeCells('E5:F5');
        
        // KPI 3: TOTAL ROLL
        $sheet->setCellValue('H4', 'TOTAL PRODUCTION (ROLL)');
        $sheet->mergeCells('H4:I4');
        $sheet->setCellValue('H5', number_format($this->activities->sum('roll'), 0) . ' Roll');
        $sheet->mergeCells('H5:I5');
        
        // 3. STYLING TITLE & SUBTITLE
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'ED1C24'], // Duniatex Red
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);
        
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'italic' => true,
                'size' => 10,
                'color' => ['rgb' => '4B5563'], // Gray 600
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);
        
        // 4. STYLING KPI CARDS
        $kpiCols = ['B4:C5', 'E4:F5', 'H4:I5'];
        $kpiBg = 'FEF2F2'; // Sangat soft red
        $kpiBorderColor = 'FCA5A5'; // Soft red border
        
        foreach ($kpiCols as $range) {
            $sheet->getStyle($range)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $kpiBg],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => $kpiBorderColor],
                    ],
                ],
            ]);
        }
        
        // Style untuk Label KPI (Row 4)
        foreach (['B4', 'E4', 'H4'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 9,
                    'color' => ['rgb' => 'DC2626'], // Deep Red
                ]
            ]);
        }
        
        // Style untuk Value KPI (Row 5)
        foreach (['B5', 'E5', 'H5'] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 13,
                    'color' => ['rgb' => '111827'], // Gray 900
                ]
            ]);
        }
        
        // 5. STYLING TABLE HEADERS (Row 8)
        $headerRange = 'A8:U8';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ED1C24'], // Duniatex Red
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(35);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(25);
        $sheet->getRowDimension(8)->setRowHeight(30);
        
        // 6. STYLE DATA ROWS & ALIGNMENTS
        $totalRows = count($this->activities);
        $lastRow = 8 + $totalRows;
        
        for ($row = 9; $row <= $lastRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
            
            // Zebra striping
            $bgColor = ($row % 2 === 0) ? 'F9FAFB' : 'FFFFFF';
            
            $sheet->getStyle("A{$row}:U{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor],
                ],
                'font' => [
                    'size' => 9,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'], // Very light gray border
                    ]
                ]
            ]);
            
            // Alignments
            // Center alignments for short/numeric codes and dates
            foreach (['A', 'C', 'D', 'L', 'M', 'N', 'O', 'S', 'T'] as $col) {
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
            
            // Number formatting for KG and Roll
            $sheet->getStyle("S{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("T{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        }
        
        // 7. TOTALS ROW AT THE BOTTOM
        $totRow = $lastRow + 1;
        $sheet->getRowDimension($totRow)->setRowHeight(25);
        $sheet->setCellValue("A{$totRow}", 'TOTAL SUMMARY');
        $sheet->mergeCells("A{$totRow}:R{$totRow}");
        
        $sheet->setCellValue("S{$totRow}", "=SUM(S9:S{$lastRow})");
        $sheet->setCellValue("T{$totRow}", "=SUM(T9:T{$lastRow})");
        
        $sheet->getStyle("A{$totRow}:U{$totRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['rgb' => '000000'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '111827'],
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                    'color' => ['rgb' => '111827'],
                ]
            ]
        ]);
        
        $sheet->getStyle("S{$totRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("T{$totRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle("S{$totRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("T{$totRow}")->getNumberFormat()->setFormatCode('#,##0.00');
    }
}
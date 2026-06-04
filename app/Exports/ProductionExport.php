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

class ProductionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping, WithCustomStartCell, WithEvents, WithTitle
{
    protected $start;
    protected $end;
    protected $mode;
    protected $unit;
    protected $operator;
    protected $activities;
    protected $sheetTitle;
    private $rowNumber = 0;

    public function __construct($start, $end, $mode, $unit, $operator = 'SEMUA', $sheetTitle = 'Production Data')
    {
        $this->start = $start;
        $this->end = $end;
        $this->mode = $mode;
        $this->unit = $unit;
        $this->operator = $operator;
        $this->sheetTitle = $sheetTitle;

        $this->activities = ProductionActivity::query()
            ->with(['marketingOrder', 'user']) 
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->when($this->mode === 'rajut', fn($q) => $q->where('division_name', 'knitting'))
            ->when($this->mode === 'warna', fn($q) => $q->whereIn('division_name', ['dyeing', 'relax-dryer', 'compactor', 'heat-setting', 'stenter', 'tumbler', 'fleece']))
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
        return 'A6';
    }

    public function headings(): array
    {
        $modeText = strtoupper($this->mode === 'rajut' ? 'DIVISI KNITTING (RAJUT)' : ($this->mode === 'warna' ? 'DIVISI DYEING & FINISHING' : 'MASTER PIPELINE PRODUKSI'));
        $periodeLabel = date('d M Y', strtotime($this->start)) . ' - ' . date('d M Y', strtotime($this->end));

        return [
            ['PT DELTA DUNIA TEKSTILE 2'],
            ['LAPORAN PRODUKSI HARIAN - ' . $modeText],
            ['PERIODE: ' . $periodeLabel . ' | KELOMPOK: ' . $this->unit . ' | OPERATOR: ' . strtoupper($this->operator)],
            ['WAKTU CETAK: ' . now()->format('d/m/Y H:i:s')],
            [''], // Spacer
            [
                'NO', 'NO. ARTIKEL', 'SAP ID', 'WAKTU PRODUKSI', 'TAHAPAN PROSES', 'NAMA OPERATOR', 
                'PELANGGAN', 'SALES (MKT)', 'KEPERLUAN', 
                'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 'KELOMPOK KAIN', 
                'LEBAR (INCH)', 'BENTUK (BLH/BLT)', 'GRAMASI (GSM)', 'WARNA', 'HANDFEEL', 'TREATMENT', 
                'HASIL (ROLL)', 'HASIL (KG)', 'KETERANGAN / CATATAN'
            ]
        ];
    }

    public function map($activity): array
    {
        $this->rowNumber++;
        $tech = is_string($activity->technical_data) ? json_decode($activity->technical_data, true) : ($activity->technical_data ?? []);

        // Lebar
        $actualLebar = null;
        if (isset($tech['lebar']) && $tech['lebar'] !== '') {
            $actualLebar = $tech['lebar'];
        } elseif (isset($tech['hasil_lebar']) && $tech['hasil_lebar'] !== '') {
            $actualLebar = $tech['hasil_lebar'];
        } elseif (isset($tech['finishing']['lebar']) && $tech['finishing']['lebar'] !== '') {
            $actualLebar = $tech['finishing']['lebar'];
        } elseif (isset($tech['finishing']['hasil_lebar']) && $tech['finishing']['hasil_lebar'] !== '') {
            $actualLebar = $tech['finishing']['hasil_lebar'];
        } elseif (isset($tech['drying']['lebar']) && $tech['drying']['lebar'] !== '') {
            $actualLebar = $tech['drying']['lebar'];
        } elseif (isset($tech['preset']['lebar']) && $tech['preset']['lebar'] !== '') {
            $actualLebar = $tech['preset']['lebar'];
        }

        // Gramasi
        $actualGramasi = null;
        if (isset($tech['gramasi']) && $tech['gramasi'] !== '') {
            $actualGramasi = $tech['gramasi'];
        } elseif (isset($tech['hasil_gramasi']) && $tech['hasil_gramasi'] !== '') {
            $actualGramasi = $tech['hasil_gramasi'];
        } elseif (isset($tech['finishing']['gramasi']) && $tech['finishing']['gramasi'] !== '') {
            $actualGramasi = $tech['finishing']['gramasi'];
        } elseif (isset($tech['finishing']['hasil_gramasi']) && $tech['finishing']['hasil_gramasi'] !== '') {
            $actualGramasi = $tech['finishing']['hasil_gramasi'];
        } elseif (isset($tech['drying']['gramasi']) && $tech['drying']['gramasi'] !== '') {
            $actualGramasi = $tech['drying']['gramasi'];
        } elseif (isset($tech['preset']['gramasi']) && $tech['preset']['gramasi'] !== '') {
            $actualGramasi = $tech['preset']['gramasi'];
        }

        // Warna
        $actualWarna = null;
        if (isset($tech['warna']) && $tech['warna'] !== '') {
            $actualWarna = $tech['warna'];
        }

        // Handfeel
        $actualHandfeel = null;
        if (isset($tech['handfeel']) && $tech['handfeel'] !== '') {
            $actualHandfeel = $tech['handfeel'];
        }

        // Treatment
        $actualTreatment = null;
        if (isset($tech['treatment']) && $tech['treatment'] !== '') {
            $actualTreatment = $tech['treatment'];
        } elseif (isset($tech['chemical']) && $tech['chemical'] !== '') {
            $actualTreatment = $tech['chemical'];
        }

        return [
            $this->rowNumber,
            $activity->marketingOrder->art_no ?? '-',
            $activity->marketingOrder->sap_no ?? '-',
            $activity->created_at->format('d/m/Y H:i'),
            strtoupper(str_replace('-', ' ', $activity->division_name)), 
            $activity->operator_name ?? ($activity->user->name ?? '-'), 
            $activity->marketingOrder->pelanggan ?? '-', 
            $activity->marketingOrder->mkt ?? '-', 
            $activity->marketingOrder->keperluan ?? '-',
            $activity->marketingOrder->konstruksi_greige ?? '-',
            $activity->marketingOrder->material ?? '-',
            $activity->marketingOrder->benang ?? '-', 
            $activity->marketingOrder->kelompok_kain ?? '-',
            $actualLebar ?? $activity->marketingOrder->target_lebar ?? '-',
            $activity->marketingOrder->belah_bulat ?? '-',
            $actualGramasi ?? $activity->marketingOrder->target_gramasi ?? '-',
            $actualWarna ?? $activity->marketingOrder->warna ?? '-',
            $actualHandfeel ?? $activity->marketingOrder->handfeel ?? '-',
            $actualTreatment ?? $activity->marketingOrder->treatment_khusus ?? '-',
            $activity->roll ?? 0,
            $activity->kg ?? 0,
            $activity->marketingOrder->keterangan_artikel ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 6 + count($this->activities); 
        
        // 1. Header Branding
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('ED1C24')); // Duniatex Red
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1e293b'));
        $sheet->getStyle('A3:A4')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748b'));

        // 2. Table Header (Row 6) - Industrial Solid Styling
        $sheet->getStyle('A6:V6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1e293b']], // Slate 800
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center']
        ]);

        // Optional: Sub-grouping colors for header (Identity, Specs, Results)
        $sheet->getStyle('J6:S6')->getFill()->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('334155')); // Slate 700 for Tech Specs
        $sheet->getStyle('T6:U6')->getFill()->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('b91c1c')); // Red 700 for Results

        // 3. Data Rows Alignment
        $sheet->getStyle("A7:V{$lastRow}")->applyFromArray([
            'font' => ['size' => 10],
            'alignment' => ['vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'cbd5e1']]]
        ]);

        // Rata Tengah: No, Artikel, SAP, Waktu, Tahapan, Kelompok, Lebar, Bentuk, Gramasi
        $centerCols = ['A', 'B', 'C', 'D', 'E', 'M', 'N', 'O', 'P'];
        foreach($centerCols as $col) {
            $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()->setHorizontal('center');
        }

        // Rata Kiri dengan Indent: Operator, Pelanggan, Sales, Keperluan, Konstruksi, Material, Benang, Warna, Handfeel, Treatment, Keterangan
        $leftCols = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'Q', 'R', 'S', 'V'];
        foreach($leftCols as $col) {
            $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()->setHorizontal('left');
            $sheet->getStyle("{$col}7:{$col}{$lastRow}")->getAlignment()->setIndent(1);
        }

        // Rata Kanan: Roll, KG
        $sheet->getStyle("T7:U{$lastRow}")->getAlignment()->setHorizontal('right');
        $sheet->getStyle("T7:U{$lastRow}")->getAlignment()->setIndent(1);
        $sheet->getStyle("T7:U{$lastRow}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0f172a')); // Bold Black
        
        // Format Angka
        $sheet->getStyle("T7:T{$lastRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("U7:U{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        // Zebra Striping (Subtle)
        for($i=7; $i<=$lastRow; $i++) {
            if($i % 2 == 0) {
                $sheet->getStyle("A{$i}:V{$i}")->getFill()->setFillType('solid')->getStartColor()->setRGB('f8fafc');
            }
        }

        // 4. TOTAL SUMMARY ROW
        if (count($this->activities) > 0) {
            $footerRow = $lastRow + 1;
            $sheet->setCellValue("A{$footerRow}", 'TOTAL AKUMULASI PRODUKSI');
            $sheet->mergeCells("A{$footerRow}:S{$footerRow}");
            
            $sheet->setCellValue("T{$footerRow}", "=SUM(T7:T{$lastRow})");
            $sheet->setCellValue("U{$footerRow}", "=SUM(U7:U{$lastRow})");
            
            $sheet->getStyle("A{$footerRow}:V{$footerRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0f172a']], // Very Dark Navy
                'alignment' => ['vertical' => 'center']
            ]);
            $sheet->getStyle("T{$footerRow}:U{$footerRow}")->getAlignment()->setHorizontal('right');
            $sheet->getStyle("U{$footerRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("T{$footerRow}")->getNumberFormat()->setFormatCode('#,##0'); // Roll is integer
        }

        // 5. Column Sizing
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(5); // No
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(18); // Waktu
        foreach(range('B','V') as $col) {
            if (!in_array($col, ['A', 'D'])) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->freezePane('A7');
                $sheet->setShowGridlines(false);
                $sheet->setAutoFilter('A6:V6');
                
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(6)->setRowHeight(30); // Header row
            },
        ];
    }
}
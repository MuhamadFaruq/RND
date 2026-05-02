<?php
namespace App\Exports;

use App\Models\ProductionActivity;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionExport implements FromQuery, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    protected $start;
    protected $end;
    protected $mode;
    protected $unit;
    protected $operator;
    private $rowNumber = 0; // Tambahkan properti untuk nomor urut

    public function __construct($start, $end, $mode, $unit, $operator = 'SEMUA')
    {
        $this->start = $start;
        $this->end = $end;
        $this->mode = $mode;
        $this->unit = $unit;
        $this->operator = $operator;
    }

    public function query()
    {
        return ProductionActivity::query()
            ->with(['marketingOrder', 'user']) 
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->when($this->mode === 'rajut', function($q) {
                $q->where('division_name', 'KNITTING');
            })
            ->when($this->mode === 'warna', function($q) {
                $q->whereIn('division_name', ['DYEING', 'FINISHING', 'STENTER']);
            })
            ->when($this->operator !== 'SEMUA', function($q) {
                $q->where('operator_id', $this->operator);
            })
            ->when($this->operator === 'SEMUA', function($q) {
                // Filter tambahan untuk memastikan data marketing/admin tidak ikut masuk
                $q->whereHas('user', function($u) {
                    $u->where('name', 'NOT LIKE', '%admin%')
                      ->where('name', 'NOT LIKE', '%marketing%');
                });
            });
    }

    public function headings(): array
    {
        return [
            'NO', // Kolom nomor urut
            'SAP', 'ART', 'TANGGAL & JAM', 'OPERATOR', 'PELANGGAN', 'MKT', 
            'KEPERLUAN', 'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 
            'KELOMPOK KAIN', 'TARGET LEBAR', 'BELAH/BULAT', 
            'TARGET GRAMASI', 'WARNA', 'HANDFEEL', 'TREATMENT KHUSUS', 
            'ROLL', 'KG', 'KETERANGAN ARTIKEL'
        ];
    }

    public function map($activity): array
    {
        $this->rowNumber++; // Naikkan nomor setiap baris data

        return [
            $this->rowNumber, // Masukkan nomor urut
            $activity->marketingOrder->sap_no ?? '-',
            $activity->marketingOrder->art_no ?? '-',
            $activity->created_at->format('d/m/Y H:i'),
            $activity->user->name ?? '-', 
            $activity->marketingOrder->pelanggan ?? '-', 
            $activity->marketingOrder->marketing_name ?? '-', 
            $activity->marketingOrder->keperluan ?? '-',
            $activity->marketingOrder->konstruksi_greige ?? '-',
            $activity->marketingOrder->material ?? '-',
            $activity->technical_data['benang_1'] ?? '-', 
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
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ED1C24'] // Merah Duniatex
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
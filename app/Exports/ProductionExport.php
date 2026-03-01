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

    // Terima parameter lengkap dari Controller
    public function __construct($start, $end, $mode, $unit)
    {
        $this->start = $start;
        $this->end = $end;
        $this->mode = $mode;
        $this->unit = $unit;
    }

    public function query()
    {
        return ProductionActivity::query()
            ->with(['marketingOrder'])
            ->whereDate('created_at', '>=', $this->start)
            ->whereDate('created_at', '<=', $this->end)
            ->when($this->unit !== 'SEMUA', function($q) {
                $q->whereHas('marketingOrder', fn($sq) => $sq->where('kelompok_kain', $this->unit));
            })
            ->when($this->mode === 'rajut', function($q) {
                $q->where('division_name', 'knitting');
            })
            ->when($this->mode === 'warna', function($q) {
                $q->whereIn('division_name', ['dyeing', 'finishing']);
            });
    }

    public function map($activity): array
    {
        // Mapping data sesuai permintaan kolom Anda
        return [
            $activity->marketingOrder->sap_no ?? '-',
            $activity->marketingOrder->art_no ?? '-',
            $activity->created_at->format('d/m/Y H:i'),
            $activity->marketingOrder->pelanggan ?? '-', // Sesuaikan 'customer' ke 'pelanggan' jika perlu
            $activity->marketingOrder->marketing_name ?? '-', 
            $activity->marketingOrder->keperluan ?? '-',
            $activity->marketingOrder->konstruksi_greige ?? '-',
            $activity->marketingOrder->material ?? '-',
            $activity->technical_data['benang_1'] ?? '-', // Mengambil dari JSON technical_data
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

    public function headings(): array
    {
        return [
            'SAP', 'ART', 'TANGGAL & JAM', 'PELANGGAN', 'MKT', 
            'KEPERLUAN', 'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 
            'KELOMPOK KAIN', 'TARGET LEBAR', 'BELAH/BULAT', 
            'TARGET GRAMASI', 'WARNA', 'HANDFEEL', 'TREATMENT KHUSUS', 
            'ROLL', 'KG', 'KETERANGAN ARTIKEL'
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
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingOrdersExport implements FromCollection, WithMapping, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $orders;
    protected $labelPeriode;

    // Tambahkan parameter kedua $labelPeriode
    public function __construct($orders, $labelPeriode) {
        $this->orders = $orders;
        $this->labelPeriode = $labelPeriode;
    }

    public function collection() {
        return $this->orders;
    }

    public function headings(): array {
        return [
            ['DUNIATEX GROUP - FULL MARKETING DATA REPORT'],
            ['Periode: ' . $this->labelPeriode], // Keterangan dinamis di sini
            ['Tanggal Cetak: ' . now()->format('d/m/Y H:i')],
            [''],
            ['SAP NO', 'ART NO', 'TANGGAL', 'PELANGGAN', 'MKT', 'KEPERLUAN', 'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 'KELOMPOK KAIN', 'TARGET LEBAR', 'BELAH/BULAT', 'TARGET GRAMASI', 'WARNA', 'HANDFEEL', 'TREATMENT KHUSUS', 'ROLL', 'KG', 'KETERANGAN']
        ];
    }

    public function map($order): array {
        return [
            $order->sap_no,
            $order->art_no,
            $order->tanggal,
            $order->pelanggan,
            $order->mkt,
            $order->keperluan,
            $order->konstruksi_greig,
            $order->material,
            $order->benang,
            $order->kelompok_kain,
            $order->target_lebar,
            $order->belah_bulat,
            $order->target_gramasi,
            $order->warna,
            $order->handfeel,
            $order->treatment_khusus,
            $order->roll_target,
            $order->kg_target,
            $order->keterangan_artikel,
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FF0000']]],
            2 => ['font' => ['bold' => true, 'italic' => true]],
            5 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '111827']]
            ],
        ];
    }
}
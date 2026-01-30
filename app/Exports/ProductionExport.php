<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Agar kolom lebar otomatis
use Maatwebsite\Excel\Concerns\WithStyles; // Untuk styling (Bold, Warna)
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductionExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return Order::select([
            'sap_no', 'art_no', 'tanggal', 'pelanggan', 'mkt', 
            'keperluan', 'konstruksi_greige', 'material', 'benang', 
            'kelompok_kain', 'target_lebar', 'belah_bulat', 
            'target_gramasi', 'warna', 'handfeel', 'treatment_khusus', 
            'roll', 'kg', 'keterangan_artikel'
        ])->get();
    }

    public function headings(): array
    {
        return [
            'SAP', 'ART', 'TANGGAL', 'PELANGGAN', 'MKT', 
            'KEPERLUAN', 'KONSTRUKSI GREIGE', 'MATERIAL', 'BENANG', 
            'KELOMPOK KAIN', 'TARGET LEBAR', 'BELAH/BULAT', 
            'TARGET GRAMASI', 'WARNA', 'HANDFEEL', 'TREATMENT KHUSUS', 
            'ROLL', 'KG', 'KETERANGAN ARTIKEL'
        ];
    }

    // Fungsi tambahan untuk membuat file Excel terlihat seperti "Template"
    public function styles(Worksheet $sheet)
    {
        return [
            // Baris 1 (Header) dibuat Bold dan Background Merah (Warna Duniatex)
            1    => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ED1C24']
                ],
            ],
        ];
    }
}
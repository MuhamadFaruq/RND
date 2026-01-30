<?php

namespace App\Exports;

use App\Models\MarketingOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingOrdersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    /**
    * Mengambil data yang akan diexport
    */
    public function collection()
    {
        return MarketingOrder::select([
            'sap_no', 'art_no', 'tanggal', 'pelanggan', 'mkt', 
            'material', 'target_lebar', 'target_gramasi', 'warna', 
            'roll_target', 'kg_target', 'status'
        ])->get();
    }

    /**
    * Membuat Header Kolom
    */
    public function headings(): array
    {
        return [
            'SAP NO', 'ART NO', 'TANGGAL ORDER', 'NAMA PELANGGAN', 'SALES (MKT)',
            'MATERIAL', 'TARGET LEBAR (CM)', 'TARGET GSM', 'WARNA',
            'TARGET ROLL', 'TARGET KG', 'STATUS PRODUKSI'
        ];
    }

    /**
    * Styling Header (Warna Merah Duniatex & Font Putih)
    */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ED1C24']
                ]
            ],
        ];
    }
}
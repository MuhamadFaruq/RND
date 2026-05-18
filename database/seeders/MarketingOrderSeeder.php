<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketingOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Data dummy untuk tes
        $orders = [
            [
                'sap_no' => 1001,
                'art_no' => 'DDT-01',
                'tanggal' => now(),
                'pelanggan' => 'Adidas Global',
                'mkt' => 'John Doe',
                'keperluan' => 'Sample',
                'konstruksi_greige' => 'Cotton Combed',
                'material' => 'Cotton',
                'benang' => '30s',
                'kelompok_kain' => 'Single Jersey',
                'target_lebar' => 180,
                'belah_bulat' => 'Belah',
                'target_gramasi' => 150,
                'warna' => 'Midnight Blue',
                'handfeel' => 'Soft',
                'roll_target' => 10,
                'kg_target' => 200,
            ],
        ];

        foreach ($orders as $order) {
            // Gunakan updateOrInsert dengan kriteria art_no (Nomor Artikel sebagai identitas unik utama)
            DB::table('marketing_orders')->updateOrInsert(
                ['art_no' => $order['art_no']], // Kriteria pencarian primer
                $order // Data yang dimasukkan/diupdate
            );
        }
    }
}
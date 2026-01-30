<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            [
                'name' => 'Knitting',
                'slug' => 'knitting',
                'icon' => '🧶',
                'description' => 'Bagian Perajutan Kain'
            ],
            [
                'name' => 'Dyeing',
                'slug' => 'dyeing',
                'icon' => '🧪',
                'description' => 'Bagian Pewarnaan Kain'
            ],
            [
                'name' => 'Stenter',
                'slug' => 'stenter',
                'icon' => '🖼️',
                'description' => 'Bagian Pengeringan & Setting Lebar'
            ],
            [
                'name' => 'Quality Control',
                'slug' => 'qc',
                'icon' => '🔬',
                'description' => 'Bagian Pemeriksaan Kualitas'
            ],
        ];

        foreach ($divisions as $div) {
            Division::updateOrCreate(['slug' => $div['slug']], $div);
        }
    }
}
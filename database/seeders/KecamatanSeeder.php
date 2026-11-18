<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kecamatan::truncate();
        $csvFile = fopen(base_path('database/csv/kecamatan.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                Kecamatan::create([
                    'id_kecamatan' => $data['0'],
                    'kabkot_id' => $data['1'],
                    'nama_kecamatan' => strtoupper($data['2']),
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

<?php

namespace Database\Seeders;

use App\Models\StatusPerkawinan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusPerkawinanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusPerkawinan::truncate();
        $csvFile = fopen(base_path('database/csv/status_perkawinan.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                StatusPerkawinan::create([
                    'id' => $data['0'],
                    'kode_status' => $data['1'],
                    'nama_status' => $data['2'],
                    'nilai_ptkp' => $data['3'],
                    'kategori_ptkp' => $data['4'],
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

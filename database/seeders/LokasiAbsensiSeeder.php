<?php

namespace Database\Seeders;

use App\Models\LokasiAbsensi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LokasiAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LokasiAbsensi::truncate();
        $csvFile = fopen(base_path('database/csv/lokasi_absensi.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                LokasiAbsensi::create([
                    'project_detail' => $data['0'],
                    'penempatan' => $data['1'],
                    'tag_location' => $data['2'],
                    'radius' => $data['3'],
                    'nama_koordinat' => trim(strtoupper($data['4'])),
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

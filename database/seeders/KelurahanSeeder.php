<?php

namespace Database\Seeders;

use App\Models\Kelurahan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelurahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kelurahan::truncate();
        $csvFile = fopen(base_path('database/csv/kelurahan.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                Kelurahan::create([
                    'id_kelurahan' => $data['0'],
                    'kecamatan_id' => $data['1'],
                    'nama_kelurahan' => strtoupper($data['2']),
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

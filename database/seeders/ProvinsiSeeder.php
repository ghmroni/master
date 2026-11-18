<?php

namespace Database\Seeders;

use App\Models\Provinsi;
use Illuminate\Database\Seeder;

class ProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Provinsi::truncate();
        $csvFile = fopen(base_path('database/csv/provinsi.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                Provinsi::create([
                    'id_provinsi' => $data['0'],
                    'nama_provinsi' => $data['1'],
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

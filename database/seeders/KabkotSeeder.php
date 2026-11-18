<?php

namespace Database\Seeders;

use App\Models\Kabkot;
use Illuminate\Database\Seeder;

class KabkotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kabkot::truncate();
        $csvFile = fopen(base_path('database/csv/kabkot.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                Kabkot::create([
                    'id_kabkot' => $data['0'],
                    'provinsi_id' => $data['1'],
                    'nama_kabkot' => $data['2'],
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

<?php

namespace Database\Seeders;

use App\Models\StatusKontrak;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusKontrakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusKontrak::truncate();
        $data = ['TETAP', 'PKWT', 'OJT', 'PB', 'KEMITRAAN'];
        $inisial = ['SK', 'PKWT', 'OJT', 'PB', 'KM'];
        foreach ($data as $index => $name) {
            StatusKontrak::create([
                'id'   => $index + 1,
                'name' => $name,
                'inisial' => $inisial[$index],
            ]);
        }
    }
}

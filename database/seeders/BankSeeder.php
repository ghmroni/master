<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Bank::truncate();
        $csvFile = fopen(base_path('database/csv/bank.csv'), 'r');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ';')) !== false) {
            if (! $firstline) {
                Bank::create([
                    'id' => $data['0'],
                    'name' => $data['1'],
                    'codebank' => $data['2'],
                    'logobank' => $data['3'],
                ]);
            }
            $firstline = false;
        }
        fclose($csvFile);
    }
}

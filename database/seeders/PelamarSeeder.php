<?php

namespace Database\Seeders;

use App\Models\Pelamar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PelamarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pelamar::factory()->count(100)->create();
    }
}

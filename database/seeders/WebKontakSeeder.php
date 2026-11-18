<?php

namespace Database\Seeders;

use App\Models\Webkontak;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WebKontakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Webkontak::truncate();

        $pusat = Webkontak::create([
            'name' => 'KANTOR PUSAT',
            'kategori' => 'PUSAT',
            'email' => 'ghm-pusat@humanindo.co.id',
            'handphone' => '+6285781071141',
            'tlp' => '02188961207',
            'fax' => '02188961207',
            'alamat' => 'Ruko Bekasi Mas Blok B No 16 Bekasi Selatan, Kota Bekasi - 17141',
            'latitude' => '-6.24085',
            'longitude' => '106.99386',
            'slug' => 'kantor-pusat',
        ]);
    }
}

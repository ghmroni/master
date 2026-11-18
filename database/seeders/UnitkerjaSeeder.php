<?php

namespace Database\Seeders;

use App\Models\Unitkerja;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UnitkerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Matikan pengecekan foreign key sementara
        Schema::disableForeignKeyConstraints();
        Unitkerja::truncate();

        // Hidupkan lagi pengecekan foreign key
        Schema::enableForeignKeyConstraints();

        $data = ['AVIO', 'BOARDING', 'CHECK IN', 'INTERPRETER', 'HAJI'];
        foreach ($data as $index => $name) {
            Unitkerja::create([
                'id'      => $index + 1,
                'name'    => $name,
                'user_id' => 1,
            ]);
        }
    }
}

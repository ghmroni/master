<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('system_settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('system_settings')->insert([
            'plan_name'    => 'Trial 6 Bulan',
            // Menggunakan toDateString() karena tipe datanya adalah 'date'
            'expired_at'   => Carbon::now()->addMonths(6)->toDateString(),
            'is_suspended' => false,
            'updated_by'   => null, // Biarkan null atau isi dengan ID Super Admin
            'created_at'   => Carbon::now(),
            'updated_at'   => Carbon::now(),
        ]);
    }
}

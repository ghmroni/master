<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::truncate();
        $admin = User::create([
            'name' => 'SUPER ADMIN',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $admin->assignRole(1);

        $pl = User::create([
            'name' => 'PL',
            'email' => 'pl@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $pl->assignRole(6);

        $rec = User::create([
            'name' => 'RECRUITMENT',
            'email' => 'rect@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $rec->assignRole(9);

        $gm = User::create([
            'name' => 'GM OPRS',
            'email' => 'gm@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $gm->assignRole(4);

        $hrtko = User::create([
            'name' => 'HR TKO',
            'email' => 'hr@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $hrtko->assignRole(8);

        $hr = User::create([
            'name' => 'HR HO',
            'email' => 'hrpusat@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $hr->assignRole(7);

        $legal = User::create([
            'name' => 'LEGAL',
            'email' => 'legal@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $legal->assignRole(10);

        $bpjs = User::create([
            'name' => 'ADMIN BPJS',
            'email' => 'bpjs@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $bpjs->assignRole(11);
    }
}

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
        $sa = User::create([
            'name' => 'SUPER ADMINISTRATOR',
            'email' => 'sa@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $sa->assignRole(1);

        $admin = User::create([
            'name' => 'ADMIN',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $admin->assignRole(2);

        $pengguna = User::create([
            'name' => 'PL',
            'email' => 'pl@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $pengguna->assignRole(3);

        $bpjs = User::create([
            'name' => 'ADMIN BPJS',
            'email' => 'bpjs@gmail.com',
            'password' => Hash::make('123456Aa'),
        ]);
        $bpjs->assignRole(4);
    }
}

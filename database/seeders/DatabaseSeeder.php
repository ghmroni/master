<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // LokasiAbsensiSeeder::class,
            // CFITSeeder::class,
            // PapiSeeder::class,
            // MbtiSeeder::class,
            // DiscSeeder::class,
            RolePermissionSeeder::class,
            MenuSeeder::class,
            UserSeeder::class,
            // ProjectSeeder::class,
            // KaryawanSeeder::class,
            // ProvinsiSeeder::class,
            // KabkotSeeder::class,
            // KecamatanSeeder::class,
            // KelurahanSeeder::class,
            StatusPerkawinanSeeder::class,
            // WebKontakSeeder::class,
            StatusKontrakSeeder::class,
            // PelamarSeeder::class,
            JabatanSeeder::class,
            BankSeeder::class,
        ]);
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Karyawan>
 */
class KaryawanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $idpelamar = 1;
        return [
            'pelamar_id' => $idpelamar++,
            'nip' => fake()->numberBetween(1, 6) . fake()->numerify('##########'),
            'ktp' => fake()->randomElement(['3']) . fake()->numerify('###############'),
            'nama' => strtoupper(fake()->name()),
            'email' => strtolower(fake()->unique()->safeEmail()),
            'tempat_lahir' => strtoupper(fake()->city()),
            'tanggal_lahir' => fake()->date('Y-m-d'),
            'tanggal_masuk' => fake()->date('Y-m-d'),
            'kelamin' => fake()->randomElement(['L', 'P']),
            'agama' => fake()->randomElement([
                'ISLAM',
                'KRISTEN',
                'PROTESTAN',
                'KATOLIK',
                'HINDU',
                'BUDDHA',
                'KONGHUCU',
            ]),
            'handphone' => fake()->randomElement(['85', '81', '89']) . fake()->numerify('#########'),
            'project' => fake()->numberBetween(2, 4),
            'password' => bcrypt('123456Aa'),
        ];
    }
}

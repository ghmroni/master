<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelamar>
 */
class PelamarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $nomor = 1;
        return [
            'nama' => $nama = strtoupper(fake()->name()),
            'noktp' => fake()->randomElement(['3']) . fake()->numerify('###############'),
            'tmptlahir' => strtoupper(fake()->city()),
            'tgllahir' => fake()->date('Y-m-d'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'email' => strtolower(str_replace(' ', '', $nama)) . '@gmail.com',
            'password' => bcrypt('123456Aa'),
            'handphone' => fake()->randomElement(['6285', '6281', '6289']) . fake()->numerify('#########'),
            'agama' => fake()->randomElement(['ISLAM', 'KRISTEN', 'PROTESTAN']),
            'status_kawin' => fake()->randomElement(['1', '2', '3', '4', '5']),
            'tinggi_badan' => fake()->numberBetween(150, 190),
            'berat_badan' => fake()->numberBetween(50, 100),
            'ukuran_baju' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'nomor_celana' => fake()->numberBetween(30, 45),
            'nomor_sepatu' => fake()->numberBetween(35, 44),
            'alamat_ktp' => fake()->address,
            'provinsi_ktp' => '32',
            'kabkot_ktp' => '3275',
            'kecamatan_ktp' => '327504',
            'kelurahan_ktp' => '3275041002',
            'kodepos_ktp' => '17141',
            'alamat_domisili' => fake()->address,
            'provinsi_domisili' => '32',
            'kabkot_domisili' => '3275',
            'kecamatan_domisili' => '327504',
            'kelurahan_domisili' => '3275041002',
            'kodepos_domisili' => '17141',
            'jenjang_pendidikan' => fake()->randomElement(['SD', 'SMP', 'SMA', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3']),
            'nama_sekolah' => strtoupper(fake()->city()),
            'jurusan_sekolah' => 'JURUSAN',
            'tahun_lulus' => fake()->date('Y'),
            'nilai_ijazah' => fake()->numberBetween(2, 4),
            'status' => 'WAITING',
            'register' => 'registered',
            'file_cv' => 'cv_' . $nomor++ . '.pdf',
            'file_ijazah' => 'ijazah_' . $nomor++ . '.pdf',
        ];
    }
}

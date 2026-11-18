<?php

namespace Database\Seeders;

use App\Models\CfitMaster;
use App\Models\CfitNorma;
use Illuminate\Database\Seeder;

class CFITSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CfitMaster::truncate();
        $kunci_jawaban = [
            'B',
            'C',
            'B',
            'D',
            'E',
            'B',
            'D',
            'B',
            'F',
            'C',
            'B',
            'B',
            'E',
            'BE',
            'AE',
            'AD',
            'CE',
            'BE',
            'AD',
            'BE',
            'BE',
            'AD',
            'BD',
            'AE',
            'CD',
            'BC',
            'AB',
            'E',
            'E',
            'E',
            'B',
            'C',
            'D',
            'E',
            'E',
            'A',
            'A',
            'F',
            'C',
            'C',
            'B',
            'A',
            'D',
            'D',
            'A',
            'B',
            'C',
            'D',
            'A',
            'D',
        ];

        $dataIQ = [
            38,
            40,
            43,
            45,
            47,
            48,
            52,
            55,
            57,
            60,
            63,
            67,
            70,
            72,
            75,
            78,
            81,
            85,
            88,
            91,
            94,
            96,
            100,
            103,
            106,
            109,
            113,
            116,
            119,
            121,
            124,
            128,
            131,
            133,
            137,
            140,
            142,
            145,
            149,
            152,
            155,
            157,
            161,
            165,
            167,
            169,
            173,
            176,
            179,
            183,
            183
        ];

        foreach ($kunci_jawaban as $key => $value) {
            $nomorUrut = $key + 1;

            if ($nomorUrut <= 13) {
                $subTest = 1;
                $nomorSoalSubTest = $nomorUrut; // Soal 1 - 13
            } elseif ($nomorUrut <= 27) { // 13 + 14 soal = 27
                $subTest = 2;
                $nomorSoalSubTest = $nomorUrut - 13; // Soal 1 - 14
            } elseif ($nomorUrut <= 40) { // 27 + 13 soal = 40
                $subTest = 3;
                $nomorSoalSubTest = $nomorUrut - 27; // Soal 1 - 13
            } else {
                $subTest = 4;
                $nomorSoalSubTest = $nomorUrut - 40; // Soal 1 - 10
            }

            CfitMaster::create([
                'id'            => $nomorUrut,
                'sub_test'      => $subTest,
                'nomor_soal'    => $nomorSoalSubTest, // Sekarang nilainya berulang dari 1 tiap ganti sub-test
                'kunci_jawaban' => $value,
                'nilai_benar'   => 1,
                'nilai_salah'   => 0,
            ]);

            $keterangan = match (true) {
                $dataIQ[$key] <= 69  => strtoupper('Mentally Retardation'),
                $dataIQ[$key] <= 79  => strtoupper('Borderline Defective'),
                $dataIQ[$key] <= 89  => strtoupper('Low Average'),
                $dataIQ[$key] <= 109 => strtoupper('Average'),
                $dataIQ[$key] <= 119 => strtoupper('High Average'),
                $dataIQ[$key] <= 139 => strtoupper('Superior'),
                $dataIQ[$key] <= 169 => strtoupper('Very Superior'),
                default    => strtoupper('Genius'),
            };

            CfitNorma::create([
                'id'           => $nomorUrut,
                'skor_mentah'  => $key,
                'skor_standar' => $dataIQ[$key],
                'keterangan'   => $keterangan,
            ]);
        }
    }
}

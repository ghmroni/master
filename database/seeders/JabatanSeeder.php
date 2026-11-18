<?php

namespace Database\Seeders;

use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Jabatan::truncate();
        $data = [
            'ADM KAM',
            'ADMIN HOTEL',
            'ADMIN HR',
            'ADMIN YARD SUPPORT',
            'ADMINISTRASI',
            'ADMINISTRASI KEUANGAN',
            'AIRCRAFT CLEANING',
            'ANGGOTA SECURITY',
            'AVSEC',
            'CARGO',
            'CASHIER',
            'CLEANING SERVICE',
            'CONTROLLERS',
            'COURIER',
            'CUSTOMER SERVICE',
            'DANRU',
            'DEPCO',
            'DIREKTUR',
            'DIREKTUR UTAMA',
            'DISPATCHER PDC',
            'DRIVER',
            'DRIVER BP',
            'DRIVER PART',
            'FOO',
            'FREIGHTER',
            'GENDEC',
            'GM OPERASIONAL',
            'GREETING',
            'GROUP LEADER',
            'GSE MEKANIK',
            'GSE OPERATOR',
            'JOUMPA',
            'KA SATPAM',
            'KEUANGAN',
            'KITCHEN',
            'KOMISARIS UTAMA',
            'KOORDINATOR',
            'KOORDINATOR CLEANING SERVICE',
            'KOORDINATOR DRIVER',
            'KOORDINATOR ME',
            'LOAD MASTER',
            'LOADING UNLOADING',
            'MAINTENANCE',
            'MANAJER HR LEGAL',
            'MANAJER IT',
            'MANAJER KEUANGAN',
            'MASSENGGER',
            'OFFICE BOY',
            'OPERASIONAL MANAGER',
            'OPERATION',
            'OPERATOR GARBARATA',
            'PASASI',
            'PEMBANTU PDS',
            'PENUNJANG OPERASIONAL',
            'PIC',
            'PORTER',
            'PRAMUBAKTI',
            'PROJECT LEADER',
            'PROJECT MANAGER',
            'RAMP',
            'RECEPTIONIS',
            'SECURITY',
            'SECWAN',
            'SERVICE PLUS',
            'STAFF',
            'STAFF ACCOUNTING',
            'STAFF IT',
            'STAFF LEGAL',
            'STAFF OPERATION',
            'STAFF REKRUTMEN',
            'SUPERVISOR',
            'SUPERVISOR BANQUET',
            'SUPERVISOR FRONT OFFICE',
            'SUPERVISOR KITCHEN',
            'SUPERVISOR PUBLIC AREA',
            'SUPERVISOR RESTAURANT',
            'SUPERVISOR ROOM ATTENDANT',
            'TIM SUS ADM REGU',
            'TIM SUS CCTV',
            'TIM SUS PATROLI',
            'TIM SUS PKD',
            'VALLET SERVICE'
        ];

        foreach ($data as $index => $name) {
            Jabatan::create([
                'id'   => $index + 1,
                'name' => $name,
                'user_id' => 1,
            ]);
        }
    }
}

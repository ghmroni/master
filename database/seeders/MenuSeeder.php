<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\SubMenu;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menu::truncate();
        // Hapus data lama dengan delete, bukan truncate
        SubMenu::query()->delete();
        Menu::query()->delete();

        ## =============================================================== DASHBOARD (public / tidak punya permission)
        $dashboard = Menu::create([
            'name'   => 'DASHBOARD',
            'url'    => '/',
            'icon'   => 'ti ti-home',
            'sort'   => 0,
            'status' => 'ENABLE',
        ]);

        ## =============================================================== SETTINGS
        $settings = Menu::create([
            'name'   => 'PENGATURAN',
            'url'    => null,
            'icon'   => 'ti ti-settings',
            'sort'   => 1,
            'status' => 'ENABLE',
        ]);
        $permSettings = Permission::whereIn('name', [
            'import-karyawan',
            'absensi-setlokasi',
            'setting-gaji',
            'setting-lembur',
            'lihat-menu',
            'lihat-user',
            'lihat-role',
            'lihat-kontrakinduk',
            'lihat-permission',
            'lihat-project',
            'lihat-aksesproject',
            'lihat-jabatan',
            'lihat-tunjangan',
            'lihat-unitkerja',
            'lihat-bank',
            'lihat-statuskawin',
            'libur-nasional'
        ])->pluck('id', 'name');

        $subMenuSettings = [
            [
                'name'       => 'Import Karyawan',
                'url'        => '/import-karyawan',
                'sort'       => 0,
                'status'     => 'ENABLE',
                'permission' => $permSettings['import-karyawan'],
            ],
            [
                'name'       => 'Master Gaji',
                'url'        => '/settinggaji',
                'sort'       => 0,
                'status'     => 'ENABLE',
                'permission' => $permSettings['setting-gaji'],
            ],
            [
                'name'       => 'Master Lembur',
                'url'        => '/settinglembur',
                'sort'       => 0,
                'status'     => 'ENABLE',
                'permission' => $permSettings['setting-lembur'],
            ],
            [
                'name'       => 'Menu',
                'url'        => '/menus',
                'sort'       => 0,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-menu'],
            ],
            [
                'name'       => 'Kontrak Induk',
                'url'        => '/kontrakInduk',
                'sort'       => 1,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-kontrakinduk'],
            ],
            [
                'name'       => 'Pengguna',
                'url'        => '/pengguna',
                'sort'       => 1,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-user'],
            ],
            [
                'name'       => 'Roles / Level',
                'url'        => '/roles',
                'sort'       => 2,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-role'],
            ],
            [
                'name'       => 'Permissions',
                'url'        => '/permissions',
                'sort'       => 3,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-permission'],
            ],
            [
                'name'       => 'Project',
                'url'        => '/project',
                'sort'       => 4,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-project'],
            ],
            [
                'name'       => 'Akses Project',
                'url'        => '/akses-projectuser',
                'sort'       => 5,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-aksesproject'],
            ],
            [
                'name'       => 'Libur Nasional',
                'url'        => '/libur-nasional',
                'sort'       => 5,
                'status'     => 'ENABLE',
                'permission' => $permSettings['libur-nasional'],
            ],
            [
                'name'       => 'Jabatan',
                'url'        => '/jabatan',
                'sort'       => 6,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-jabatan'],
            ],
            [
                'name'       => 'Tunjangan',
                'url'        => '/tunjangan',
                'sort'       => 6,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-tunjangan'],
            ],
            [
                'name'       => 'Bank',
                'url'        => '/bank',
                'sort'       => 7,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-bank'],
            ],
            [
                'name'       => 'Unitkerja',
                'url'        => '/unitkerja',
                'sort'       => 8,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-unitkerja'],
            ],
            [
                'name'       => 'Status Perkawinan',
                'url'        => '/statuskawin',
                'sort'       => 9,
                'status'     => 'ENABLE',
                'permission' => $permSettings['lihat-statuskawin'],
            ],
        ];

        foreach ($subMenuSettings as $sub) {
            $settings->subMenus()
                ->create([
                    'name'   => strtoupper($sub['name']),
                    'url'    => $sub['url'],
                    'sort'   => $sub['sort'],
                    'status' => $sub['status'],
                ])
                ->permissions()
                ->sync($sub['permission']);
        }

        ## =============================================================== KARYAWAN
        $karyawan = Menu::create([
            'name'   => 'DATA KARYAWAN',
            'url'    => null,
            'icon'   => 'ti ti-users',
            'sort'   => 3,
            'status' => 'ENABLE',
        ]);
        $permKaryawanIds = Permission::whereIn('name', [
            'karyawan-aktif',
            'karyawan-keluar'
        ])->pluck('id', 'name');

        $subMenuKaryawan = [
            [
                'name'       => 'Karyawan Aktif',
                'url'        => '/karyawanaktif',
                'sort'       => 0,
                'status'     => 'ENABLE',
                'permission' => $permKaryawanIds['karyawan-aktif'],
            ],
            [
                'name'       => 'Karyawan Keluar',
                'url'        => '/karyawanout',
                'sort'       => 1,
                'status'     => 'ENABLE',
                'permission' => $permKaryawanIds['karyawan-keluar'],
            ],
        ];
        foreach ($subMenuKaryawan as $sub) {
            $karyawan->subMenus()
                ->create([
                    'name'   => strtoupper($sub['name']),
                    'url'    => $sub['url'],
                    'sort'   => $sub['sort'],
                    'status' => $sub['status'],
                ])
                ->permissions()
                ->sync($sub['permission']);
        }

        ## =============================================================== Permintaan
        $req = Menu::create([
            'name'   => 'PERMINTAAN',
            'url'    => null,
            'icon'   => 'ti ti-receipt',
            'sort'   => 1,
            'status' => 'ENABLE',
        ]);

        $permPermintaanIds = Permission::whereIn('name', [
            'reqout-karyawan',
            'mutasi-karyawan'
        ])->pluck('id', 'name');
        $req->subMenus()->create([
            'name'   => 'KARYAWAN KELUAR',
            'url'    => '/requestOut',
            'sort'   => 1,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permPermintaanIds['reqout-karyawan']]);

        $req->subMenus()->create([
            'name'   => 'MUTASI KARYAWAN',
            'url'    => '/mutasi-karyawan',
            'sort'   => 1,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permPermintaanIds['mutasi-karyawan']]);

        ## =============================================================== Persetujuan
        $approve = Menu::create([
            'name'   => 'PERSETUJUAN',
            'url'    => null,
            'icon'   => 'ti ti-archive',
            'sort'   => 1,
            'status' => 'ENABLE',
        ]);

        $ka = $approve->subMenus()->create([
            'name'   => 'KONTRAK INDUK / ADENDUM',
            'url'    => '/approval-kontrakinduk',
            'sort'   => 0,
            'status' => 'ENABLE',
        ]);
        $permAppKontrakinduk = Permission::where('name', 'approval-kontrakinduk')->first();
        $ka->permissions()->sync([$permAppKontrakinduk->id]);

        $karyawanMasuk = $approve->subMenus()->create([
            'name'   => 'KARYAWAN MASUK',
            'url'    => '/approvalIn',
            'sort'   => 1,
            'status' => 'ENABLE',
        ]);
        $permkaryawanMasuk = Permission::where('name', 'approve-karyawan-in')->first();
        $karyawanMasuk->permissions()->sync($permkaryawanMasuk->id);

        $karyawanKeluar = $approve->subMenus()->create([
            'name'   => 'KARYAWAN KELUAR',
            'url'    => '/approvalOut',
            'sort'   => 2,
            'status' => 'ENABLE',
        ]);
        $permKarywanKeluar = Permission::where('name', 'approve-karyawan-out')->first();
        $karyawanKeluar->permissions()->attach($permKarywanKeluar->id);

        $mutasiKar = $approve->subMenus()->create([
            'name'   => 'MUTASI KARYAWAN',
            'url'    => '/approval-mutasi',
            'sort'   => 3,
            'status' => 'ENABLE',
        ]);
        $approveMutasi = Permission::where('name', 'approve-mutasi')->first();
        $mutasiKar->permissions()->sync($approveMutasi->id);

        ## =============================================================== PKWT
        $pkwt = Menu::create([
            'name'   => 'PKWT',
            'url'    => '/pkwt',
            'icon'   => 'ti ti-book',
            'sort'   => 3,
            'status' => 'ENABLE',
        ]);
        $permissionsPkwtId = Permission::where('name', 'cetak-pkwt')->first();
        $pkwt->permissions()->sync([$permissionsPkwtId->id]);

        ## =============================================================== BPJS
        $bpjs = Menu::create([
            'name'   => 'BPJS',
            'url'    => null,
            'icon'   => 'ti ti-shield',
            'sort'   => 1,
            'status' => 'ENABLE',
        ]);
        $permissionsBpjsIds = Permission::whereIn('name', [
            'lihat-bpjs',
            'setting-bpjs',
        ])->pluck('id', 'name');

        $bpjs->subMenus()->create([
            'name'   => 'DATA BPJS',
            'url'    => '/bpjs',
            'sort'   => 0,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permissionsBpjsIds['lihat-bpjs']]);
        $bpjs->subMenus()->create([
            'name'   => 'Setting BPJS',
            'url'    => '/setting-bpjs',
            'sort'   => 1,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permissionsBpjsIds['setting-bpjs']]);

        ## =============================================================== PENGGAJIAN
        $gaji = Menu::create([
            'name'   => 'PENGGAJIAN',
            'url'    => null,
            'icon'   => 'ti ti-wallet',
            'sort'   => 3,
            'status' => 'ENABLE',
        ]);

        $permissionGajiIds = Permission::whereIn('name', [
            'proses-gaji',
            'pengajuan-gaji',
            'slip-gaji',
        ])
            ->pluck('id', 'name');

        $uploadPenggajian = $gaji->subMenus()->create([
            'name'   => 'Upload',
            'url'    => '/uploadgaji',
            'sort'   => 0,
            'status' => 'ENABLE',
        ]);
        $permissionUploadPenggajian = Permission::whereIn('name', ['upload-gaji'])
            ->pluck('id')
            ->toArray();
        $uploadPenggajian->permissions()->attach($permissionUploadPenggajian);

        $gaji->subMenus()->create([
            'name'   => 'Proses Gaji',
            'url'    => '/prosesgaji',
            'sort'   => 1,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permissionGajiIds['proses-gaji']]);
        $gaji->subMenus()->create([
            'name'   => 'Pengajuan Gaji',
            'url'    => '/pengajuan-gaji',
            'sort'   => 2,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permissionGajiIds['pengajuan-gaji']]);
        $gaji->subMenus()->create([
            'name'   => 'Slip Gaji',
            'url'    => '/slipgaji',
            'sort'   => 3,
            'status' => 'ENABLE',
        ])->permissions()->sync([$permissionGajiIds['slip-gaji']]);

        ## =============================================================== KEHADIRAN
        $kehadiran = Menu::create([
            'name'   => 'KEHADIRAN',
            'url'    => null,
            'icon'   => 'fas fa-street-view',
            'sort'   => 2,
            'status' => 'ENABLE',
        ]);
        $permissionAbsensiIds = Permission::whereIn('name', [
            'absensi-realtime',
            'absensi-perorang',
            'absensi-perlokasi',
            'absensi-datalokasi',
            'absensi-data-schedule'
        ])
            ->pluck('id', 'name');

        $kehadiran->subMenus()
            ->create([
                'name'   => 'REALTIME',
                'url'    => '/absensi',
                'sort'   => 0,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionAbsensiIds['absensi-realtime']]);

        $kehadiran->subMenus()
            ->create([
                'name'   => 'PER ORANG',
                'url'    => '/perorang',
                'sort'   => 1,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionAbsensiIds['absensi-perorang']]);

        $kehadiran->subMenus()
            ->create([
                'name'   => 'PER LOKASI',
                'url'    => '/perlokasi',
                'sort'   => 2,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionAbsensiIds['absensi-perlokasi']]);

        $kehadiran->subMenus()
            ->create([
                'name'   => 'DATA SCHEDULE',
                'url'    => '/data-schedule',
                'sort'   => 3,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionAbsensiIds['absensi-data-schedule']]);

        // $uploadKehadiaran = $kehadiran->subMenus()
        //     ->create([
        //         'name'   => 'UPLOAD',
        //         'url'    => '/absensi-upload',
        //         'sort'   => 3,
        //         'status' => 'ENABLE',
        //     ]);
        // $permissionUploadKehadiran = Permission::whereIn('name', ['absensi-upload-absensi', 'absensi-upload-schedule'])
        //     ->pluck('id')
        //     ->toArray();
        // $uploadKehadiaran->permissions()->attach($permissionUploadKehadiran);

        $dtLokasiAbsensi = $kehadiran->subMenus()
            ->create([
                'name'   => 'LOKASI ABSENSI',
                'url'    => '/lokasi-absensi',
                'sort'   => 5,
                'status' => 'ENABLE',
            ]);
        $permissionLokasiAbsensi = Permission::whereIn('name', ['absensi-datalokasi', 'absensi-setlokasi'])
            ->pluck('id')
            ->toArray();
        $dtLokasiAbsensi->permissions()->attach($permissionLokasiAbsensi);

        ## =============================================================== MONITORING
        $monitoring = Menu::create([
            'name'   => 'MONITORING',
            'url'    => null,
            'icon'   => 'ti ti-calendar',
            'sort'   => 2,
            'status' => 'ENABLE',
        ]);
        $permissionMonitoringIds = Permission::whereIn('name', [
            'monitoring-realtime',
            'monitoring-perorang',
            'monitoring-rekap'
        ])
            ->pluck('id', 'name');

        $monitoring->subMenus()
            ->create([
                'name'   => 'REALTIME',
                'url'    => '/monitoring',
                'sort'   => 0,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionMonitoringIds['monitoring-realtime']]);

        $monitoring->subMenus()
            ->create([
                'name'   => 'PER ORANG',
                'url'    => '/monitoring-perorang',
                'sort'   => 1,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionMonitoringIds['monitoring-perorang']]);

        $monitoring->subMenus()
            ->create([
                'name'   => 'REKAP MONITORING',
                'url'    => '/rekap-monitoring',
                'sort'   => 2,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionMonitoringIds['monitoring-rekap']]);

        ## =============================================================== SIMC
        $simc = Menu::create([
            'name'   => 'SIMC',
            'url'    => null,
            'icon'   => 'ti ti-heart-broken',
            'sort'   => 2,
            'status' => 'ENABLE',
        ]);
        $permissionSimcIds = Permission::whereIn('name', [
            'simc-sakit',
            'simc-izin',
            'simc-cuti',
            'simc-sisa-cuti',
            'simc-rekap'
        ])
            ->pluck('id', 'name');

        $simc->subMenus()
            ->create([
                'name'   => 'SAKIT',
                'url'    => '/sakit',
                'sort'   => 0,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionSimcIds['simc-sakit']]);

        $simc->subMenus()
            ->create([
                'name'   => 'IZIN',
                'url'    => '/izin',
                'sort'   => 1,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionSimcIds['simc-izin']]);

        $simc->subMenus()
            ->create([
                'name'   => 'CUTI',
                'url'    => '/cuti',
                'sort'   => 2,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionSimcIds['simc-cuti']]);

        $simc->subMenus()
            ->create([
                'name'   => 'SISA CUTI',
                'url'    => '/sisacuti',
                'sort'   => 3,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionSimcIds['simc-sisa-cuti']]);

        ## =============================================================== LEMBUR
        $lembur = Menu::create([
            'name'   => 'LEMBUR',
            'url'    => null,
            'icon'   => 'ti ti-dashboard',
            'sort'   => 2,
            'status' => 'ENABLE',
        ]);
        $permissionLemburIds = Permission::whereIn('name', [
            'approval-lembur',
            'data-lembur',
        ])
            ->pluck('id', 'name');

        $lembur->subMenus()
            ->create([
                'name'   => 'APPROVAL LEMBUR',
                'url'    => '/approval-lembur',
                'sort'   => 0,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionLemburIds['approval-lembur']]);

        $lembur->subMenus()
            ->create([
                'name'   => 'DATA LEMBUR',
                'url'    => '/data-lembur',
                'sort'   => 1,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionLemburIds['data-lembur']]);

        ## =============================================================== DOWNLOAD
        $download = Menu::create([
            'name'   => 'DOWNLOAD',
            'url'    => null,
            'icon'   => 'ti ti-download',
            'sort'   => 6,
            'status' => 'ENABLE',
        ]);
        $permissionDownloadIds = Permission::whereIn('name', [
            'download-karyawan',
            'absensi-rekap-absen',
            'absensi-rekap-waktu',
            'download-lembur',
        ])
            ->pluck('id', 'name');

        $download->subMenus()
            ->create([
                'name'   => 'KARYAWAN',
                'url'    => '/download-karyawan',
                'sort'   => 0,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionDownloadIds['download-karyawan']]);
        # ABSENSI
        $download->subMenus()
            ->create([
                'name'   => 'REKAP WAKTU',
                'url'    => '/rekap-waktu',
                'sort'   => 1,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionDownloadIds['absensi-rekap-waktu']]);
        $download->subMenus()
            ->create([
                'name'   => 'REKAP KEHADIRAN',
                'url'    => '/rekap-kehadiran',
                'sort'   => 2,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionDownloadIds['absensi-rekap-absen']]);
        $download->subMenus()
            ->create([
                'name'   => 'REKAP LEMBUR',
                'url'    => '/rekap-lembur',
                'sort'   => 3,
                'status' => 'ENABLE',
            ])->permissions()->sync([$permissionDownloadIds['download-lembur']]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ## Permissions Users
        Permission::create(['name' => 'lihat-user', 'module' => 'Users']);
        Permission::create(['name' => 'tambah-user', 'module' => 'Users']);
        Permission::create(['name' => 'edit-user', 'module' => 'Users']);
        ## Permissions Roles
        Permission::create(['name' => 'lihat-role', 'module' => 'Roles']);
        Permission::create(['name' => 'tambah-role', 'module' => 'Roles']);
        Permission::create(['name' => 'edit-role', 'module' => 'Roles']);
        ## Permissions
        Permission::create(['name' => 'lihat-permission', 'module' => 'Permissions']);
        Permission::create(['name' => 'tambah-permission', 'module' => 'Permissions']);
        Permission::create(['name' => 'edit-permission', 'module' => 'Permissions']);
        ## Permissions Menu
        Permission::create(['name' => 'lihat-menu', 'module' => 'Menus']);
        Permission::create(['name' => 'tambah-menu', 'module' => 'Menus']);
        Permission::create(['name' => 'edit-menu', 'module' => 'Menus']);
        ## Permissions PT
        Permission::create(['name' => 'lihat-company', 'module' => 'Company']);
        Permission::create(['name' => 'tambah-company', 'module' => 'Company']);
        Permission::create(['name' => 'edit-company', 'module' => 'Company']);
        ## Permissions Project
        Permission::create(['name' => 'lihat-project', 'module' => 'Project']);
        Permission::create(['name' => 'tambah-project', 'module' => 'Project']);
        Permission::create(['name' => 'edit-project', 'module' => 'Project']);
        ## Permissions Jabatan
        Permission::create(['name' => 'lihat-jabatan', 'module' => 'Jabatan']);
        Permission::create(['name' => 'tambah-jabatan', 'module' => 'Jabatan']);
        Permission::create(['name' => 'edit-jabatan', 'module' => 'Jabatan']);
        Permission::create(['name' => 'hapus-jabatan', 'module' => 'Jabatan']);
        ## Permissions Website
        Permission::create(['name' => 'kelola-website', 'module' => 'Website']);
        ## Permissions Pelamar
        Permission::create(['name' => 'lihat-pelamar', 'module' => 'Pelamar']);
        ## Permissions Bank
        Permission::create(['name' => 'lihat-bank', 'module' => 'Bank']);
        Permission::create(['name' => 'tambah-bank', 'module' => 'Bank']);
        Permission::create(['name' => 'edit-bank', 'module' => 'Bank']);
        Permission::create(['name' => 'hapus-bank', 'module' => 'Bank']);
        ## Permissions Status Kawin
        Permission::create(['name' => 'lihat-statuskawin', 'module' => 'Statuskawin']);
        Permission::create(['name' => 'tambah-statuskawin', 'module' => 'Statuskawin']);
        Permission::create(['name' => 'edit-statuskawin', 'module' => 'Statuskawin']);
        Permission::create(['name' => 'hapus-statuskawin', 'module' => 'Statuskawin']);
        ## Permissions Status Kawin
        Permission::create(['name' => 'lihat-ptko', 'module' => 'PTKO']);
        Permission::create(['name' => 'tambah-ptko', 'module' => 'PTKO']);
        Permission::create(['name' => 'edit-ptko', 'module' => 'PTKO']);
        Permission::create(['name' => 'batal-ptko', 'module' => 'PTKO']);
        ## Permissions Approval
        Permission::create(['name' => 'approve-karyawan-in', 'module' => 'Approval']);
        Permission::create(['name' => 'approve-karyawan-out', 'module' => 'Approval']);
        Permission::create(['name' => 'approve-mutasi', 'module' => 'Approval']);
        Permission::create(['name' => 'approval-kontrakinduk', 'module' => 'Approval']);
        Permission::create(['name' => 'approve-banksdm', 'module' => 'Approval']);
        ## Permissions Karyawan
        Permission::create(['name' => 'karyawan-aktif', 'module' => 'Karyawan']);
        Permission::create(['name' => 'karyawan-keluar', 'module' => 'Karyawan']);
        Permission::create(['name' => 'edit-karyawan', 'module' => 'Karyawan']);
        Permission::create(['name' => 'reqout-karyawan', 'module' => 'Karyawan']);
        Permission::create(['name' => 'mutasi-karyawan', 'module' => 'Karyawan']);
        Permission::create(['name' => 'download-karyawan', 'module' => 'Karyawan']);
        Permission::create(['name' => 'paklaring-karyawan', 'module' => 'Karyawan']);
        Permission::create(['name' => 'karyawan-reset', 'module' => 'Karyawan']);
        ## Permissions Lowongan
        Permission::create(['name' => 'lihat-lowongan', 'module' => 'Lowongan']);
        Permission::create(['name' => 'tambah-lowongan', 'module' => 'Lowongan']);
        Permission::create(['name' => 'edit-lowongan', 'module' => 'Lowongan']);
        Permission::create(['name' => 'hapus-lowongan', 'module' => 'Lowongan']);
        ## Permissions Akses Project
        Permission::create(['name' => 'lihat-aksesproject', 'module' => 'Akses Project']);
        Permission::create(['name' => 'edit-aksesproject', 'module' => 'Akses Project']);
        ## Permissions Pemanggilan
        Permission::create(['name' => 'tambah-pemanggilan', 'module' => 'Pemanggilan']);
        Permission::create(['name' => 'lihat-pemanggilan', 'module' => 'Pemanggilan']);
        Permission::create(['name' => 'edit-pemanggilan', 'module' => 'Pemanggilan']);
        Permission::create(['name' => 'banksdm', 'module' => 'Pemanggilan']);
        ## Permissions Unitkerja
        Permission::create(['name' => 'lihat-unitkerja', 'module' => 'Unitkerja']);
        Permission::create(['name' => 'tambah-unitkerja', 'module' => 'Unitkerja']);
        Permission::create(['name' => 'edit-unitkerja', 'module' => 'Unitkerja']);
        Permission::create(['name' => 'hapus-unitkerja', 'module' => 'Unitkerja']);
        ## Permissions KontrakInduk
        Permission::create(['name' => 'lihat-kontrakinduk', 'module' => 'Kontrakinduk']);
        Permission::create(['name' => 'tambah-kontrakinduk', 'module' => 'Kontrakinduk']);
        Permission::create(['name' => 'edit-kontrakinduk', 'module' => 'Kontrakinduk']);
        Permission::create(['name' => 'hapus-kontrakinduk', 'module' => 'Kontrakinduk']);
        ## Permissions PKWT
        Permission::create(['name' => 'cetak-pkwt', 'module' => 'PKWT']);
        Permission::create(['name' => 'edit-pkwt', 'module' => 'PKWT']);
        Permission::create(['name' => 'tambah-pkwt', 'module' => 'PKWT']);
        ## Permissions BPJS
        Permission::create(['name' => 'lihat-bpjs', 'module' => 'Bpjs']);
        Permission::create(['name' => 'edit-bpjs', 'module' => 'Bpjs']);
        Permission::create(['name' => 'tambah-bpjs', 'module' => 'Bpjs']);
        Permission::create(['name' => 'setting-bpjs', 'module' => 'Bpjs']);
        Permission::create(['name' => 'hapus-bpjs', 'module' => 'Bpjs']);
        ## Permissions Penggajian
        Permission::create(['name' => 'upload-gaji', 'module' => 'Penggajian']);
        Permission::create(['name' => 'proses-gaji', 'module' => 'Penggajian']);
        Permission::create(['name' => 'pengajuan-gaji', 'module' => 'Penggajian']);
        Permission::create(['name' => 'slip-gaji', 'module' => 'Penggajian']);
        ## Permissions Tunjangan
        Permission::create(['name' => 'lihat-tunjangan', 'module' => 'Tunjangan']);
        Permission::create(['name' => 'tambah-tunjangan', 'module' => 'Tunjangan']);
        Permission::create(['name' => 'edit-tunjangan', 'module' => 'Tunjangan']);
        Permission::create(['name' => 'hapus-tunjangan', 'module' => 'Tunjangan']);
        ## Permissions Settings
        Permission::create(['name' => 'import-karyawan', 'module' => 'Setting']);
        Permission::create(['name' => 'absensi-setlokasi', 'module' => 'Setting']);
        Permission::create(['name' => 'setting-gaji', 'module' => 'Setting']);
        Permission::create(['name' => 'setting-lembur', 'module' => 'Setting']);
        Permission::create(['name' => 'libur-nasional', 'module' => 'Setting']);
        ## Permissions ABSENSI
        Permission::create(['name' => 'absensi-create', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-realtime', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-perorang', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-perlokasi', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-upload-schedule', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-data-schedule', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-rekap-absen', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-rekap-waktu', 'module' => 'Absensi']);
        Permission::create(['name' => 'absensi-datalokasi', 'module' => 'Absensi']);
        ## Permissions Monitoring
        Permission::create(['name' => 'monitoring-create', 'module' => 'Monitoring']);
        Permission::create(['name' => 'monitoring-realtime', 'module' => 'Monitoring']);
        Permission::create(['name' => 'monitoring-perorang', 'module' => 'Monitoring']);
        Permission::create(['name' => 'monitoring-rekap', 'module' => 'Monitoring']);
        ## Permissions TEST
        Permission::create(['name' => 'psikotest-input', 'module' => 'Psikotest']);
        Permission::create(['name' => 'psikotest-hasil', 'module' => 'Psikotest']);
        ## Permissions SIMC
        Permission::create(['name' => 'simc-sakit', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-approval-sakit', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-izin', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-approval-izin', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-cuti', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-approval-cuti', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-sisa-cuti', 'module' => 'SIMC']);
        Permission::create(['name' => 'simc-generate-cuti', 'module' => 'SIMC']);
        ## Permissions LEMBUR
        Permission::create(['name' => 'approval-lembur', 'module' => 'Lembur']);
        Permission::create(['name' => 'data-lembur', 'module' => 'Lembur']);
        Permission::create(['name' => 'upload-lembur', 'module' => 'Lembur']);
        Permission::create(['name' => 'download-lembur', 'module' => 'Lembur']);
        ## Permissions FLIGHT
        Permission::create(['name' => 'flight-upload', 'module' => 'Flight']);
        Permission::create(['name' => 'flight-data', 'module' => 'Flight']);
        Permission::create(['name' => 'flight-edit', 'module' => 'Flight']);
        ## Permissions FLIGHT HAJI
        Permission::create(['name' => 'flighthaji-upload', 'module' => 'Flight Haji']);
        Permission::create(['name' => 'flighthaji-data', 'module' => 'Flight Haji']);
        Permission::create(['name' => 'flighthaji-edit', 'module' => 'Flight Haji']);

        ## Roles
        Role::create(['name' => 'ADMIN']);
        Role::create(['name' => 'DIREKTUR UTAMA']);
        Role::create(['name' => 'KOMISARIS']);
        Role::create(['name' => 'GM OPERASIONAL']);
        Role::create(['name' => 'REGIONAL']);
        Role::create(['name' => 'PROJECT LEADER']);
        Role::create(['name' => 'HR PUSAT']);
        Role::create(['name' => 'HR TENAGAKERJA']);
        Role::create(['name' => 'RECRUITMENT']);
        Role::create(['name' => 'LEGAL']);
        Role::create(['name' => 'ADMIN BPJS']);

        ## Set Role Permissions
        $roleAdmin = Role::findByName('ADMIN');
        $roleAdmin->givePermissionTo('lihat-user');
        $roleAdmin->givePermissionTo('tambah-user');
        $roleAdmin->givePermissionTo('edit-user');

        $roleAdmin->givePermissionTo('lihat-role');
        $roleAdmin->givePermissionTo('tambah-role');
        $roleAdmin->givePermissionTo('edit-role');

        $roleAdmin->givePermissionTo('lihat-menu');
        $roleAdmin->givePermissionTo('tambah-menu');
        $roleAdmin->givePermissionTo('edit-menu');

        $roleAdmin->givePermissionTo('lihat-company');
        $roleAdmin->givePermissionTo('tambah-company');
        $roleAdmin->givePermissionTo('edit-company');

        $roleAdmin->givePermissionTo('lihat-project');
        $roleAdmin->givePermissionTo('tambah-project');
        $roleAdmin->givePermissionTo('edit-project');

        $roleAdmin->givePermissionTo('lihat-aksesproject');
        $roleAdmin->givePermissionTo('edit-aksesproject');

        $roleAdmin->givePermissionTo('import-karyawan');
        $roleAdmin->givePermissionTo('lihat-kontrakinduk');
        $roleAdmin->givePermissionTo('edit-kontrakinduk');
        $roleAdmin->givePermissionTo('tambah-kontrakinduk');
        $roleAdmin->givePermissionTo('hapus-kontrakinduk');

        # RECT
        $roleRect = Role::findByName('RECRUITMENT');
        $roleRect->givePermissionTo('lihat-lowongan');
        $roleRect->givePermissionTo('tambah-lowongan');
        $roleRect->givePermissionTo('edit-lowongan');
        $roleRect->givePermissionTo('hapus-lowongan');

        $roleRect->givePermissionTo('psikotest-input');
        $roleRect->givePermissionTo('psikotest-hasil');

        $roleRect->givePermissionTo('lihat-pelamar');
        $roleRect->givePermissionTo('lihat-pemanggilan');
        $roleRect->givePermissionTo('tambah-pemanggilan');
        $roleRect->givePermissionTo('edit-pemanggilan');
        # GM OPERASIONAL
        $roleGM = Role::findByName('GM OPERASIONAL');
        $roleGM->givePermissionTo('approve-banksdm');
        # LEGAL
        $roleLegal = Role::findByName('LEGAL');
        $roleLegal->givePermissionTo('cetak-pkwt');
        $roleLegal->givePermissionTo('edit-pkwt');
        $roleLegal->givePermissionTo('tambah-pkwt');
        $roleLegal->givePermissionTo('karyawan-aktif');
        $roleLegal->givePermissionTo('karyawan-keluar');
        $roleLegal->givePermissionTo('download-karyawan');
        $roleLegal->givePermissionTo('paklaring-karyawan');
        $roleLegal->givePermissionTo('approval-kontrakinduk');

        # HR PUSAT
        $rolePusat = Role::findByName('HR PUSAT');
        $rolePusat->givePermissionTo('karyawan-aktif');
        $rolePusat->givePermissionTo('karyawan-keluar');
        $rolePusat->givePermissionTo('approve-karyawan-in');
        $rolePusat->givePermissionTo('approve-karyawan-out');
        $rolePusat->givePermissionTo('edit-karyawan');
        $rolePusat->givePermissionTo('mutasi-karyawan');
        $rolePusat->givePermissionTo('approve-mutasi');
        $rolePusat->givePermissionTo('reqout-karyawan');
        $rolePusat->givePermissionTo('download-karyawan');
        $rolePusat->givePermissionTo('paklaring-karyawan');
        $rolePusat->givePermissionTo('edit-bank');
        $rolePusat->givePermissionTo('lihat-pelamar');
        $rolePusat->givePermissionTo('lihat-pemanggilan');
        $rolePusat->givePermissionTo('tambah-pemanggilan');
        $rolePusat->givePermissionTo('edit-pemanggilan');
        $rolePusat->givePermissionTo('banksdm');
        $rolePusat->givePermissionTo('approve-banksdm');
        $rolePusat->givePermissionTo('lihat-bpjs');
        $rolePusat->givePermissionTo('edit-bpjs');
        $rolePusat->givePermissionTo('tambah-bpjs');
        $rolePusat->givePermissionTo('setting-bpjs');
        $rolePusat->givePermissionTo('hapus-bpjs');
        $rolePusat->givePermissionTo('absensi-create');
        $rolePusat->givePermissionTo('absensi-realtime');
        $rolePusat->givePermissionTo('absensi-perorang');
        $rolePusat->givePermissionTo('absensi-perlokasi');
        $rolePusat->givePermissionTo('absensi-rekap-absen');
        $rolePusat->givePermissionTo('absensi-datalokasi');
        $rolePusat->givePermissionTo('monitoring-create');
        $rolePusat->givePermissionTo('monitoring-realtime');
        $rolePusat->givePermissionTo('monitoring-perorang');
        $rolePusat->givePermissionTo('monitoring-rekap');
        # HR TENAGAKERJA
        $roleTKO = Role::findByName('HR TENAGAKERJA');
        $roleTKO->givePermissionTo('karyawan-aktif');
        $roleTKO->givePermissionTo('karyawan-keluar');
        $roleTKO->givePermissionTo('approve-karyawan-in');
        $roleTKO->givePermissionTo('approve-karyawan-out');
        $roleTKO->givePermissionTo('edit-karyawan');
        $roleTKO->givePermissionTo('approve-mutasi');
        $roleTKO->givePermissionTo('download-karyawan');
        $roleTKO->givePermissionTo('edit-bank');
        $roleTKO->givePermissionTo('lihat-pelamar');
        # PROJECT LEADER
        $rolePL = Role::findByName('PROJECT LEADER');
        $rolePL->givePermissionTo('karyawan-aktif');
        $rolePL->givePermissionTo('karyawan-keluar');
        $rolePL->givePermissionTo('karyawan-reset');
        $rolePL->givePermissionTo('mutasi-karyawan');
        $rolePL->givePermissionTo('edit-karyawan');
        $rolePL->givePermissionTo('reqout-karyawan');
        $rolePL->givePermissionTo('download-karyawan');
        $rolePL->givePermissionTo('paklaring-karyawan');
        $rolePL->givePermissionTo('lihat-pelamar');
        $rolePL->givePermissionTo('lihat-pemanggilan');
        $rolePL->givePermissionTo('tambah-pemanggilan');
        $rolePL->givePermissionTo('edit-pemanggilan');
        $rolePL->givePermissionTo('banksdm');
        $rolePL->givePermissionTo('lihat-bpjs');
        $rolePL->givePermissionTo('setting-bpjs');
        $rolePL->givePermissionTo('monitoring-realtime');
        $rolePL->givePermissionTo('monitoring-create');
        $rolePL->givePermissionTo('monitoring-perorang');
        $rolePL->givePermissionTo('monitoring-rekap');
        # ADMIN BPJS
        $roleBpjs = Role::findByName('ADMIN BPJS');
        $roleBpjs->givePermissionTo('lihat-bpjs');
        $roleBpjs->givePermissionTo('edit-bpjs');
        $roleBpjs->givePermissionTo('tambah-bpjs');
        $roleBpjs->givePermissionTo('setting-bpjs');
        $roleBpjs->givePermissionTo('hapus-bpjs');
        $roleBpjs->givePermissionTo('karyawan-aktif');
        $roleBpjs->givePermissionTo('karyawan-keluar');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        ## Permissions System
        Permission::create(['name' => 'quota-manage', 'module' => 'Quota']);
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
        ## Permissions Project
        Permission::create(['name' => 'lihat-project', 'module' => 'Project']);
        Permission::create(['name' => 'tambah-project', 'module' => 'Project']);
        Permission::create(['name' => 'edit-project', 'module' => 'Project']);
        ## Permissions Jabatan
        Permission::create(['name' => 'lihat-jabatan', 'module' => 'Jabatan']);
        Permission::create(['name' => 'tambah-jabatan', 'module' => 'Jabatan']);
        Permission::create(['name' => 'edit-jabatan', 'module' => 'Jabatan']);
        Permission::create(['name' => 'hapus-jabatan', 'module' => 'Jabatan']);
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
        ## Permissions Akses Project
        Permission::create(['name' => 'lihat-aksesproject', 'module' => 'Akses Project']);
        Permission::create(['name' => 'edit-aksesproject', 'module' => 'Akses Project']);
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

        ## Roles
        Role::create(['name' => 'SUPER ADMINISTRATOR']);
        Role::create(['name' => 'ADMINISTRATOR']);
        Role::create(['name' => 'PENGGUNA']);
        Role::create(['name' => 'ADMIN BPJS']);

        ## Set Role Permissions
        $roleAdmin = Role::findByName('SUPER ADMINISTRATOR');
        $roleAdmin->syncPermissions(Permission::all());
    }
}

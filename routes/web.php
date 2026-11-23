<?php

use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Bpjs\BpjsController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Gaji\GajiController;
use App\Http\Controllers\Imports\MigrasiController;
use App\Http\Controllers\Jabatan\JabatanController;
use App\Http\Controllers\Karyawan\KaryawanController;
use App\Http\Controllers\Kontrakinduk\KontrakindukController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Monitoring\MonitoringController;
use App\Http\Controllers\Permintaan\PermintaanController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Persetujuan\PersetujuanController;
use App\Http\Controllers\Pkwt\PkwtController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Simc\SimcController;
use App\Http\Controllers\Super\SuperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'preventBackHistory'], function () {
    // Start preventBackHistory

    Route::controller(LoginController::class)
        ->group(function () {
            Route::get('login', 'login')->name('login');
            Route::post('actionlogin', 'actionlogin')->name('actionlogin');
            Route::get('forgot-password', 'forgot')->name('forgot');
            Route::post('forgot-password', 'send_forgot_password')->name('sendforgot');
            Route::get('/reset-password/{token}', 'reset_password')->middleware('guest')->name('password.reset');
            Route::post('reset-password', 'send_reset_password')->middleware('guest')->name('password.update');
            Route::get('page-not-found', function (Request $request) {
                return view('errors.404');
            })->middleware('guest')->name('password.notfound');
        });

    Route::post('/logout', [LoginController::class, 'logout'])->name('actionlogout')->middleware('auth');

    ## DASHBOARD
    Route::controller(DashboardController::class)
        ->middleware(['auth:web'])
        ->name('dashboard.')
        ->group(function () {
            Route::get('/', 'index')->name('home');
            Route::get('/active-session', 'activeSessions')->name('activeUser');
            Route::get('/stream-active-sessions', 'streamActiveSessions')->name('streamSession');
            Route::post('/force-logout', 'forceLogout')->name('forceOut');
        });

    ## SA
    Route::controller(SuperController::class)
        ->middleware('auth')
        ->name('sa.')
        ->group(function () {
            Route::get('pengguna', 'users')->name('users');
            Route::post('pengguna', 'store_user')->name('userstore');
        });

    ## PROFILE
    Route::controller(ProfileController::class)
        ->middleware('auth')
        ->name('profile.')
        ->group(function () {
            Route::get('profile', 'index')->name('index');
            Route::post('store-profile', 'store')->name('store');
            Route::post('store-password', 'store_password')->name('storepass');
            Route::post('store-image', 'store_image')->name('storeimage');
        });

    ## ADMIN
    Route::controller(AdminController::class)
        ->middleware('auth')
        ->name('admin.')
        ->group(function () {
            Route::get('pengguna/edit', 'edit_user')->name('edituser');
            Route::get('/pengguna/permissions/{token}', 'direct_permission')->name('tambahan');
            Route::post('/pengguna/direct', 'store_direct')->name('directstore');
            Route::get('/listroles', 'get_roles')->name('getroles');
            Route::get('getKontrak', 'get_kontrak')->name('kontrak');
            Route::get('statuskawin', 'statuskawin')->name('statuskawin');
            Route::post('statuskawin', 'store_statuskawin')->name('storekawin');
            Route::get('edit-statuskawin', 'edit_statuskawin')->name('editkawin');
            Route::post('destroy-statuskawin', 'destroy_statuskawin')->name('destroykawin');
            Route::get('bank', 'list_bank')->name('bank');
            Route::post('bank', 'store_bank')->name('storebank');
            Route::get('edit-bank', 'edit_bank')->name('editbank');
            Route::post('destroy-bank', 'destroy_bank')->name('destroybank');
            Route::get('ptko', 'ptko')->name('ptko');
            Route::post('ptko', 'store_ptko')->name('storeptko');
            Route::get('ptko/edit', 'edit_ptko')->name('editptko');
            Route::get('ptko/detail', 'detail_ptko')->name('detailptko');
            Route::post('ptko/batal', 'batal_ptko')->name('batalptko');
            Route::post('ptko/hrapps', 'proses_hrapps')->name('proseshrapps');
            Route::get('settinggaji', 'setting_gaji')->name('settinggaji');
            Route::post('settinggaji', 'store_settinggaji')->name('storeSettinggaji');
            Route::get('settinggaji/tambah', 'tambah_settinggaji')->name('tambahSettinggaji');
            Route::get('settinggaji/getKontrakinduk', 'liss_all_kontrakinduk')->name('listKontrakinduk');
            Route::get('settinggaji/setting-kontrakinduk', 'liss_setting_kontrakinduk')->name('setKontrakinduk');
            Route::get('settinggaji/getHarga', 'get_data_setting')->name('dataSetting');
            Route::get('settinggaji/lokasi', 'settinggaji_lokasi')->name('lokasiSettinggaji');
            Route::get('settinggaji/detail', 'settinggaji_detail')->name('detailSettinggaji');
            Route::post('settinggaji/hapus', 'settinggaji_hapus')->name('hapusSettinggaji');
            Route::get('settinglembur', 'setting_lembur')->name('settinglembur');
            Route::get('settinglembur/tambah', 'settinglembur_tambah')->name('addSettinglembur');
            Route::get('settinglembur/detail', 'settinglembur_detail')->name('editSettinglembur');
            Route::post('settinglembur/hapus', 'settinglembur_hapus')->name('hapusSettinglembur');
            Route::post('settinglembur', 'store_settinglembur')->name('storeSettinglembur');
        });

    ## MENU
    Route::controller(MenuController::class)
        ->middleware('auth')
        ->name('menu.')
        ->group(function () {
            Route::get('menus', 'index')->name('menus');
            Route::get('menus/akses', 'menu_akses')->name('menusakses');
            Route::get('menus/edit', 'edit_menu')->name('menuedit');
            Route::post('menus', 'store_menu')->name('menustore');
            Route::get('submenus', 'list_submenus')->name('submenus');
            Route::post('submenus', 'store_submenu')->name('submenustore');
            Route::get('submenus/menu', 'menu_submenus')->name('submenusmenu');
            Route::get('submenus/edit', 'edit_submenu')->name('submenuedit');
            Route::get('web-routes', 'getWebRoutes')->name('webRoute');
            Route::get('curent-submenu', 'getCurrentSubmenu')->name('submenucurrent');
        });

    ## PROJECT
    Route::controller(ProjectController::class)
        ->middleware('auth')
        ->name('project.')
        ->group(function () {
            Route::get('project', 'index')->name('project');
            Route::post('project', 'store_project')->name('projectstore');
            Route::get('/project/edit', 'edit_project')->name('projectedit');
            Route::get('/project/editsub', 'edit_subproject')->name('subprojectedit');
            Route::post('project/hapus', 'hapus_project')->name('hapus');
            Route::get('project/all', 'project_all')->name('projectall');
            Route::get('akses-projectuser', 'akses_user')->name('aksesuser');
            Route::get('kelola/aksesproject/{token}', 'kelola_aksesproject')->name('kelolaakses');
            Route::post('akses-projectuser', 'store_akses')->name('storeaksesuser');
            Route::get('setting-akses', 'akses_setting')->name('aksessetting');
            Route::get('setting-akses-child', 'akses_child')->name('akseschild');
            Route::get('akses-project', 'akses_project')->name('aksesproject');
            Route::get('list-aksesproject', 'list_aksesproject')->name('listakses');
            Route::get('lokasi-project', 'lokasi_project')->name('lokasiproject');
            Route::get('lokasi/ptko', 'lokasi_ptko_recursive')->name('lokasiptko');
            Route::get('lokasi/ptko', 'lokasi_ptko_recursive')->name('lokasiptko');
            Route::get('akses-project-tree', 'tree')->name('projectTree');
            Route::get('akses-project-kontrak', 'tree_kontrak')->name('projectKontrak');
        });

    ## KONTRAK INDUK
    Route::controller(KontrakindukController::class)
        ->middleware('auth')
        ->name('ki.')
        ->group(function () {
            Route::get('kontrakInduk', 'index')->name('kontrakinduk');
            Route::post('kontrakInduk', 'store')->name('store');
            Route::get('kontrakinduk/perpanjang', 'kontrakinduk_perpanjang')->name('perpanjang');
            Route::post('kontrakInduk-perpanjang', 'proses_perpanjang')->name('storePerpanjang');
            Route::get('kontrakInduk/tambah', 'kontrakinduk_tambah')->name('tambah');
            Route::get('kontrakinduk/edit', 'edit_kontrakinduk')->name('edit');
            Route::post('kontrakInduk/nonaktif', 'request_nonaktif')->name('reqNonaktif');
            Route::get('approval-kontrakinduk', 'list_approval')->name('listApprove');
            Route::get('approval-kontrakinduk/detail', 'showApprovalKontrakinduk')->name('detailApprove');
            Route::post('approval-kontrakinduk', 'proses_approval_kontrakinduk')->name('prosesApprove');
            Route::post('tolak-kontrakinduk', 'proses_reject_kontrakinduk')->name('tolakApprove');
            Route::post('approval-kontrakinduk-off', 'proses_approval_off_kontrakinduk')->name('prosesOff');
            Route::post('tolak-kontrakinduk-off', 'proses_reject_off_kontrakinduk')->name('tolakOff');
            Route::get('unitkerja-project', 'list_unitkerja_project')->name('listUnit');
            Route::get('adendum/tambah', 'adendum_tambah')->name('addAdendum');
            Route::post('adendum-request', 'adendum_request')->name('reqAdendum');
            Route::post('adendum-add-new', 'adendum_store_new')->name('storeNewAdendum');
            Route::post('adendum-reject', 'adendum_reject')->name('tolakAdendum');
            Route::get('adendum/edit', 'adendum_edit')->name('editAdendum');
        });

    ## ROLES
    Route::controller(RoleController::class)
        ->middleware('auth')
        ->name('role.')
        ->group(function () {
            Route::get('roles', 'index')->name('roles');
            Route::post('roles', 'store_roles')->name('rolesstore');
            Route::get('roles/kelola/{token?}', 'kelola_roles')->name('kelolaroles');
            Route::get('edit-roles', 'edit_roles')->name('editroles');
        });

    ## PERMISSIONS
    Route::controller(PermissionController::class)
        ->middleware('auth')
        ->name('permission.')
        ->group(function () {
            Route::get('permissions', 'index')->name('permissions');
            Route::post('permissions', 'store_permissions')->name('permissionsstore');
            Route::get('permissions/edit', 'permissions_edit')->name('permissionsedit');
            Route::get('permissions-groups', 'permissions_groups')->name('permissiongroups');
        });

    ## JABATAN
    Route::controller(JabatanController::class)
        ->middleware('auth')
        ->name('jabatan.')
        ->group(function () {
            Route::get('jabatan', 'index')->name('jabatan');
            Route::get('getJabatan', 'get_jabatan')->name('getJabatan');
            Route::post('jabatan', 'store')->name('storejabatan');
            Route::get('jabatan/edit', 'edit')->name('editjabatan');
            Route::post('jabatan/delete', 'destroy')->name('destroyjabatan');
            Route::get('unitkerja', 'list_unitkerja')->name('unitkerja');
            Route::get('edit-unitkerja', 'edit_unitkerja')->name('editunitkerja');
            Route::post('unitkerja', 'store_unitkerja')->name('storeunitkerja');
            Route::get('getUnitkerja', 'get_unitkerja')->name('getUnitkerja');
            Route::post('hapus-unitkerja', 'destroy_unitkerja')->name('destroyunitkerja');
            Route::get('tunjangan', 'tunjangan_list')->name('tunjangan');
            Route::post('tunjangan', 'tunjangan_store')->name('storetunjangan');
            Route::get('getTunjangan', 'get_tunjangan')->name('getTunjangan');
            Route::get('tunjangan/edit', 'tunjangan_edit')->name('edittunjangan');
            Route::post('tunjangan/delete', 'tunjangan_destroy')->name('hapustunjangan');
        });


    ## KARYAWAN
    Route::controller(KaryawanController::class)
        ->middleware('auth')
        ->name('karyawan.')
        ->group(function () {
            Route::get('karyawan-dashboard', 'dashboard')->name('dashboard');
            Route::get('karyawanaktif', 'index')->name('karyawanaktif');
            Route::get('karyawanaktif/recursive', 'lokasi_recursive')->name('lokasirecursive');
            Route::get('karyawan/detail', 'detail_karyawan')->name('detailkaryawan');
            Route::get('karyawanout', 'karyawan_keluar')->name('karyawanout');
            Route::get('getProvinsi', 'get_provinsi')->name('provinsi');
            Route::get('getKabKot', 'get_kabkot')->name('kabkot');
            Route::get('getKecamatan', 'get_kecamatan')->name('kecamatan');
            Route::get('getKelurahan', 'get_kelurahan')->name('kelurahan');
            Route::get('getStatusKawin', 'get_statusperkawinan')->name('getstatuskawin');
            Route::get('getBank', 'get_bank')->name('getbank');
            Route::post('update-karyawan', 'update_karyawan')->name('update');
            Route::get('download-karyawan', 'download_karyawan')->name('download');
            Route::post('download-karyawan', 'proses_download')->name('prosesdownload');
            Route::get('approvalIn', 'request_in')->name('reqin');
            Route::post('approvalIn', 'proses_approval')->name('storereqin');
            Route::post('approvalIn/batal', 'batal_request_in')->name('batalreqin');
            Route::get('approvalIn/approval/', 'approval_request_in')->name('approvalreqin');
            Route::get('approvalIn/kontrakinduk', 'cek_kontrakinduk')->name('cekKontrakinduk');
            Route::get('approvalIn/ptko', 'cek_ptko')->name('cekPtko');
            Route::get('getSettingGaji-project', 'getSettingGaji')->name('getGaji');
            Route::get('mutasi-karyawan', 'request_mutasi')->name('reqMutasi');
            Route::post('mutasi-karyawan', 'store_mutasikaryawan')->name('storeMutasi');
            Route::get('list-karyawan', 'list_karyawan')->name('listkaryawan');
            Route::get('approval-mutasi', 'approval_mutasi')->name('approveMutasi');
            Route::get('detail-mutasikaryawan', 'detail_mutasikaryawan')->name('detailApproveMutasi');
            Route::post('approval-mutasi', 'proses_approval_mutasi')->name('prosesAppMutasi');
            Route::post('batalkan-mutasi', 'proses_pembatalan_mutasi')->name('batalAppMutasi');
            Route::post('karyawan/paklaring', 'paklaring_karyawan')->name('paklaring');
            Route::get('get-penempatan', 'get_penempatan')->name('penempatan');
            Route::post('reset-device', 'reset_device')->name('resetDevice');
            Route::post('reset-password', 'reset_password')->name('resetPassword');
        });

    ## PKWT
    Route::controller(PkwtController::class)
        ->middleware('auth')
        ->name('pkwt.')
        ->group(function () {
            Route::get('pkwt', 'index')->name('index');
            Route::get('pkwt/detail', 'detail_pkwt')->name('detail');
            Route::get('/pkwt/cetak', 'proses_pkwt')->name('cetak');
            Route::get('/error-page', 'error_page')->name('error');
        });


    ## PERMISSIONS
    Route::controller(MigrasiController::class)
        ->middleware('auth')
        ->name('import.')
        ->group(function () {
            Route::get('import-karyawan', 'index')->name('karyawanimport');
            Route::post('project-company', 'get_project_company')->name('projectCompany');
            Route::post('template-import-karyawan', 'template_karyawan')->name('templateKaryawan');
            Route::post('import-karyawan', 'store_import_karyawan')->name('karyawan');
            // Route::post('import-karyawan', 'import_karyawan')->name('storekaryawan');
            Route::get('import/status/{logId}', 'status')->name('statusimport');
            Route::get('import/cek-import', 'activeImport')->name('activeimport');
            Route::post('import-keluarga', 'import_keluarga')->name('keluarga');
            Route::post('import-schedule', 'import_schedule')->name('schedule');
        });

    ## BPJS
    Route::controller(BpjsController::class)
        ->middleware('auth')
        ->name('bpjs.')
        ->group(function () {
            Route::get('bpjs', 'index')->name('view');
            Route::get('bpjs-dashboard-in', 'dashboard_in')->name('dashboardIn');
            Route::get('bpjs-dashboard-out', 'dashboard_out')->name('dashboardOut');
            Route::post('bpjs', 'store_bpjs')->name('store');
            Route::get('bpjs-detail', 'detail_bpjs')->name('detail');
            Route::get('bpjs-tambahan', 'detail_tambahan')->name('tambahan');
            Route::get('bpjs-tambahan/list', 'list_tambahan')->name('list');
            Route::post('bpjs-tambahan', 'store_tambahan')->name('storetambahan');
            Route::get('setting-bpjs', 'setting_bpjs')->name('setting');
            Route::get('setting-bpjs/detail', 'detail_setting_bpjs')->name('detailsetting');
            Route::get('setting-bpjs/create', 'new_setting_bpjs')->name('createSettingBpjs');
            Route::get('setting-bpjs/lokasi', 'setting_lokasi_project')->name('lokasiSettingBpjs');
            Route::post('setting-bpjs', 'store_setting_bpjs')->name('storesetting');
        });

    ## PERMINTAAN
    Route::controller(PermintaanController::class)
        ->middleware('auth')
        ->name('req.')
        ->group(function () {
            Route::get('requestOut', 'index')->name('view');
            Route::post('requestOut', 'store_out')->name('storeOut');
            Route::get('edit-requestout', 'edit_requestout')->name('edit');
            Route::post('hapus-requestout', 'hapus_requestout')->name('hapus');
        });

    ## PERSETUJUAN
    Route::controller(PersetujuanController::class)
        ->middleware('auth')
        ->name('approve.')
        ->group(function () {
            Route::get('approvalOut', 'index')->name('view');
            Route::post('approvalOut', 'store_out')->name('store');
        });

    ## ABSENSI
    Route::controller(AbsensiController::class)
        ->middleware('auth')
        ->name('absensi.')
        ->group(function () {
            Route::get('absensi', 'index')->name('realtime');
            Route::get('absensi-stream', 'stream')->name('streamAbsensi');
            Route::get('absensi-tambah', 'add_absensi')->name('add');
            Route::get('cek-absensi-person', 'cek_absensi_person')->name('cekPerson');
            Route::post('absensi-tambah', 'store_absensi')->name('storeAbsensi');
            Route::get('perorang', 'absensi_perorang')->name('perorang');
            Route::get('absensi-perorang', 'getAbsensi_perorang')->name('getPerorang');
            Route::post('perorang', 'download_perorang')->name('downloadPerorang');
            Route::get('perlokasi', 'absensi_perlokasi')->name('perlokasi');
            Route::get('rekap-kehadiran', 'rekap_kehadiran')->name('rekapKehadiran');
            Route::get('maps-lokasi-absensi', 'maps_lokasi_absensi')->name('mapsAbsensi');
            Route::get('lokasi-absensi', 'lokasi_absensi')->name('lokasiAbsensi');
            Route::get('generate-url-maps', 'generateUrlMaps')->name('generateUrl');
            Route::get('lokasi-absensi/tambah', 'add_lokasi_absensi')->name('tambahLokasiAbsensi');
            Route::get('lokasi-absensi/detail', 'detail_lokasi_absensi')->name('detailLokasiAbsensi');
            Route::post('lokasi-absensi', 'store_lokasi_absensi')->name('storelokasiAbsensi');
            Route::post('lokasi-absensi/status', 'store_status_web')->name('storeStatusWeb');
            Route::post('destroy-lokasi-absensi', 'hapus_lokasi_absensi')->name('destroylokasiAbsensi');
            Route::get('upload-schedule', 'upload_schedule')->name('uploadsSchedule');
            Route::get('data-schedule', 'data_schedule')->name('schedule');
            Route::post('data-schedule', 'proses_download_schedule')->name('downloadSchedule');
            Route::get('pergantian-schedule', 'pergantian_schedule')->name('pergantianSchedule');
            Route::get('penggati-schedule', 'pengganti_schedule')->name('pergantiSchedule');
            Route::post('pergantian-schedule', 'proses_pergantian_schedule')->name('ProsesPergantianSchedule');
            Route::get('detail-schedule', 'detail_schedule')->name('detailSchedule');
            Route::post('update-schedule', 'update_schedule')->name('updateSchedule');
            Route::post('template-upload', 'download_template_upload')->name('templateUpload');
            Route::post('absensi-upload-kehadiran', 'proses_upload_absensi')->name('uploadAbsensi');
            Route::get('rekap-waktu', 'rekap_waktu')->name('rekapWaktu');
        });

    ## MONITORING
    Route::controller(MonitoringController::class)
        ->middleware('auth')
        ->name('monitoring.')
        ->group(function () {
            Route::get('monitoring', 'index')->name('realtime');
            Route::get('monitoring-stream', 'stream')->name('streamMonitoring');
            Route::get('tambah-monitoring', 'add_monitoring')->name('add');
            Route::post('tambah-monitoring', 'store_monitoring')->name('store');
            Route::get('monitoring-perorang', 'perorang')->name('perorang');
            Route::post('monitoring-perorang', 'store_perorang')->name('storePerorang');
            Route::get('get-monitoring-perorang', 'get_perorang')->name('getPerorang');
            Route::get('rekap-monitoring', 'rekap_monitoring')->name('rekap');
            Route::post('rekap-monitoring', 'proses_rekap_monitoring')->name('prosesRekap');
        });

    ## SIMC
    Route::controller(SimcController::class)
        ->middleware('auth')
        ->name('simc.')
        ->group(function () {
            # SAKIT
            Route::get('sakit', 'index')->name('sakit');
            Route::get('sakit/tambah', 'tambah_sakit')->name('addSakit');
            Route::post('sakit/tambah', 'proses_tambah_sakit')->name('storeSakit');
            Route::get('sakit/approval', 'approval_sakit')->name('approveSakit');
            Route::get('sakit/approval-detail', 'detail_approval_sakit')->name('detailSakit');
            Route::post('sakit/approval', 'proses_approval_sakit')->name('prosesApproveSakit');
            Route::post('sakit-destroy', 'destroy_sakit')->name('destroySakit');
            Route::get('sakit-recycle', 'recycle_sakit')->name('recycleSakit');
            Route::post('rejct-simc', 'proses_reject_simc')->name('rejectSimc');
            # IZIN
            Route::get('izin', 'izin')->name('izin');
            Route::get('izin-tambah', 'tambah_izin')->name('addIzin');
            Route::post('izin-tambah', 'proses_tambah_izin')->name('storeIzin');
            Route::get('izin-approval', 'approval_izin')->name('approveIzin');
            Route::get('izin/approval-detail', 'detail_approval_izin')->name('detailIzin');
            Route::post('izin-approval', 'proses_approval_izin')->name('storeApproveIzin');
            Route::post('izin-destroy', 'destroy_izin')->name('destroyIzin');
            Route::get('izin-recycle', 'recycle_izin')->name('recycleIzin');
            # CUTI
            Route::get('cuti', 'cuti')->name('cuti');
            Route::get('cuti-tambah', 'tambah_cuti')->name('addCuti');
            Route::post('cuti-tambah', 'proses_tambah_cuti')->name('storeCuti');
            Route::post('cuti-destroy', 'destroy_cuti')->name('destroyCuti');
            Route::get('cuti-recycle', 'recycle_cuti')->name('recycleCuti');
            Route::get('cuti-approval', 'approval_cuti')->name('approvalCuti');
            Route::get('cuti-approval/detail', 'detail_approval_cuti')->name('detailApprovalCuti');
            Route::post('cuti-approval', 'proses_approval_cuti')->name('storeApprovalCuti');
            Route::get('sisacuti', 'sisa_cuti')->name('sisaCuti');
            Route::get('detail-recordcuti', 'detail_record_cuti')->name('detailRecordCuti');
            # Libur Nasional
            Route::get('libur-nasional', 'libur_nasional')->name('liburNasional');
            Route::post('libur-nasional', 'store_libur_nasional')->name('storeLiburNasional');
            Route::get('detail-liburnasioanal', 'detail_liburnasioanal')->name('detailLiburNasional');
            Route::post('destroy-liburnasional', 'destroy_liburnasional')->name('destroyLiburNasional');
        });


    ## GAJI
    Route::controller(GajiController::class)
        ->middleware('auth')
        ->name('gaji.')
        ->group(function () {
            Route::get('uploadgaji', 'uploadgaji')->name('uploads');
            Route::get('prosesgaji', 'prosesgaji')->name('proses');
            Route::get('pengajuan-gaji', 'pengajuan_gaji')->name('pengajuan');
            Route::get('slipgaji', 'slipgaji')->name('slip');
        });


    // End preventBackHistory
});

<?php

use App\Http\Controllers\Absensi\AbsensiController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Bpjs\BpjsController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Download\DownloadController;
use App\Http\Controllers\Flight\FlightController;
use App\Http\Controllers\Gaji\GajiController;
use App\Http\Controllers\Imports\MigrasiController;
use App\Http\Controllers\Jabatan\JabatanController;
use App\Http\Controllers\Karyawan\KaryawanController;
use App\Http\Controllers\Kontrakinduk\KontrakindukController;
use App\Http\Controllers\Login\LoginController;
use App\Http\Controllers\Lowongan\LowonganController;
use App\Http\Controllers\Menu\MenuController;
use App\Http\Controllers\Monitoring\MonitoringController;
use App\Http\Controllers\Notifikasi\NotifikasiController;
use App\Http\Controllers\Pelamar\PelamarController;
use App\Http\Controllers\Pemanggilan\PemanggilanController;
use App\Http\Controllers\Permintaan\PermintaanController;
use App\Http\Controllers\Permission\PermissionController;
use App\Http\Controllers\Persetujuan\PersetujuanController;
use App\Http\Controllers\Pkwt\PkwtController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Psikotest\PsikotestController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Simc\SimcController;
use App\Http\Controllers\Website\LayananController;
use App\Http\Controllers\Website\WebsiteController;
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
            Route::get('/belum-cetak-pkwt', 'cetak_pkwt')->name('cetakpkwt');
        });

    ## NOTIFIKASI
    Route::controller(NotifikasiController::class)
        ->middleware(['auth:web'])
        ->name('notif.')
        ->group(function () {
            Route::get('notif/read/{id}', 'asRead')->name('asRead');
            Route::post('notif/markAsRead', 'markAsRead')->name('markAsRead');
            Route::get('notif/markReadAll', 'markAllAsRead')->name('markAll');
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

    ## COMPANY
    Route::controller(CompanyController::class)
        ->middleware('auth')
        ->name('pt.')
        ->group(function () {
            Route::get('company', 'index')->name('index');
            Route::get('next-code-company', 'nextCompanyCode')->name('nextCode');
            Route::get('detail-company', 'detail_company')->name('edit');
            Route::post('company', 'store')->name('store');
            Route::get('get-perusahaan-all', 'get_perusahaan')->name('allCompany');
            Route::get('get-perusahaan-current', 'current_perusahaan')->name('currentCompany');
        });

    ## ADMIN
    Route::controller(AdminController::class)
        ->middleware('auth')
        ->name('admin.')
        ->group(function () {
            Route::get('pengguna', 'users')->name('pengguna');
            Route::post('pengguna', 'store_user')->name('userstore');
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

    ## PELAMAR
    Route::controller(PelamarController::class)
        ->middleware('auth')
        ->name('pelamar.')
        ->group(function () {
            Route::get('pelamar', 'index')->name('pelamar');
            Route::get('pelamar/history', 'riwayat_apply')->name('riwayat');
            Route::get('pelamar/detail', 'detail_pelamar')->name('detail');
            Route::get('ajax-keluarga', 'ajax_keluarga')->name('keluarga');
            Route::get('ajax-nonformal', 'ajax_pendidikan')->name('pendidikan');
            Route::get('ajax-paklaring', 'ajax_paklaring')->name('paklaring');
            Route::get('ajax-sertifikat', 'ajax_sertifikat')->name('sertifikat');
            Route::get('lampiran/view/{lampiran}', 'preview_paklaring')->name('lihatpaklaring');
        });

    ## LOWONGAN
    Route::controller(LowonganController::class)
        ->middleware('auth')
        ->name('lowongan.')
        ->group(function () {
            Route::get('lowongan', 'index')->name('lowongan');
            Route::post('lowongan', 'store')->name('store');
            Route::get('inputlowongan', 'input_lowongan')->name('input');
            Route::post('tutup-lowongan/{slug}', 'tutup_lowongan')->name('close');
            Route::get('edit-lowongan/{slug}', 'edit_lowongan')->name('editLowongan');
        });

    ## Pemanggilan

    Route::controller(PemanggilanController::class)
        ->middleware('auth')
        ->name('pemanggilan.')
        ->group(function () {
            Route::get('data-pemanggilan', 'index')->name('lihat');
            Route::get('pemanggilan/detail', 'detail_pemanggilan')->name('detailpanggil');
            Route::get('edit-tanggalpemanggilan', 'edit_tanggal_panggil')->name('editTanggal');
            Route::post('update-tanggalpemanggilan', 'update_tanggal_panggil')->name('updateTanggal');
            Route::post('data-pemanggilan/absensi', 'print_absensi')->name('pdfabsensi');
            Route::post('mulai-test', 'mulai_test')->name('mulaitest');
            Route::get('input-nilai', 'input_nilai')->name('inputnilai');
            Route::post('input-nilai', 'store_nilai')->name('storenilai');
            Route::post('kirim-user', 'kirim_user')->name('kirimuser');
            Route::get('pemanggilan', 'panggil_pelamar')->name('panggil');
            Route::post('pemanggilan', 'store_pemanggilan_pelamar')->name('storepanggil');
            Route::get('pemanggilan/invite', 'invite_pelamar')->name('invitepelamar');
            Route::post('pemanggilan/invite', 'store_invite')->name('storeinvite');
            Route::post('pemanggilan/hapus', 'destroy_invite')->name('destroyinvite');
            Route::get('banksdm', 'bank_sdm')->name('banksdm');
            Route::post('banksdm', 'store_banksdm')->name('storebanksdm');
            Route::get('banksdm/resend', 'bank_sdm_resend')->name('banksdmresend');
            Route::post('banksdm/resend', 'store_sdm_resend')->name('storesdmresend');
            Route::get('persetujuanuser', 'persetujuan_user')->name('persetujuanuser');
            Route::post('persetujuanuser', 'store_persetujuanuser')->name('storepersetujuan');
            Route::post('persetujuanuser-one', 'store_persetujuanuser_one')->name('storepersetujuanone');
        });

    Route::controller(PsikotestController::class)
        ->middleware('auth')
        ->name('test.')
        ->group(function () {
            Route::get('cfit-input', 'cfit_input')->name('cfitInput');
            Route::get('cfit-realtime', 'cfitRealtime')->name('cfitRealtime');
            Route::post('cfit-store', 'cfitStore')->name('cfitStore');
            Route::post('cetak-hasil-cfit', 'cetak_cfit')->name('cetakCfit');
            Route::get('mbti-input', 'mbti_input')->name('mbtiInput');
            Route::get('mbti-realtime', 'mbtiRealtime')->name('mbtiRealtime');
            Route::post('mbti-store', 'mbtiStore')->name('mbtiStore');
            Route::post('cetak-hasil-mbti', 'cetak_mbti')->name('cetakMbti');
            Route::get('papi-input', 'papi_input')->name('papiInput');
            Route::get('papi-realtime', 'papiRealtime')->name('papiRealtime');
            Route::post('papi-store', 'papiStore')->name('papiStore');
            Route::post('cetak-hasil-papi', 'cetak_papi')->name('cetakPapi');
            Route::get('disc-input', 'disc_input')->name('discInput');
            Route::get('disc-realtime', 'discRealtime')->name('discRealtime');
            Route::post('disc-store', 'discStore')->name('discStore');
            Route::post('cetak-hasil-disc', 'cetak_disc')->name('cetakDisc');
            Route::get('wawancara', 'wawancara')->name('wawancara');
            Route::post('wawancara', 'store_wawancara')->name('storeWawancara');
            Route::post('cetak-hasil-wawancara', 'cetak_wawancara')->name('cetakWawancara');
            Route::get('data-psikotest', 'data_psikotest')->name('hasil');
            Route::get('create-analisis', 'create_analisis')->name('createAnalisis');
            Route::post('create-analisis', 'store_analisis')->name('storeAnalisis');
            Route::post('cetak-hasil-analisis', 'cetak_analisis')->name('cetakAnalisis');
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

    ## WEBSITE
    Route::controller(WebsiteController::class)
        ->middleware('auth')
        ->name('web.')
        ->group(function () {
            Route::get('slider', 'slider')->name('slider');
            Route::get('tambah-slider', 'slider_tambah')->name('tambahslider');
            Route::post('slider', 'slider_store')->name('storeslider');
            Route::get('detail-slider/{slug}', 'slider_detail')->name('detaillider');
            Route::get('kontak', 'kontak')->name('kontak');
            Route::post('kontak', 'store_kontak')->name('storekontak');
            Route::get('kontak-detail/{slug}', 'detail_kontak')->name('detailkontak');
            Route::get('tambah-kontak', 'tambah_kontak')->name('addkontak');
            Route::get('visi-misi', 'visi_misi')->name('visimisi');
            Route::post('visi-misi', 'store_visi_misi')->name('storevisimisi');
            // KLIEN
            Route::get('klien', 'klien')->name('klien');
            Route::post('klien', 'store_klien')->name('storeklien');
            Route::post('hapus-klien/{slug}', 'destroy_klien')->name('destroyklien');
        });

    Route::controller(LayananController::class)
        ->middleware('auth')
        ->name('layanan.')
        ->group(function () {
            // TENTANG
            Route::get('tentang', 'index')->name('index');
            Route::get('edit-tentang/{slug}', 'tentang_detail')->name('detailtentang');
            Route::post('tentang', 'tentang_store')->name('storetentang');
            // TIM
            Route::get('tim-kami', 'tim_kami')->name('tim');
            Route::post('tim-kami', 'tim_kami_store')->name('storetim');
            Route::get('get-tim-kami', 'get_data_tim')->name('gettim');
            Route::get('add-tim', 'detail_tim_sementara')->name('detailtimsementara');
            Route::post('add-tim', 'tampung_tim_kami')->name('storetampung');
            Route::post('hapus-tim', 'hapus_tim_sementara')->name('hapustimsementara');
            // NILAI PERUSAHAAN
            Route::get('nilai-korporasi', 'nilai_korporasi')->name('nilai');
            Route::post('nilai-korporasi', 'store_nilai_korporasi')->name('storenilai');
            Route::get('data-nilai-korporasi', 'get_nilai_korporasi')->name('datanilai');
            Route::get('tambah-nilai', 'detail_nilai_tampung')->name('detailtampung');
            Route::post('tambah-nilai', 'tampung_nilai_korporasi')->name('tampungnilai');
            Route::post('hapus-nilai-tampung', 'hapus_nilai_tampung')->name('hapusnilai');
            // LEGALITAS
            Route::get('legalitas', 'legalitas')->name('legalitas');
            Route::post('legalitas', 'store_legalitas')->name('storelegalitas');
            Route::get('legalitas-detail', 'detail_legalitas')->name('detaillegalitas');
            Route::post('legalitas-hapus', 'hapus_legalitas')->name('deletelegalitas');
            // PORTOFOLIO
            Route::get('portofolio', 'portofolio')->name('portofolio');
            Route::post('portofolio', 'store_portofolio')->name('storeportofolio');
            Route::get('portofolio-detail', 'detail_portofolio')->name('detailportofolio');
            Route::post('portofolio-hapus', 'hapus_portofolio')->name('hapusportofolio');
            // LAYANAN
            Route::get('layanan', 'layanan')->name('layanan');
            Route::post('layanan', 'store_layanan')->name('storelayanan');
            Route::get('layanan-detail/{slug}', 'detail_layanan')->name('detaillayanan');
            Route::post('layanan-hapus/{slug}', 'hapus_layanan')->name('destroylayanan');
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

    ## DOWNLOAD
    Route::controller(DownloadController::class)
        ->middleware('auth')
        ->name('download.')
        ->group(function () {
            Route::get('download-status/{jobId}', 'status')->name('status');
            Route::get('download-file/{jobId}', 'download_file')->name('downloadFile');
            Route::post('perlokasi', 'rekap_perlokasi')->name('rekapPerlokasi');
            Route::post('rekap-waktu', 'rekap_waktu')->name('rekapWaktu');
            Route::post('rekap-kehadiran', 'rekap_kehadiran')->name('rekapKehadiran');
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
    ## FLIGHT
    Route::controller(FlightController::class)
        ->middleware('auth')
        ->name('flight.')
        ->group(function () {
            # Flight
            Route::get('upload-flight', 'uploads')->name('uploads');
            Route::post('upload-flight', 'store_upload_flight')->name('uploadFlight');
            Route::get('data-flight', 'index')->name('dataFlight');
            Route::get('detail-operator', 'detail_operator')->name('detailOperator');
            Route::get('detail-flight', 'detail_flight')->name('detailFlight');
            Route::post('regflight', 'store_regflight')->name('storeRegFlight');
            Route::post('time-flight', 'store_timeflight')->name('storeTimeFlight');
            Route::post('store-flight', 'store_flight')->name('storeFlight');
            Route::get('detail-layanan', 'detail_layanan')->name('detailLayanan');
            Route::post('store-layanan', 'store_layanan')->name('storeLayanan');
            Route::post('update-layanan', 'update_layanan')->name('updateLayanan');
            Route::post('destroy-layanan', 'destroy_layanan')->name('destroyLayanan');
            Route::post('destroy-flight', 'destroy_flight')->name('destroyFlight');
            Route::post('destroy-operator', 'destroy_operator')->name('destroyOperator');
            Route::get('getDataLampiran', 'detail_lampiran')->name('detailLampiran');
            Route::post('download-flight', 'download_flight')->name('downloadFlight');
            # Haji
            Route::post('upload-flighthaji', 'store_upload_flighthaji')->name('uploadFlighthaji');
            Route::get('data-flighthaji', 'data_flighthaji')->name('dataFlighthaji');
        });

    // End preventBackHistory
});

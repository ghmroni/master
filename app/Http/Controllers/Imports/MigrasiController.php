<?php

namespace App\Http\Controllers\Imports;

use App\Http\Controllers\Controller;
use App\Jobs\MigrasiKaryawanJob;
use App\Jobs\MigrasiKeluargaJob;
use App\Jobs\ScheduleJob;
use App\Models\Companies;
use App\Models\CutiBersama;
use App\Models\GradeGolongan;
use App\Models\ImportLog;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\KontrakIndukBiaya;
use App\Models\Pelamar;
use App\Models\Project;
use App\Models\SimcSaldo;
use App\Models\StatusKontrak;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Middleware\PermissionMiddleware;

class MigrasiController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using('import-karyawan'), only: ['store_import_karyawan']),
        ];
    }

    public function index(Request $request)
    {
        $companies = Companies::whereNull('deleted_at')
            ->orderBy('name', 'ASC')
            ->get();

        // Tambahkan token ke setiap item
        $companies->transform(function ($company) {
            $company->token_id = Crypt::encryptString($company->id);
            return $company;
        });

        return view('import.karyawan-import', [
            'judul' => 'Import Karyawan'
        ], compact('companies'));
    }

    public function get_project_company(Request $request)
    {
        try {
            $companyId = Crypt::decryptString($request->token);
            $projects = Project::whereNull('deleted_at')
                ->whereNull('parent_id')
                ->where('pt_id', $companyId)
                ->orderBy('name', 'ASC')
                ->get();
            return response()->json($projects);
        } catch (\Exception $e) {
            dd('Token tidak valid atau rusak');
        }
    }

    public function template_karyawan(Request $request)
    {
        $companyId = Crypt::decryptString($request->ptid);
        $projectId = $request->projectid;
        dd($companyId);
    }

    public function store_import_karyawan(Request $request)
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();

        if (!str_contains($originalName, 'import_karyawan')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nama file tidak sesuai ketentuan!'
            ], 400);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        # Ambil sheet ke-2 (index 1 karena 0 = sheet pertama)
        $sheet = $spreadsheet->getSheet(1);

        # Cek apakah ada data mulai row 2
        $highestRow = $sheet->getHighestRow();
        $headerRow = 1;
        $highestColumn = $sheet->getHighestColumn($headerRow);
        $headerData = $sheet->rangeToArray("A{$headerRow}:{$highestColumn}{$headerRow}", null, true, true, true);

        if ($highestRow <= 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Import Tidak Ditemukan.'
            ], 400);
        }

        if ($highestRow > 101) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Import tidak boleh lebih dari 100 baris.'
            ], 400);
        }

        $companyId = Crypt::decryptString($request->pt);
        $rootId = (int) trim((string) $request->project);
        $projects = Project::find($rootId)
            ->descendantsAndSelf()
            ->with(['pt:id,code'])
            ->get();

        $projectByName = $projects->keyBy('name');

        $unitKerjaIds = KontrakIndukBiaya::whereHas('settingHarga', function ($q) use ($rootId) {
            $q->whereRaw('FIND_IN_SET(?, project_detail)', [$rootId])
                ->whereHas('kontrakinduk', function ($k) {
                    $k->whereNull('deleted_at');
                });
        })
            ->distinct()
            ->pluck('unitkerja_id')
            ->filter()
            ->values()
            ->toArray();

        $jabatans = Jabatan::pluck('id', 'name');
        $kontraks = StatusKontrak::pluck('id', 'name');

        try {
            DB::beginTransaction();
            for ($row = 2; $row <= $highestRow; $row++) {
                $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, true);
                $rowValues = $rowData[$row];

                $jabatanId = $jabatans[trim($rowValues['H'])] ?? null;
                $kontrakId = $kontraks[trim($rowValues['AL'])] ?? null;
                $statuskontrak = StatusKontrak::findOrFail($kontrakId, ['id', 'inisial']);
                ## Lokasi Penempatan
                $lokasi = trim($rowValues['F']);
                $project = $projectByName[$lokasi] ?? null;

                if (!$project) {
                    throw new Exception("Lokasi Penempatan '{$lokasi}' pada baris {$row} tidak ditemukan");
                }
                # Unitkerja
                $rawUnitKerja = trim((string) ($rowValues['G'] ?? ''));
                $unitKerjaId = (int) $rawUnitKerja;

                if ($rawUnitKerja === '' || !in_array($unitKerjaId, $unitKerjaIds, true)) {
                    throw new Exception("ID Unit Kerja '{$rawUnitKerja}' pada baris {$row} tidak valid atau tidak terdaftar di project ini");
                }

                ## Penentuan NIP
                $tglkontrak = Carbon::parse(trim($rowValues['AN']));
                $month = $tglkontrak->format('m');
                $years = $tglkontrak->year;

                $tgllahir = trim($rowValues['K']);
                $tglmasuk = trim($rowValues['AF']);
                $lahir = Carbon::parse($tgllahir);

                $lastUrut = Karyawan::whereRaw('UPPER(nama) = ?', [strtoupper(trim($rowValues['D']))]) // Nama
                    ->whereDate('tanggal_lahir', $tgllahir)
                    ->whereMonth('tanggal_masuk', Carbon::parse($tglmasuk)->month)
                    ->whereYear('tanggal_masuk', Carbon::parse($tglmasuk)->year)
                    ->lockForUpdate()
                    ->max(DB::raw('RIGHT(nip, 2)'));

                $urut = ($lastUrut ?? 0) + 1;

                do {
                    $nourut = str_pad($urut, 2, '0', STR_PAD_LEFT);

                    // NIP AAA2611199910101 (16 digit)
                    $nip = $projects->first()->pt->code // code PT 1 digit : A
                        . $projects->first()->code // code project 2 digit : AA
                        . $tglkontrak->format('y') // tahun kontrak 2 digit : 26
                        . $month // bulan kontrak 2 digit : 11
                        . $lahir->year // tahun lahir 4 digit 1999
                        . $lahir->format('m') // bulan lahir 2 digit : 10
                        . (trim($rowValues['L']) == "L" ? "1" : "2") // kelamin 1 digit : 1
                        . $nourut; // Nomor urut 2 digit : 01

                    $urut++;
                } while (Karyawan::where('nip', $nip)->exists());


                ## =============================================================================================================== PELAMAR
                $dtpelamar = array(
                    'nama'               => trim($rowValues['D']),
                    'nokkk'              => trim($rowValues['B']),
                    'noktp'              => trim($rowValues['C']),
                    'tmptlahir'          => trim(strtoupper($rowValues['J'])),
                    'tgllahir'           => Carbon::parse($rowValues['K'])->format('Y-m-d'),
                    'jenis_kelamin'      => trim(strtoupper($rowValues['L'])),
                    'email'              => trim(strtolower($rowValues['W'])),
                    'password'           => bcrypt('123456Aa'),
                    'handphone'          => trim(str_replace(['-', ' '], '', $rowValues['V'])),
                    'agama'              => trim(strtoupper($rowValues['N'])),
                    'status_kawin'       => trim($rowValues['M']),
                    'tinggi_badan'       => trim($rowValues['AG']),
                    'berat_badan'        => trim($rowValues['AH']),
                    'ukuran_baju'        => trim(strtoupper($rowValues['AI'])),
                    'nomor_celana'       => trim($rowValues['AJ']),
                    'nomor_sepatu'       => trim($rowValues['AK']),
                    'alamat_ktp'         => trim(strtoupper($rowValues['T'])),
                    'provinsi_ktp'       => trim($rowValues['O']),
                    'kabkot_ktp'         => trim($rowValues['P']),
                    'kecamatan_ktp'      => trim($rowValues['Q']),
                    'kelurahan_ktp'      => trim($rowValues['R']),
                    'kodepos_ktp'        => trim($rowValues['S']),
                    'alamat_domisili'    => trim(strtoupper($rowValues['U'])),
                    'provinsi_domisili'  => trim($rowValues['O']),
                    'kabkot_domisili'    => trim($rowValues['P']),
                    'kecamatan_domisili' => trim($rowValues['Q']),
                    'kelurahan_domisili' => trim($rowValues['R']),
                    'kodepos_domisili'   => trim($rowValues['S']),
                    'jenjang_pendidikan' => trim(strtoupper($rowValues['X'])),
                    'nama_sekolah'       => trim(strtoupper($rowValues['Y'])),
                    'jurusan_sekolah'    => trim(strtoupper($rowValues['Z'])),
                    'tahun_lulus'        => trim(strtoupper($rowValues['AA'])),
                    'nilai_ijazah'       => trim(strtoupper($rowValues['AB'])),
                    'status'             => 'COMPLETE',
                    'register'           => 'registered',
                );

                $idpel = Pelamar::create($dtpelamar)->id_pelamar;

                ## =============================================================================================================== KARYAWAN

                $dtkaryawan = array(
                    'pelamar_id'      => $idpel,
                    'pt_id'           => $companyId,
                    'nip'             => $nip,
                    'kk'              => trim($rowValues['B']),
                    'ktp'             => trim($rowValues['C']),
                    'nama'            => trim(strtoupper($rowValues['D'])),
                    'email'           => trim(strtolower($rowValues['W'])),
                    'tempat_lahir'    => trim(strtoupper($rowValues['J'])),
                    'tanggal_lahir'   => Carbon::parse($rowValues['K'])->format('Y-m-d'),
                    'alamat_ktp'      => trim(strtoupper($rowValues['T'])),
                    'alamat_domisili' => trim(strtoupper($rowValues['U'])),
                    'provinsi_id'     => trim($rowValues['O']),
                    'kabkot_id'       => trim($rowValues['P']),
                    'kecamatan_id'    => trim($rowValues['Q']),
                    'kelurahan_id'    => trim($rowValues['R']),
                    'kodepos'         => trim($rowValues['S']),
                    'statuskawin_id'  => trim($rowValues['M']),
                    'kelamin'         => trim(strtoupper($rowValues['L'])),
                    'agama'           => trim(strtoupper($rowValues['N'])),
                    'handphone'       => trim(str_replace(['-', ' '], '', $rowValues['V'])),
                    'project'         => $project->id,
                    'project_detail'  => str_replace('.', ',', $project->path),
                    'jabatan'         => $jabatanId,
                    'unitkerja'       => $unitKerjaId,
                    'ukuranbaju'      => trim(strtoupper($rowValues['AI'])),
                    'ukurancelana'    => trim($rowValues['AJ']),
                    'tinggibadan'     => trim($rowValues['AG']),
                    'beratbadan'      => trim($rowValues['AH']),
                    'nomorsepatu'     => trim($rowValues['AK']),
                    'pendidikan'      => trim($rowValues['X']),
                    'namasekolah'     => trim(strtoupper($rowValues['Y'])),
                    'jurusan'         => trim($rowValues['Z']),
                    'tahunlulus'      => trim($rowValues['AA']),
                    'nilai'           => trim($rowValues['AB']),
                    'bank_id'         => trim($rowValues['AC']),
                    'namabank'        => trim(strtoupper($rowValues['AE'])),
                    'norek'           => trim($rowValues['AD']),
                    'percepatan'      => (trim($rowValues['AC']) ? false : true),
                    'tanggal_masuk'   => Carbon::parse($rowValues['AF'])->format('Y-m-d'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                );

                $idkar = Karyawan::create($dtkaryawan)->id;
                ## =============================================================================================================== SALDO CUTI
                $karyawan  = Karyawan::findOrFail($idkar);
                $masaKerja = $karyawan->tanggal_masuk->diffInMonths(now());

                // hitung jumlah cuti bersama tahun berjalan
                $cutiBersama = CutiBersama::where('tahun', date('Y'))
                    ->whereNull('deleted_at') // kalau pakai soft delete
                    ->count();

                // kalau project_detail mengandung root 1 → abaikan cuti bersama
                $projectIds = explode(',', $karyawan->project_detail);
                if (in_array(1, $projectIds)) {
                    $cutiBersama = 0;
                }

                SimcSaldo::updateOrCreate(
                    [
                        'karyawan_id' => $idkar,
                        'tahun'       => date('Y'),
                    ],
                    [
                        'saldo'            => $masaKerja < 12 ? 0 : (12 - $cutiBersama),
                        'saldo_sebelumnya' => 0,
                        'saldo_expired'    => now()->endOfYear(),
                        'saldo_terpakai'   => 0,
                    ]
                );

                ## =============================================================================================================== KONTRAK
                $romawi = [
                    '01' => 'I',
                    '02' => 'II',
                    '03' => 'III',
                    '04' => 'IV',
                    '05' => 'V',
                    '06' => 'VI',
                    '07' => 'VII',
                    '08' => 'VIII',
                    '09' => 'IX',
                    '10' => 'X',
                    '11' => 'XI',
                    '12' => 'XII',
                ];

                // format id jadi 5 digit
                $idkarFormatted = str_pad($idkar, 5, '0', STR_PAD_LEFT);

                $nokontrak = sprintf(
                    '%s/GHM%s/%s/%s/%s',
                    $idkarFormatted,                       // %s pertama
                    $kontrakId == 5 ? '' : '-' . $project->inisial, // %s kedua
                    $romawi[$month],                       // %s ketiga
                    $years,                                // %s keempat
                    $kontrakId == 2 ? 'K' . trim($rowValues['AP']) : $statuskontrak->inisial                // %s kelima
                );

                Kontrak::create([
                    'karyawan_id'      => $idkar,
                    'statuskontrak_id' => $kontrakId,
                    'nomor_kontrak'    => $nokontrak,
                    'kontrak_dari'     => Carbon::parse(trim($rowValues['AN']))->format('Y-m-d'),
                    'kontrak_sampai'   => (trim($rowValues['AO']) == '0000-00-00' ? null : Carbon::parse(trim($rowValues['AO']))->format('Y-m-d')),
                    'kontrak_ke'       => trim($rowValues['AP']),
                    'lokasi_kontrak'   => trim($rowValues['AQ']),
                    'tanggal_kontrak'  => Carbon::parse(trim($rowValues['AM']))->format('Y-m-d'),
                    'tanggal_ptko'     => Carbon::parse(trim($rowValues['AM']))->format('Y-m-d'),
                    'keterangan_ptko'  => 'Mutasi DB Baru',
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                ## ===============================================================================================================  GRADE GOLONGAN
                GradeGolongan::create([
                    'karyawan_id'   => $idkar,
                    'grade'         => ($row['grade'] ?? null),
                    'tunj_grade'    => 0,
                    'golongan'      => 0,
                    'tunj_golongan' => 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Migrasi Karyawan Berhasil Diupload.'
            ], 200);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function import_karyawan(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:xls'
            ],
            [
                'file.required' => 'File tidak ditemukan',
                'file.mimes' => 'Format file harus .xls',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            # Protek nama file
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            if ($originalName != 'import_karyawan.xls') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File salah! Pastikan Anda mengunggah file untuk migrasi karyawan dengan nama "import_karyawan".'
                ], 422);
            }

            $path = $request->file('file')->store('imports');
            # Create ke tabel import_log untuk progressbar / notifikasi proses import
            $log = ImportLog::create([
                'filename' => $path,
                'status'   => 'pending',
                'user_id'  => auth()->id(),
                'progress' => 0,
                'title'  => 'Migrasi Karyawan'
            ]);

            # Larikan ke Job atau Queue di App/Jobs
            MigrasiKaryawanJob::dispatch($log->id);
            // Excel::import(new KaryawanImport($log), storage_path('app/private/' . $log->filename));
            return response()->json([
                'log_id' => $log->id,
                'message' => 'File sedang proses upload.'
            ], 200);
        }
    }

    public function import_keluarga(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:xls'
            ],
            [
                'file.required' => 'File tidak ditemukan',
                'file.mimes' => 'Format file harus .xls',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            # Protek nama file
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            if ($originalName != 'import_keluarga.xls') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File salah! Pastikan Anda mengunggah file untuk migrasi keluarga dengan nama "import_keluarga".'
                ], 422);
            }

            $path = $request->file('file')->store('imports');
            # Create ke tabel import_log untuk progressbar / notifikasi proses import
            $log = ImportLog::create([
                'filename' => $path,
                'status'   => 'pending',
                'user_id'  => auth()->id(),
                'progress' => 0,
                'title'  => 'Migrasi Keluarga'
            ]);
            # Larikan ke Job atau Queue di App/Jobs
            MigrasiKeluargaJob::dispatch($log->id);
            // Excel::import(new MigrasiKeluargaImport($log), storage_path('app/private/' . $log->filename));
            return response()->json([
                'log_id' => $log->id,
                'message' => 'File sedang proses upload.'
            ], 200);
        }
    }

    public function import_schedule(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|mimes:xls',
                'periode' => 'required'
            ],
            [
                'file.required' => 'File tidak ditemukan',
                'file.mimes' => 'Format file harus .xls',
                'periode.required' => 'Periode schedule masih kosong',
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->messages()->first(),
            ], 405);
        } else {
            # Protek nama file
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();

            if ($originalName != 'template_upload_schedule.xls') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File salah! Pastikan Anda mengunggah file untuk upload schedule dengan nama "template_upload_schedule".'
                ], 422);
            }

            $periode = $request->input('periode');
            $tahun   = (int) date('Y', strtotime($periode));
            $bulan   = (int) date('m', strtotime($periode));

            $path = $request->file('file')->store('imports');
            # Create ke tabel import_log untuk progressbar / notifikasi proses import
            $log = ImportLog::create([
                'filename' => $path,
                'status'   => 'pending',
                'user_id'  => auth()->id(),
                'progress' => 0,
                'title'  => 'Import Schedule'
            ]);

            # Larikan ke Job atau Queue di App/Jobs
            ScheduleJob::dispatch($log->id, $bulan, $tahun);
            // Excel::import(new ScheduleImport($log, $bulan, $tahun), storage_path('app/private/' . $log->filename));
            return response()->json([
                'log_id' => $log->id,
                'message' => 'File sedang proses upload.'
            ], 200);
        }
    }

    public function status($logId)
    {
        $log = ImportLog::find($logId);
        return response()->json([
            'status'   => $log->status,
            'progress' => $log->progress,
            'title'    => $log->title,
            'message'  => $log->message,
        ]);
    }

    public function activeImport()
    {
        $log = ImportLog::whereIn('status', ['pending', 'progress', 'processing'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
        // ->first();

        return response()->json($log);
    }
}

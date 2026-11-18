<?php

namespace App\Imports;

use App\Models\Bank;
use App\Models\GradeGolongan;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\Pelamar;
use App\Models\Project;
use App\Models\SimcSaldo;
use App\Models\StatusKontrak;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterChunk;

class MigrasiKaryawanImport implements ToCollection, WithHeadingRow, WithChunkReading, WithEvents
{
    /**
     * @param Collection $collection
     */
    protected $log;
    protected $totalRows;
    protected $chunkSize;
    protected $processedChunks = 0;

    public $timeout = 600;

    public function __construct($log, $totalRows = null)
    {
        $this->log = $log;
        $this->totalRows = $totalRows;
        $this->chunkSize = $this->chunkSize();
    }

    public function registerEvents(): array
    {
        return [
            AfterChunk::class => function (AfterChunk $event) {
                $this->processedChunks++;

                $totalChunks = ceil($this->totalRows / $this->chunkSize);
                $progress = round(($this->processedChunks / $totalChunks) * 100);

                // update progress ke log / database
                $this->log->update([
                    'progress' => $progress,
                    'message'  => "Memproses chunk ke-{$this->processedChunks}..."
                ]);
            },
        ];
    }

    public function collection(Collection $rows)
    {
        # CEK JIKA FILE KOSONG
        if ($rows->isEmpty()) {
            throw ValidationException::withMessages(['file' => 'File Excel tidak memiliki data untuk diimpor.']);
        }

        // 2. Cek baris pertama apakah kolom NIP-nya kosong (Baris 2 di Excel)
        // $firstRow = $rows->first();
        // if (empty($firstRow['noktp'])) {
        //     throw ValidationException::withMessages(['file' => 'Baris pertama data (Baris 2) tidak memiliki NIP.']);
        // }
        $jabatans = Jabatan::pluck('id', 'name');
        $banks    = Bank::pluck('id', 'name');
        $kontraks = StatusKontrak::pluck('id', 'name');

        // 1. Ambil semua data dan buat mapping unik: "nama|parent_id" => id
        $projectMap = Project::all()
            ->groupBy(fn($item) => $item->name . '|' . ($item->parent_id ?? 'root'))
            ->map(fn($group) => $group->first()->id)
            ->toArray();

        foreach ($rows as $row) {
            #project
            // 2. Cari Bagian (Level 1)
            $idbagian = $projectMap[$row['nm_bagian'] . '|root'] ?? null;
            // 3. Cari Area (Level 2) - Menggunakan idbagian sebagai parent
            $idarea = $idbagian ? ($projectMap[$row['nm_bagiansub'] . '|' . $idbagian] ?? null) : null;
            // 4. Cari Lokasi (Level 3) - Menggunakan idarea sebagai parent
            $idlokasi = $idarea ? ($projectMap[$row['nm_bagiansubsub'] . '|' . $idarea] ?? null) : null;

            $ids = [$idbagian, $idarea, $idlokasi];
            $projectDetail = implode(',', $ids);


            $jabatanId = $jabatans[trim($row['nm_jabatan'])] ?? null;
            $bankId    = $banks[trim($row['nmbank'])] ?? null;
            $kontrakId = $kontraks[trim($row['nm_statuskontrak'])] ?? null;

            #pendidikan
            if ($row['jenis_pendidikan'] == "SLTP") {
                $pendidikan = "SMP";
            } else if ($row['jenis_pendidikan'] == "SMU" || $row['jenis_pendidikan'] == "SMK") {
                $pendidikan = "SMA";
            } else if ($row['jenis_pendidikan'] == "" || $row['jenis_pendidikan'] == null) {
                $pendidikan = "SD";
            } else {
                $pendidikan = $row['jenis_pendidikan'];
            }

            $namasekolah = trim($row[strtolower('nm_' . $pendidikan)]);
            $jurusan = $row[strtolower('jurusan_' . $pendidikan)];
            $tahunlulus = $row[strtolower('thnlulus_' . $pendidikan)];
            $nilaisekolah = $row[strtolower('nilai_' . $pendidikan)];

            ## Proses Perancangan NIP Karyawan
            $detailProjectId = explode(',', $projectDetail);
            $project = Project::findOrFail($detailProjectId[0], ['id', 'code', 'inisial']);
            $statuskontrak = StatusKontrak::findOrFail($kontrakId, ['id', 'inisial']);
            ## parsing tanggal dengan Carbon
            $tgl = Carbon::parse($row['tanggal_kontrak']);
            $month = $tgl->format('m');
            $years = $tgl->year;

            $lahir = Carbon::parse($row['tgllahir']);

            $lastUrut = Karyawan::whereRaw('UPPER(nama) = ?', [strtoupper(trim($row['nmkaryawan']))])
                ->whereDate('tanggal_lahir', $row['tgllahir'])
                ->whereMonth('tanggal_masuk', Carbon::parse($row['tglmasuk'])->month)
                ->whereYear('tanggal_masuk', Carbon::parse($row['tglmasuk'])->year)
                ->lockForUpdate()
                ->max(DB::raw('RIGHT(nip, 2)'));

            $urut = ($lastUrut ?? 0) + 1;

            do {
                $nourut = str_pad($urut, 2, '0', STR_PAD_LEFT);

                $nip = $project->code
                    . $tgl->format('y')
                    . $month
                    . $lahir->year
                    . $lahir->format('m')
                    . ($row['jeniskelamin'] == "L" ? "1" : "2")
                    . $nourut;

                $urut++;
            } while (Karyawan::where('nip', $nip)->exists());

            DB::transaction(function () use ($row, $nip, $month, $project, $statuskontrak, $years, $idlokasi, $projectDetail, $jabatanId, $pendidikan, $namasekolah, $jurusan, $tahunlulus, $nilaisekolah, $bankId, $kontrakId) {


                ## =============================================================================================================== PELAMAR
                $dtpelamar = array(
                    'nama'               => trim(strtoupper($row['nmkaryawan'])),
                    'nokkk'              => trim($row['nokk']),
                    'noktp'              => trim($row['noktp']),
                    'tmptlahir'          => trim(strtoupper($row['tmptlahir'])),
                    'tgllahir'           => Carbon::parse($row['tgllahir'])->format('Y-m-d'),
                    'jenis_kelamin'      => trim(strtoupper($row['jeniskelamin'])),
                    'email'              => trim(strtolower($row['email'])),
                    'password'           => bcrypt('123456Aa'),
                    'handphone'          => trim(str_replace(['-', ' '], '', $row['hp'])),
                    'agama'              => trim(strtoupper($row['nmagama'])),
                    'status_kawin'       => trim($row['statusperkawinan']),
                    'tinggi_badan'       => trim($row['tinggi_badan']),
                    'berat_badan'        => trim($row['berat_badan']),
                    'ukuran_baju'        => trim(strtoupper($row['ukuranbaju_nama'])),
                    'nomor_celana'       => trim($row['ukuran_celana']),
                    'nomor_sepatu'       => trim($row['nomor_sepatu']),
                    'alamat_ktp'         => trim(strtoupper($row['alamatktp'])),
                    'provinsi_ktp'       => trim($row['provinsi_id']),
                    'kabkot_ktp'         => trim($row['kabkot_id']),
                    'kecamatan_ktp'      => trim($row['kecamatan_id']),
                    'kelurahan_ktp'      => trim($row['kelurahan_id']),
                    'kodepos_ktp'        => trim($row['kodepos']),
                    'alamat_domisili'    => trim(strtoupper($row['alamatdomisili'])),
                    'provinsi_domisili'  => trim($row['provinsi_id']),
                    'kabkot_domisili'    => trim($row['kabkot_id']),
                    'kecamatan_domisili' => trim($row['kecamatan_id']),
                    'kelurahan_domisili' => trim($row['kelurahan_id']),
                    'kodepos_domisili'   => trim($row['kodepos']),
                    'jenjang_pendidikan' => $pendidikan,
                    'nama_sekolah'       => $namasekolah,
                    'jurusan_sekolah'    => $jurusan,
                    'tahun_lulus'        => $tahunlulus,
                    'nilai_ijazah'       => $nilaisekolah,
                    'status'             => 'COMPLETE',
                    'register'           => 'registered',
                    'file_cv'            => 'cv_' . trim($row['noktp']) . '.pdf',
                    'file_ijazah'        => 'ijazah_' . trim($row['noktp']) . '.pdf',
                );

                ## Simpan Pelamar
                $idpel = Pelamar::create($dtpelamar)->id_pelamar;

                ## =============================================================================================================== KARYAWAN

                $dtkaryawan = array(
                    'pelamar_id'      => $idpel,
                    'nip'             => $nip,
                    'kk'              => trim($row['nokk']),
                    'ktp'             => trim($row['noktp']),
                    'nama'            => trim(strtoupper($row['nmkaryawan'])),
                    'email'           => trim(strtolower($row['email'])),
                    'tempat_lahir'    => trim(strtoupper($row['tmptlahir'])),
                    'tanggal_lahir'   => Carbon::parse($row['tgllahir'])->format('Y-m-d'),
                    'alamat_ktp'      => trim(strtoupper($row['alamatktp'])),
                    'alamat_domisili' => trim(strtoupper($row['alamatdomisili'])),
                    'kabkot_id'       => trim($row['kabkot_id']),
                    'provinsi_id'     => trim($row['provinsi_id']),
                    'kecamatan_id'    => trim($row['kecamatan_id']),
                    'kelurahan_id'    => trim($row['kelurahan_id']),
                    'kodepos'         => trim($row['kodepos']),
                    'statuskawin_id'  => trim($row['statusperkawinan']),
                    'kelamin'         => trim(strtoupper($row['jeniskelamin'])),
                    'agama'           => trim(strtoupper($row['nmagama'])),
                    'handphone'       => trim(str_replace(['-', ' '], '', $row['hp'])),
                    'project'         => $idlokasi,
                    'project_detail'  => $projectDetail,
                    'jabatan'         => $jabatanId,
                    'ukuranbaju'      => trim(strtoupper($row['ukuranbaju_nama'])),
                    'ukurancelana'    => trim($row['ukuran_celana']),
                    'tinggibadan'     => trim($row['tinggi_badan']),
                    'beratbadan'      => trim($row['berat_badan']),
                    'nomorsepatu'     => trim($row['nomor_sepatu']),
                    'pendidikan'      => $pendidikan,
                    'namasekolah'     => $namasekolah,
                    'jurusan'         => $jurusan,
                    'tahunlulus'      => $tahunlulus,
                    'nilai'           => $nilaisekolah,
                    'bank_id'         => $bankId,
                    'namabank'        => trim(strtoupper($row['nmdirekening'])),
                    'norek'           => trim($row['norek']),
                    'percepatan'      => ($bankId ? false : true),
                    'tanggal_masuk'   => Carbon::parse($row['tglmasuk'])->format('Y-m-d'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                );
                ## Simpan karyawan kemudian buat variable idkar dari id;
                $idkar = Karyawan::create($dtkaryawan)->id;
                ## =============================================================================================================== SALDO CUTI
                $karyawan = Karyawan::findOrFail($idkar);
                $masaKerja = $karyawan->tanggal_masuk->diffInMonths(now());

                // hitung jumlah cuti bersama tahun berjalan
                $cutiBersama = \App\Models\CutiBersama::where('tahun', date('Y'))
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

                // mapping romawi bisa inline
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
                    $kontrakId == 2 ? 'K' . $row['kontrak_ke'] : $statuskontrak->inisial                // %s kelima
                );

                Kontrak::create([
                    'karyawan_id'      => $idkar,
                    'statuskontrak_id' => $kontrakId,
                    'nomor_kontrak'    => $nokontrak,
                    'kontrak_dari'     => Carbon::parse($row['kontrak_dari'])->format('Y-m-d'),
                    'kontrak_sampai'   => ($row['kontrak_sampai'] == '0000-00-00' ? null : Carbon::parse($row['kontrak_sampai'])->format('Y-m-d')),
                    'kontrak_ke'       => $row['kontrak_ke'],
                    'lokasi_kontrak'   => $row['lokasi_ttd'],
                    'tanggal_kontrak'  => Carbon::parse($row['tanggal_kontrak'])->format('Y-m-d'),
                    'tanggal_ptko'     => Carbon::parse($row['tanggal_kontrak'])->format('Y-m-d'),
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
            });
        }
    }

    public function rules(): array
    {
        return [
            // 'noktp'       => 'required|unique:karyawan,ktp',
            'noktp' => [
                'required',
                'unique:karyawan,ktp',
                'unique:pelamar,noktp',
            ],
            'nokk' => [
                'required',
                'unique:karyawan,kk',
                'unique:pelamar,nokk',
            ],
            'email' => [
                'required',
                'unique:karyawan,email',
                'unique:pelamar,email',
            ],
            'nmkaryawan' => 'required|string',
        ];
    }

    public function chunkSize(): int
    {
        return 50; // proses per 100 baris
    }

    // public function afterChunk()
    // {
    //     $this->log->increment('progress', 10); // progress naik tiap chunk
    // }
}

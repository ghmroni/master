<?php

namespace App\Imports;

use App\Models\Bpjs;
use App\Models\Karyawan;
use App\Models\Keluarga;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterChunk;

class MigrasiKeluargaImport implements ToCollection, WithHeadingRow, WithChunkReading, WithEvents
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

        # Ambil semua noktp dari header noktp
        $allNoKtp = $rows->pluck('noktp')->filter()->toArray();

        # Ambil id dan ktp dari tabel karyawan dalam satu query
        $karyawans = Karyawan::whereIn('ktp', $allNoKtp)
            ->get(['id', 'ktp', 'pelamar_id', 'kelamin'])
            ->keyBy('ktp');

        $insertKeluarga = [];
        $insertBPJS     = [];
        $insertTambahan = [];

        foreach ($rows as $row) {
            // 3. Cari ID Karyawan berdasarkan noktp di baris ini
            $karyawan = $karyawans[$row['noktp']] ?? null;
            if (!$karyawan) continue;

            $idKaryawan = $karyawan->id;
            $status = (int) $row['statusperkawinan'];

            // 2. Logika Hubungan Pasangan (Berdasarkan Kelamin Karyawan)
            // Jika Karyawan Laki-laki (L), maka pasangannya Istri. Selain itu Suami.
            $hubunganPasangan = (strtoupper($karyawan->kelamin) == 'L') ? 'ISTRI' : 'SUAMI';

            // Ambil Suami/Istri (Status >= 2)
            if ($status >= 2 && !empty($row['nm_suis'])) {
                $insertKeluarga[] = [
                    'pelamar_id'         => $karyawan->pelamar_id,
                    'status_hubungan'    => $hubunganPasangan,
                    'nama_keluarga'      => $row['nm_suis'],
                    'ktp_keluarga'       => $row['noktp_suis'] ?? null,
                    'tmptlahir_keluarga' => $row['tmptlahir_suis'] ?? null,
                    'tgllahir_keluarga'  => $row['tgllahir_suis'] ?? null,
                    'kelamin_keluarga'   => $row['jk_suis'] ?? null,
                    'no_bpjskes'         => $row['nobpjs_suis'] ?? null,
                    'status_bpjskes'     => $row['status_bpjssuis'] ?? null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }

            // 3. Ambil Anak (Status >= 3)
            if ($status >= 3) {
                for ($i = 1; $i <= 3; $i++) {
                    $kolomNama   = 'nm_anak' . $i;
                    $kolomKtp    = 'noktp_anak' . $i;
                    $kolomLahir  = 'tmptlahir_anak' . $i;
                    $kolomTgl    = 'tgllahir_anak' . $i;
                    $kolomJk     = 'jk_anak' . $i;
                    $kolomBpjs   = 'nobpjs_anak' . $i;
                    $kolomStatus = 'status_bpjsanak' . $i;

                    if (!empty($row[$kolomNama])) {
                        $insertKeluarga[] = [
                            'pelamar_id'         => $karyawan->pelamar_id,
                            'status_hubungan'    => 'ANAK',
                            'nama_keluarga'      => $row[$kolomNama],
                            'ktp_keluarga'       => $row[$kolomKtp] ?? null,
                            'tmptlahir_keluarga' => $row[$kolomLahir] ?? null,
                            'tgllahir_keluarga'  => $row[$kolomTgl] ?? null,
                            'kelamin_keluarga'   => $row[$kolomJk] ?? null,
                            'no_bpjskes'         => $row[$kolomBpjs] ?? null,
                            'status_bpjskes'     => $row[$kolomStatus] ?? null,
                            'created_at'         => now(),
                            'updated_at'         => now(),
                        ];
                    }
                }
            }

            $insertBPJS[] = [
                'karyawan_id'        => $idKaryawan,
                'gapok_bpjstk'       => $row['upah_bpjstk'] ?? 0,
                'gapok_bpjskes'      => $row['upah_bpjskes'] ?? 0,
                'jkk'                => $row['jkk'] ?? 0,
                'jkm'                => $row['jkm'] ?? 0,
                'jht'                => $row['jht'] ?? 0,
                'jp'                 => $row['jp'] ?? 0,
                'jkn'                => $row['jkn'] ?? 0,
                'nobpjs_tk'          => $row['bpjstk'] ?? null,
                'nobpjs_kes'         => $row['bpjskes'] ?? null,
                'status_kepesertaan' => $row['status_bpjskar'] ?? null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ];

            # TAMBAHAN
            if (!empty($row['no_bpjs_tambahan'])) {
                $tHubungan = array_map('trim', explode('|', $row['hubungan_tambahan']));
                $tNama     = array_map('trim', explode('|', $row['nama_tambahan']));
                $tKtp      = array_map('trim', explode('|', $row['no_ktp_tambahan']));
                $tBpjs     = array_map('trim', explode('|', $row['no_bpjs_tambahan']));

                for ($t = 0; $t < count($tBpjs); $t++) {
                    if (empty($tBpjs[$t])) continue;

                    $insertTambahan[] = [
                        'pelamar_id'         => $karyawan->pelamar_id,
                        'status_hubungan'    => $tHubungan[$t] ?? null,
                        'nama_keluarga'      => $tNama[$t]  ?? null,
                        'ktp_keluarga'       => $tKtp[$t]  ?? null,
                        'no_bpjskes'         => $tBpjs[$t]  ?? null,
                        'status_bpjskes'     => 'AKTIF',
                        'created_at'         => now(),
                    ];
                }
            }
        }

        # PROSES SIMPAN
        DB::transaction(function () use ($insertKeluarga, $insertBPJS, $insertTambahan) {
            if (!empty($insertKeluarga)) {
                Keluarga::insert($insertKeluarga);
            }

            if (!empty($insertBPJS)) {
                Bpjs::insert($insertBPJS);
            }

            if (!empty($insertTambahan)) {
                Keluarga::insert($insertTambahan);
            }
        });
    }

    public function chunkSize(): int
    {
        return 50; // proses per 100 baris
    }
}

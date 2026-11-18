<?php

namespace App\Imports;

use App\Models\Karyawan;
use App\Models\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterChunk;

class ScheduleImport implements ToCollection, WithChunkReading, WithEvents
{
    /**
     * @param Collection $collection
     */

    protected $log;
    protected $bulan;
    protected $tahun;
    protected $totalRows = 0;
    protected $chunkSize;
    protected $processedChunks = 0;

    public $timeout = 600;

    public function __construct($log, $bulan, $tahun)
    {
        $this->log = $log;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
        $this->chunkSize = $this->chunkSize();
    }

    public function setTotalRows($totalRows)
    {
        $this->totalRows = $totalRows;
    }

    public function registerEvents(): array
    {
        return [
            AfterChunk::class => function (AfterChunk $event) {
                $this->processedChunks++;

                if ($this->totalRows <= 0 || $this->chunkSize <= 0) {
                    $progress = 0;
                } else {
                    $totalChunks = ceil($this->totalRows / $this->chunkSize);
                    $progress = round(($this->processedChunks / $totalChunks) * 100);
                    if ($progress > 100) $progress = 100;
                }

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

        $bulan = $this->bulan;
        $tahun = $this->tahun;

        ## Slice mulai dari indeks 5 (Baris 6 Excel) untuk mengambil data Schedule
        $dataSchedule = $rows->slice(2);

        # Kumpulkan Nip dari excel
        $allNipsInExcel = $dataSchedule->map(function ($row) {
            return isset($row[0]) ? trim($row[0]) : null;
        })->filter()->toArray();

        // Cari semua ID Karyawan berdasarkan NIP tersebut dalam SATU KALI QUERY saja [1]
        // Kemudian ubah menjadi pasangan Key-Value ( 'NIP' => 'ID_KARYAWAN' ) [1, 2]
        $karyawanMap = Karyawan::whereIn('nip', $allNipsInExcel)
            ->pluck('id', 'nip');

        $dataToUpsert = [];

        foreach ($dataSchedule as $index => $row) {
            $nomorBarisExcel = $index + 1; // Konversi indeks ke nomor baris asli Excel

            $nip          = isset($row[0]) ? trim($row[0]) : null; // Kolom A
            $namaKaryawan = isset($row[1]) ? trim($row[1]) : null; // Kolom B

            // Ini otomatis mengabaikan sisa baris kosong di bawah data karyawan terakhir
            if (empty($nip) && empty($namaKaryawan)) {
                continue;
            }

            // if (!is_numeric($nip)) {
            //     continue;
            // }

            # Validasi: NIP tidak boleh kosong
            if (empty($nip)) {
                throw ValidationException::withMessages([
                    'excel' => "Gagal Import: NIP pada baris ke-{$nomorBarisExcel} tidak boleh kosong!"
                ]);
            }

            $idKaryawan = $karyawanMap[$nip] ?? null;
            // Jika NIP di Excel tidak terdaftar di database master karyawan, tolak/lewati
            if (!$idKaryawan) {
                throw ValidationException::withMessages([
                    'excel' => "Gagal Import: Karyawan dengan NIP {$nip} pada baris ke-{$nomorBarisExcel} tidak ditemukan di database!"
                ]);
            }

            $currentTimestamp = date('Y-m-d H:i:s');

            $dtTanggal = [
                'karyawan_id' => (int)$idKaryawan,
                'bulan'       => (int)$bulan,   // selalu pakai header
                'tahun'       => (int)$tahun,   // selalu pakai header
                'created_at'  => $currentTimestamp,
                'updated_at'  => $currentTimestamp,
            ];

            for ($tanggal = 1; $tanggal <= 31; $tanggal++) {
                $indeksKolomExcel = $tanggal + 1;

                // Amankan nilai cell, jika kosong isi dengan string kosong atau null
                $nilaiCell = isset($row[$indeksKolomExcel]) ? trim(strtoupper($row[$indeksKolomExcel])) : null;

                $dtTanggal['tgl' . $tanggal] = $nilaiCell;
            }

            // Urutkan key alfabetis (A-Z) agar strukturnya identik di memori
            ksort($dtTanggal);

            $dataToUpsert[] = $dtTanggal;
        }

        if (!empty($dataToUpsert)) {
            // Menghasilkan daftar kolom tanggal untuk di-update jika data sudah ada
            $columnsToUpdate = [];
            for ($t = 1; $t <= 31; $t++) {
                $columnsToUpdate[] = 'tgl' . $t;
            }

            // Hanya 1 Query SQL besar untuk memproses ratusan/ribuan data sekaligus!
            Schedule::upsert(
                $dataToUpsert,
                ['karyawan_id', 'bulan', 'tahun'], // Kombinasi kolom unik untuk pengecekan (pemberitahu data sudah ada/belum)
                $columnsToUpdate // Kolom yang akan di-update jika data sudah ada
            );
        }
    }

    public function chunkSize(): int
    {
        return 50; // proses per 100 baris
    }
}

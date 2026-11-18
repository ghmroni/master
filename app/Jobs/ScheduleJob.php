<?php

namespace App\Jobs;

use App\Imports\ScheduleImport;
use App\Models\ImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ScheduleJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected $logId;
    protected $bulan;
    protected $tahun;

    public function __construct($logId, $bulan, $tahun)
    {
        $this->logId = $logId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function handle(): void
    {
        $log = ImportLog::find($this->logId);
        if (!$log) return; // Proteksi jika log tidak ditemukan
        $log->update(['status' => 'processing', 'progress' => 0]);

        $path = $log->filename;
        $fullPath = storage_path('app/private/' . $path);

        try {
            $path = $log->filename;

            $importInstance = new ScheduleImport($log, $this->bulan, $this->tahun);

            // 2. Hitung total rows sebelum import menggunakan instance yang sama
            $totalRows = Excel::toCollection($importInstance, $fullPath)
                ->first()
                ->count();

            // 3. Masukkan total baris ke dalam instance import untuk kebutuhan hitung progress bar
            $importInstance->setTotalRows($totalRows);

            // 4. Jalankan proses import yang sebenarnya menggunakan instance yang sudah lengkap datanya
            Excel::import($importInstance, $fullPath);
            // Excel::queueImport(new ScheduleImport($importInstance), storage_path('app/private/' . $path));

            // Jika sukses
            $log->update([
                'status'   => 'done',
                'progress' => 100,
                'message'  => 'Selesai'
            ]);

            // Hapus File
            // if (Storage::exists($path)) {
            //     Storage::delete($path);
            // }
        } catch (\Throwable $e) {
            // Jika ada error
            $log->update([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            // if (Storage::exists($path)) {
            //     Storage::delete($path);
            // }

            throw $e; // penting supaya job dianggap gagal

        } finally {
            // Blok ini PASTI DILALUI baik saat sukses maupun saat masuk ke blok catch (error)

            // JIKA ANDA MENGGUNAKAN DISK PRIVATE KUSTOM (Rekomendasi jika simpan di app/private)
            // if (Storage::disk('private')->exists($path)) {
            //     Storage::disk('private')->delete($path);
            // }

            // ATAU JIKA MENGGUNAKAN DISK DEFAULT (Sesuaikan dengan cara Anda upload di Controller)
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }
}

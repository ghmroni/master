<?php

namespace App\Jobs;

use App\Imports\MigrasiKeluargaImport;
use App\Models\ImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class MigrasiKeluargaJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $logId;

    public function __construct($logId)
    {
        $this->logId = $logId;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = ImportLog::find($this->logId);
        $log->update(['status' => 'processing', 'progress' => 0]);

        try {
            $path = $log->filename;

            // Hitung total rows sebelum import
            $totalRows = Excel::toCollection(new MigrasiKeluargaImport($log), storage_path('app/private/' . $path))
                ->first()
                ->count();

            // Proses import ke App/Imports
            Excel::import(new MigrasiKeluargaImport($log, $totalRows), storage_path('app/private/' . $path));

            // Jika sukses
            $log->update([
                'status'   => 'done',
                'progress' => 100,
                'message'  => 'Selesai'
            ]);

            // Hapus File
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        } catch (\Throwable $e) {
            // Jika ada error
            $log->update([
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            throw $e; // penting supaya job dianggap gagal

            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }
}

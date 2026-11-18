<?php

namespace App\Jobs;

use App\Models\Download;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DownloadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobId;
    public $userId;
    public $params;
    public $view;
    public $prefix;
    public $useSchedule;

    /**
     * Create a new job instance.
     */
    public function __construct($jobId, $userId, $params, $view, $useSchedule = null, $prefix = 'export')
    {
        $this->jobId       = $jobId;
        $this->userId      = $userId;
        $this->params      = $params;
        $this->view        = $view;         // nama blade view
        $this->useSchedule = $useSchedule;
        $this->prefix      = $prefix;       // prefix nama file
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Download::where('job_id', $this->jobId)->update(['progress' => 10]);

        $orientation = match ($this->view) {
            'absensi.pdf_rekap_waktu' => 'lanscape',
            'absensi.pdf_kehadiran' => 'lanscape',
            'absensi.pdf_perlokasi'   => 'portrait',
            default                   => 'portrait',
        };
        // generate PDF sesuai view
        $pdf = Pdf::loadView($this->view, [
            'data'        => $this->params,
            'useSchedule' => $this->useSchedule
        ])
            ->setPaper('A4', $orientation);

        Download::where('job_id', $this->jobId)->update(['progress' => 50]);

        $filename = "{$this->prefix}_{$this->userId}_" . $this->jobId . ".pdf";

        if (!Storage::disk('local')->exists('exports')) {
            Storage::disk('local')->makeDirectory('exports');
        }

        Storage::disk('local')->put("exports/{$filename}", $pdf->output());

        Download::where('job_id', $this->jobId)->update(['progress' => 80]);

        if (!Storage::disk('local')->exists("exports/{$filename}")) {
            return; // jangan update status kalau file belum ada
        }
        // update status di tabel downloads
        Download::where('job_id', $this->jobId)
            ->update([
                'status'   => 'done',
                'filename' => $filename,
                'progress' => 100,
            ]);
    }
}

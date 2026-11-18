<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimcSaldo extends Model
{
    use SoftDeletes;

    protected $table = 'simc_saldo';
    protected $guarded = ['id'];
    protected $appends = ['remaining'];
    protected $casts = [
        'saldo_expired' => 'date',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function getRemainingAttribute()
    {
        $today = now();
        $sisa = 0;

        // hitung masa kerja dari TMT masuk
        $masaKerja = $this->karyawan->tanggal_masuk->diffInMonths($today);

        // kalau belum 12 bulan, saldo cuti = 0
        if ($masaKerja < 12) {
            return 0;
        }

        // carry over kalau belum expired
        if ($this->saldo_expired === null || $this->saldo_expired->gte($today)) {
            $sisa = $this->saldo_sebelumnya;
        }

        // ambil jumlah cuti bersama dari tabel cuti_bersama
        $cutiBersama = CutiBersama::where('tahun', $today->year)
            ->whereNull('deleted_at')
            ->count();

        // cek project_detail apakah mengandung root project 1 GHM PUSAT
        $projectDetail = $this->karyawan->project_detail;
        $projectIds = explode(',', $projectDetail);

        if (in_array(1, $projectIds)) {
            // kalau project root = 1, tidak dikurangi cuti bersama
            $cutiBersama = 0;
        }

        // total saldo dikurangi cuti bersama
        return (($this->saldo ?? 0) + $sisa)
            - ($this->saldo_terpakai ?? 0)
            - $cutiBersama;

        // return ($this->saldo + $sisa) - $this->saldo_terpakai;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'karyawan';
    protected $guarded = ['id'];
    protected $appends = ['lokasi_penempatan'];
    protected $casts = [
        'tanggal_masuk' => 'date',
    ];


    public function schedule(): HasMany
    {
        return $this->hasMany(Schedule::class, 'karyawan_id', 'id');
    }

    public function rekapKehadiran(): HasMany
    {
        return $this->hasMany(RekapKehadiran::class, 'karyawan_id', 'id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class, 'karyawan_id', 'id');
    }

    public function absensiTerbaru(): HasOne
    {
        return $this->hasOne(Absensi::class, 'karyawan_id', 'id')->latestOfMany('check_time');
    }

    public function monitoring(): HasMany
    {
        return $this->hasMany(Monitoring::class, 'karyawan_id', 'id');
    }

    public function saldoCuti(): HasMany
    {
        return $this->hasMany(SimcSaldo::class, 'karyawan_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id')->select('id', 'parent_id', 'name');
    }

    public function aksesProject(): HasMany
    {
        return $this->hasMany(AksesProject::class, 'project_id', 'project');
    }

    public function kontrak(): HasMany
    {
        return $this->hasMany(Kontrak::class, 'karyawan_id', 'id');
    }

    public function kontrakAktif(): HasOne
    {
        return $this->hasOne(Kontrak::class, 'karyawan_id', 'id');
    }

    public function gajiekstra(): HasOne
    {
        return $this->hasOne(Gajiekstra::class, 'karyawan_id', 'id');
    }

    public function jabatan(): HasOne
    {
        return $this->hasOne(Jabatan::class, 'id', 'jabatan');
    }

    public function pelamar(): HasOne
    {
        return $this->hasOne(Pelamar::class, 'id_pelamar', 'pelamar_id');
    }

    public function gradeGolongan(): HasOne
    {
        return $this->hasOne(GradeGolongan::class, 'karyawan_id', 'id');
    }

    public function statuskawin(): HasOne
    {
        return $this->hasOne(StatusPerkawinan::class, 'id', 'statuskawin_id');
    }

    public function bpjs(): HasOne
    {
        return $this->hasOne(Bpjs::class, 'karyawan_id', 'id');
    }

    public function recordout(): HasOne
    {
        return $this->hasOne(RecordOut::class, 'karyawan_id', 'id');
    }

    public function keluarga()
    {
        return $this->hasManyThrough(
            Keluarga::class, // Tabel Tujuan
            Pelamar::class, // Tabel Perantara
            'id_pelamar',   // 1. Foreign key di tabel Pelamar
            'pelamar_id',   // 2. Foreign key di tabel Keluarga
            'pelamar_id',   // 3. Local key di tabel Karyawan
            'id_pelamar'    // 4. Local key di tabel Pelamar
        )
            ->orderByRaw("CASE 
                WHEN status_hubungan LIKE '%Istri%' THEN 1 
                WHEN status_hubungan LIKE '%Suami%' THEN 1 
                ELSE 2 END ASC")
            ->orderBy('tgllahir_keluarga', 'ASC');
    }

    public function getLokasiPenempatanAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }

        $ids = explode(',', $this->project_detail);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }
}

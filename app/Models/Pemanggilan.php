<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemanggilan extends Model
{
    protected $table = 'pemanggilan_pelamar';
    protected $guarded = ['id'];
    protected $appends = ['lokasi_names'];
    protected $casts = [
        'data_analisis' => 'array'
    ];

    public function lowonganApply(): BelongsTo
    {
        return $this->belongsTo(LowonganApply::class, 'no_registrasi', 'no_registrasi');
    }

    public function recordPemanggilan(): HasMany
    {
        return $this->hasMany(PemanggilanRecord::class, 'pemanggilan_id')->orderBy('id', 'desc');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function userReq(): BelongsTo
    {
        return $this->belongsTo(User::class, 'request_id', 'id');
    }

    public function cfit(): BelongsTo
    {
        return $this->belongsTo(CfitHasil::class, 'cfit_id', 'id');
    }
    public function mbti(): BelongsTo
    {
        return $this->belongsTo(MbtiHasil::class, 'mbti_id', 'id');
    }
    public function papi(): BelongsTo
    {
        return $this->belongsTo(PapiHasil::class, 'papi_id', 'id');
    }
    public function disc(): BelongsTo
    {
        return $this->belongsTo(DiscHasil::class, 'disc_id', 'id');
    }
    public function wawancara(): BelongsTo
    {
        return $this->belongsTo(Wawancara::class, 'wawancara_id', 'id');
    }

    public function getLokasiNamesAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }

        $ids = explode(',', $this->lokasi_penempatan);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }
}

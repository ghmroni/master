<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengalamanKerja extends Model
{
    protected $table = 'pengalaman_kerja';
    protected $guarded = ['id'];

    public function pelamar(): BelongsTo
    {
        return $this->belongsTo(pelamar::class, 'id_pelamar', 'pelamar_id');
    }
}

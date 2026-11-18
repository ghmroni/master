<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SertifikatPelamar extends Model
{
    protected $table = 'sertifikat_pelamar';
    protected $guarded = ['id'];

    public function pelamar(): BelongsTo
    {
        return $this->belongsTo(Pelamar::class, 'id_pelamar', 'pelamar_id');
    }
}

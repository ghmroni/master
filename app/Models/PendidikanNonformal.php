<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendidikanNonformal extends Model
{
    protected $table = 'pendidikan_nonformal';
    protected $primaryKey = 'id_nonformal';
    protected $guarded = ['id_nonformal'];

    public function pelamar(): BelongsTo
    {
        return $this->belongsTo(Pelamar::class, 'id_pelamar', 'pelamar_id');
    }
}

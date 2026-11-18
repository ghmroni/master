<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kontrak extends Model
{
    use SoftDeletes;

    protected $table = 'kontrak';
    protected $guarded = ['id'];

    public function statuskontrak(): HasOne
    {
        return $this->hasOne(StatusKontrak::class, 'id', 'statuskontrak_id');
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusKontrak extends Model
{
    protected $table = 'statuskontrak';
    protected $guarded = ['id'];

    public function kontrak(): BelongsTo
    {
        return $this->belongsTo(Kontrak::class, 'id', 'statuskontrak_id');
    }
}

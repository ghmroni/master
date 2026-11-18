<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemanggilanRecord extends Model
{
    protected $table = 'pemanggilan_record';
    protected $guarded = ['id'];
    // setiap kali toArray() atau response()->json() field created_at_formatted akan muncul
    protected $appends = ['created_at_formatted'];


    public function pemanggilan(): BelongsTo
    {
        return $this->belongsTo(Pemanggilan::class, 'pemanggilan_id', 'id');
    }

    public function getCreatedAtFormattedAttribute()
    {
        return Carbon::parse($this->created_at)->translatedFormat('l, d F Y H:i');
    }
}

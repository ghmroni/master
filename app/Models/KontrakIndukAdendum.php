<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontrakIndukAdendum extends Model
{
    use SoftDeletes;

    protected $table = 'kontrakinduk_adendum';
    protected $guarded = ['id'];

    public function kontrakinduk(): BelongsTo
    {
        return $this->belongsTo(Kontrakinduk::class, 'kontrakinduk_id');
    }

    // Adendum ini melahirkan versi harga baru untuk project tertentu
    public function settingHarga(): BelongsTo
    {
        return $this->belongsTo(KontrakIndukSettingHarga::class, 'setting_id', 'id');
    }
}

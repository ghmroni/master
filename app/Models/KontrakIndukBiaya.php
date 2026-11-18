<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontrakIndukBiaya extends Model
{
    use SoftDeletes;

    protected $table = 'kontrakinduk_biaya';
    protected $guarded = ['id'];

    // Kembali ke Setting Harganya (Many-to-One)
    public function settingHarga(): BelongsTo
    {
        return $this->belongsTo(KontrakIndukSettingHarga::class, 'setting_harga_id');
    }

    // Hubungan Many-to-One ke Tabel Master Unitkerja untuk mengambil nama Unit Kerjanya
    public function unitkerja(): BelongsTo
    {
        return $this->belongsTo(Unitkerja::class, 'unitkerja_id');
    }
}

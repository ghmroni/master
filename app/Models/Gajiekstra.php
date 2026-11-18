<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gajiekstra extends Model
{
    use SoftDeletes;

    protected $table = 'gajiekstra';
    protected $guarded = ['id'];

    // Casting: mengubah data json di DB menjadi array PHP secara otomatis
    protected $casts = [
        'tunjangan' => 'array',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'id', 'karyawan_id');
    }
}

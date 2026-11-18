<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LowonganApply extends Model
{
    use HasFactory;

    protected $table = 'lowongan_apply';
    protected $primaryKey = 'no_registrasi';
    public $incrementing = false; // kalau bukan auto increment
    protected $keyType = 'string'; // kalau tipe kolom bukan integer

    protected $fillable = [
        'no_registrasi',
        'pelamar_id',
        'lowongan_id',
    ];

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class, 'lowongan_id', 'id');
    }

    public function pemanggilan(): HasOne
    {
        return $this->hasOne(Pemanggilan::class, 'no_registrasi', 'no_registrasi');
    }

    public function pelamar(): BelongsTo
    {
        return $this->belongsTo(Pelamar::class, 'pelamar_id', 'id_pelamar');
    }
}

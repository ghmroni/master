<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pelamar extends Model
{
    use HasFactory;

    protected $table = 'pelamar';
    protected $primaryKey = 'id_pelamar';
    protected $guarded = ['id_pelamar'];

    public function lowonganApply(): HasMany
    {
        return $this->hasMany(LowonganApply::class, 'pelamar_id', 'id_pelamar');
    }

    public function perkawinan(): HasOne
    {
        return $this->hasOne(StatusPerkawinan::class, 'id', 'status_kawin');
    }

    public function keluarga(): HasMany
    {
        return $this->hasMany(Keluarga::class, 'pelamar_id', 'id_pelamar')->whereNull('deleted_at');
    }

    //PROVINSI
    public function provinsiKtp(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_ktp', 'id_provinsi');
    }
    public function provinsiDomisili(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_domisili', 'id_provinsi');
    }

    public function kabkotKtp(): BelongsTo
    {
        return $this->belongsTo(Kabkot::class, 'kabkot_ktp', 'id_kabkot');
    }
    public function kabkotDomisili(): BelongsTo
    {
        return $this->belongsTo(Kabkot::class, 'kabkot_domisili', 'id_kabkot');
    }

    public function kecamatanKtp(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_ktp', 'id_kecamatan');
    }
    public function kecamatanDomisili(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_domisili', 'id_kecamatan');
    }

    public function kelurahanKtp(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_ktp', 'id_kelurahan');
    }
    public function kelurahanDomisili(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_domisili', 'id_kelurahan');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webdetaillayanan extends Model
{
    protected $table = 'web_detail_layanan';

    public $incrementing = false; // UUID bukan auto-increment
    protected $keyType = 'string'; // UUID adalah string


    protected $casts = [
        'id' => 'string',
    ];

    protected $fillable = [
        'id',
        'layanan_id',
        'nourut',
        'nama',
        'jabatan',
        'quote',
        'photo',
    ];

    public $timestamps = false;
}

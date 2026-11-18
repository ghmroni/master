<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightLampiran extends Model
{
    use SoftDeletes;

    protected $table = 'flight_lampiran';
    protected $guarded = ['id'];

    protected $casts = [
        'data_flight'  => 'array',
        'data_rekap'   => 'array',
        'data_layanan' => 'array',
        'data_petugas' => 'array',
    ];
}

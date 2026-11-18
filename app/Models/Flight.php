<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Flight extends Model
{
    use SoftDeletes;

    protected $table = 'flight';
    protected $guarded = ['id'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function lampiran(): HasOne
    {
        return $this->hasOne(FlightLampiran::class, 'cabang', 'cabang')
            ->whereColumn('flight_lampiran.tahun', 'tahun')
            ->whereColumn('flight_lampiran.bulan', 'bulan');
        // return $this->hasOne(FlightLampiran::class, null, null)
        //     ->whereColumn('flight.tahun', 'flight_lampiran.tahun')
        //     ->whereColumn('flight.bulan', 'flight_lampiran.bulan')
        //     ->whereColumn('flight.cabang', 'flight_lampiran.cabang');
    }
}

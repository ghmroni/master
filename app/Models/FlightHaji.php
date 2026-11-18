<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightHaji extends Model
{
    use SoftDeletes;

    protected $table = 'flight_haji';
    protected $guarded = ['id'];
}

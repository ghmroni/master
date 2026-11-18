<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PapiMaster extends Model
{
    use SoftDeletes;

    protected $table = 'papi_master';
    protected $guarded = ['id'];
}

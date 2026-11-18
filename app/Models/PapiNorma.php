<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PapiNorma extends Model
{
    use SoftDeletes;

    protected $table = 'papi_norma';
    protected $guarded = ['id'];
}

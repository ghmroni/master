<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pkwt extends Model
{
    use SoftDeletes;

    protected $table = 'pkwt';
    protected $guarded = ['id'];
}

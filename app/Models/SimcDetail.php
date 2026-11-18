<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimcDetail extends Model
{
    use SoftDeletes;

    protected $table = 'simc_detail';
    protected $guarded = ['id'];
}

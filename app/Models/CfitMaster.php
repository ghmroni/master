<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CfitMaster extends Model
{
    use SoftDeletes;

    protected $table = 'cfit_master';
    protected $guarded = ['id'];
}

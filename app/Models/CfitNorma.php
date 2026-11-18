<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CfitNorma extends Model
{
    use SoftDeletes;

    protected $table = 'cfit_norma';
    protected $guarded = ['id'];
}

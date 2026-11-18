<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeGolongan extends Model
{
    use SoftDeletes;

    protected $table = 'grade_golongan';
    protected $guarded = ['id'];
}

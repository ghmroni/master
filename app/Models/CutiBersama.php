<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CutiBersama extends Model
{
    use SoftDeletes;

    protected $table = 'cuti_bersama';
    protected $guarded = ['id'];
}

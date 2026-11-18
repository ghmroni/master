<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MbtiSoal extends Model
{
    use SoftDeletes;

    protected $table = 'mbti_soal';
    protected $guarded = ['id'];
}

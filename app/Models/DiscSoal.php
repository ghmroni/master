<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscSoal extends Model
{
    use SoftDeletes;

    protected $table = 'disc_soal';
    protected $guarded = ['id'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CfitHasil extends Model
{
    use SoftDeletes;

    protected $table = 'cfit_hasil';
    protected $guarded = ['id'];

    protected $casts = [
        'data_jawaban' => 'array'
    ];
}

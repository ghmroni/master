<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MbtiAnalisis extends Model
{
    use SoftDeletes;

    protected $table = 'mbti_analisis';
    protected $guarded = ['id'];

    protected $casts = [
        'deskripsi' => 'array',
        'saran'     => 'array',
    ];
}

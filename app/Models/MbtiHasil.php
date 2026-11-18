<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MbtiHasil extends Model
{
    use SoftDeletes;

    protected $table = 'mbti_hasil';
    protected $guarded = ['id'];

    protected $casts = [
        'jawaban' => 'array',
        'dimensi' => 'array',
        'persentase' => 'array',
        'analisis' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PapiHasil extends Model
{
    use SoftDeletes;

    protected $table = 'papi_hasil';
    protected $guarded = ['id'];

    protected $casts = [
        'jawaban' => 'array',
        'analisis' => 'array',
    ];
}

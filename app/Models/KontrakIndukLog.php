<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontrakIndukLog extends Model
{
    use SoftDeletes;

    protected $table = 'kontrakinduk_log';
    protected $guarded = ['id'];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];
}

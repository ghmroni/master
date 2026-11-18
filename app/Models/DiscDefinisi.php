<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscDefinisi extends Model
{
    use SoftDeletes;

    protected $table = 'disc_definisi';
    protected $guarded = ['id'];

    protected $casts = [
        'karakter' => 'array',
    ];
}

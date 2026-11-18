<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscHasil extends Model
{
    use SoftDeletes;

    protected $table = 'disc_hasil';
    protected $guarded = ['id'];
    protected $casts = [
        'most'          => 'array',
        'least'         => 'array',
        'data_line'     => 'array',
        'data_grafik'   => 'array',
        'data_faktor'   => 'array',
        'data_karakter' => 'array',
    ];
}

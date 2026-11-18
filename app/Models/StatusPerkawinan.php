<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusPerkawinan extends Model
{
    use SoftDeletes;

    protected $table = 'status_perkawinan';
    protected $guarded = ['id'];

    public function Pelamar()
    {
        return $this->belongsTo(Pelamar::class, 'id', 'id_pelamar');
    }
}

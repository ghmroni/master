<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    use HasFactory;

    protected $primaryKey = null;
    public $incrementing = false;

    protected $table = 'kelurahan';
    protected $fillable = ['id_kelurahan', 'kecamatan_id', 'nama_kelurahan'];
}

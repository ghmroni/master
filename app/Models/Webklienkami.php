<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webklienkami extends Model
{
    use SoftDeletes;

    protected $table = 'web_klienkami';
    protected $guarded = ['id'];
}

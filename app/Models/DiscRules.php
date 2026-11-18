<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscRules extends Model
{
    use SoftDeletes;

    protected $table = 'disc_rules';
    protected $guarded = ['id'];
}

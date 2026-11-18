<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscScores extends Model
{
    use SoftDeletes;

    protected $table = 'disc_scores';
    protected $guarded = ['id'];
}

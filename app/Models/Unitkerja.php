<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unitkerja extends Model
{
    use SoftDeletes;

    protected $table = 'unitkerja';
    protected $guarded = ['id'];

    public function User(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}

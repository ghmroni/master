<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MbtiMaster extends Model
{
    use SoftDeletes;

    protected $table = 'mbti_master';
    protected $guarded = ['id'];

    public function analisis(): BelongsTo
    {
        return $this->belongsTo(MbtiAnalisis::class, 'analisis_id', 'id');
    }
}

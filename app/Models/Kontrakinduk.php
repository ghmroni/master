<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kontrakinduk extends Model
{
    use SoftDeletes;

    protected $table = 'kontrakinduk';
    protected $guarded = ['id'];

    public function settingHarga(): HasMany
    {
        return $this->hasMany(KontrakIndukSettingHarga::class, 'kontrakinduk_id');
    }

    public function adendum(): HasMany
    {
        return $this->hasMany(KontrakIndukAdendum::class, 'kontrakinduk_id');
    }

    public function approval(): HasMany
    {
        return $this->hasMany(KontrakIndukApprovalRequest::class, 'kontrakinduk_id', 'id');
    }

    public function approvalPending(): HasOne
    {
        return $this->hasOne(KontrakIndukApprovalRequest::class, 'kontrakinduk_id', 'id')
            ->where('status', 'PENDING');
    }
}

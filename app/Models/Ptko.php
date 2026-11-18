<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ptko extends Model
{
    use SoftDeletes;

    protected $table = 'ptko';
    protected $guarded = ['id'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class, 'ptko_id', 'id')
            ->orderBy('id', 'desc'); // urutkan dari id karyawan yg terbesar
    }
}

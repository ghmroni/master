<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AksesProject extends Model
{
    use HasFactory;

    protected $table = 'akses_project';
    protected $fillable = ['user_id', 'project_id'];
    public $timestamps = false;


    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'project_id', 'project');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }
}

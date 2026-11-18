<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MutasiKaryawan extends Model
{
    use SoftDeletes;

    protected $table = 'mutasi_karyawan';
    protected $guarded = ['id'];
    protected $appends = ['project_old', 'project_new'];


    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function getProjectOldAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }

        $ids = explode(',', $this->project_sebelumnya);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }

    public function getProjectNewAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }

        $ids = explode(',', $this->project_tujuan);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }
}

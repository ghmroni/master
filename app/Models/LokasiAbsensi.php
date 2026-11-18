<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LokasiAbsensi extends Model
{
    use SoftDeletes;

    protected $table = 'lokasi_absensi';
    protected $guarded = ['id'];
    protected $appends = ['lokasi_absen'];

    public function getLokasiAbsenAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }
        $ids = is_array($this->penempatan)
            ? $this->penempatan
            : explode(',', $this->penempatan);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }
}

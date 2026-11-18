<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordOut extends Model
{
    use SoftDeletes;

    protected $table = 'record_out';
    protected $guarded = ['id'];
    protected $appends = ['lokasi_pekerjaan'];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id', 'id');
    }

    public function getLokasiPekerjaanAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }

        $ids = explode(',', $this->project_detail);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }
}

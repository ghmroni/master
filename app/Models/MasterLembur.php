<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterLembur extends Model
{
    use SoftDeletes;

    protected $table = 'master_lembur';
    protected $guarded = ['id'];
    protected $appends = ['lokasi_project'];

    public function getLokasiProjectAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::pluck('name', 'id');
        }

        $ids = explode(',', $this->project_detail);
        return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
    }
}

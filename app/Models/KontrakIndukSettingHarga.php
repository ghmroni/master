<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KontrakIndukSettingHarga extends Model
{
    use SoftDeletes;

    protected $table = 'kontrakinduk_setting_harga';
    protected $guarded = ['id'];
    protected $appends = ['lokasi_project'];

    protected $casts = [
        'list_biaya'   => 'array',
    ];

    public function kontrakinduk(): BelongsTo
    {
        return $this->belongsTo(Kontrakinduk::class, 'kontrakinduk_id');
    }

    // public function adendum(): BelongsTo
    // {
    //     return $this->belongsTo(KontrakIndukAdendum::class, 'adendum_id');
    // }

    public function biaya(): HasMany
    {
        return $this->hasMany(KontrakIndukBiaya::class, 'setting_harga_id');
    }

    public function getLokasiProjectAttribute()
    {
        static $allProjects = null;
        if ($allProjects === null) {
            $allProjects = Project::with('pt')->get()->keyBy('id');
        }

        $ids = explode(',', $this->project_detail);
        // return collect($ids)->map(fn($id) => $allProjects[$id] ?? null)->filter();
        return collect($ids)->map(function ($id) use ($allProjects) {
            $project = $allProjects->get(trim($id));

            if (!$project) {
                return null;
            }
            // Hanya tambahkan nama PT jika project adalah root (parent_id kosong)
            if (is_null($project->parent_id)) {
                $ptName = $project->pt?->name ?? '-';
                return $project->name . ' (' . $ptName . ')';
            }

            // Jika bukan root (sub-project), tampilkan nama project saja
            return $project->name;
        })->filter();
    }

    // Mutator Otomatis untuk teks referensi tagihan invoice Anda
    // public function getTeksReferensiTagihanAttribute()
    // {
    //     $nomorKontrak = $this->kontrakinduk->nomor;

    //     if (is_null($this->adendum_id)) {
    //         return "Berdasarkan kontrakinduk nomor {$nomorKontrak}";
    //     }

    //     return "Berdasarkan kontrakinduk nomor {$nomorKontrak}, adendum ke {$this->adendum->adendum_ke} nomor {$this->adendum->nomor_adendum}";
    // }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Project extends Model
{
    use SoftDeletes, HasRecursiveRelationships;

    protected $guarded = ['id'];
    protected $table = 'project';
    protected $appends = ['text'];

    ## Khusus untuk jstree manggilnya text
    public function getTextAttribute()
    {
        // if (is_null($this->parent_id)) {
        //     return $this->name . ' ( <strong>' . $this->pt->name . '</strong> )';
        // }
        // return $this->name;

        if (is_null($this->parent_id)) {
            $inisialName = $this->inisial ?? '-';
            return $this->name . ' ( <strong>' . $inisialName . '</strong> )';
        }
        return $this->name;
    }

    ## Khusus untuk blade manggilnya plain_text
    public function getPlainTextAttribute()
    {
        if (is_null($this->parent_id)) {
            $inisialName = $this->inisial ?? '-';
            return $this->name . ' ( ' . $inisialName . ' )';
        }
        return $this->name;
    }

    public function getParentKeyName()
    {
        return 'parent_id';
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'akses_project', 'project_id', 'user_id');
    }

    public function Parent(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'parent_id');
    }

    public function pt()
    {
        return $this->belongsTo(Companies::class, 'pt_id');
    }

    public function Children(): HasMany
    {
        // return $this->hasMany(Project::class, 'parent_id')
        //     ->select('project.name AS text', 'project.*');
        return $this->hasMany(Project::class, 'parent_id')->with('children');
    }

    public function childrenRecursive()
    {
        // return $this->hasMany(Project::class, 'parent_id')
        //     ->select('project.name AS text', 'project.*')
        //     ->where('status', 'ENABLE')
        //     ->with(['childrenRecursive' => function ($q) {
        //         $q->whereHas('aksesProject', function ($sub) {
        //             $sub->where('user_id', auth()->id());
        //         });
        //     }]);
        return $this->children()->with('childrenRecursive');
    }

    public function allChildren(): HasMany
    {
        return $this->Children()->with('allChildren')->where('status', 'ENABLE');
    }

    public function getAllChildrenIds(&$ids = [])
    {
        $ids[] = $this->id;

        foreach ($this->allChildren as $child) {
            $child->getAllChildrenIds($ids);
        }

        return $ids;
    }


    public function aksesProject(): HasMany
    {
        return $this->hasMany(AksesProject::class, 'project_id', 'id');
    }

    public function kontrakinduk(): HasOne
    {
        return $this->hasOne(Kontrakinduk::class, 'project_id', 'id');
    }


    public function businessRoot()
    {
        $current = $this;

        // Jika mulai dari node ber-inisial, langsung return
        if (!empty($current->inisial)) {
            return $current;
        }

        while ($current->parent_id !== null) {
            $parent = Project::find($current->parent_id);
            // Hentikan di parent yang ber-inisial
            if (!empty($parent->inisial)) {
                return $parent;
            }

            $current = $parent;
        }

        // Tidak menemukan inisial di rantai, kembalikan root absolut
        return $current;
    }

    public function root()
    {
        $current = $this;

        while ($current->parent_id !== null) {
            $current = Project::find($current->parent_id);
        }

        return $current;
    }



    // Parent() → Relasi ke kategori induk.
    // Children() → Relasi ke kategori anak.



}

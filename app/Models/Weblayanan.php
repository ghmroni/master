<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Weblayanan extends Model
{
    use SoftDeletes, Sluggable;

    protected $table = 'web_layanan';
    protected $guarded = ['id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true
            ]
        ];
    }

    public function detail(): HasMany
    {
        return $this->hasMany(Webdetaillayanan::class, 'layanan_id', 'id');
    }
}

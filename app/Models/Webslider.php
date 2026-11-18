<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webslider extends Model
{
    use SoftDeletes, Sluggable;

    protected $table = 'web_slider';
    protected $fillable = [
        'title',
        'short_deskripsi',
        'long_deskripsi',
        'image',
        'no_urut',
        'slug',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title',
                // 'onUpdate' => true
            ]
        ];
    }
}

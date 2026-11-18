<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class Menu extends Model
{
    use HasRoles, SoftDeletes;

    protected $guarded = ['id'];
    protected $table = 'menus';

    // // public function Parent(): BelongsTo
    // // {
    // //     return $this->belongsTo(Menu::class, 'submenu');
    // // }
    // public function Parent(): HasMany
    // {
    //     return $this->hasMany(Menu::class, 'submenu');
    // }

    // public function subMenus(): HasMany
    // {
    //     return $this->hasMany(Menu::class, 'submenu')->orderBy('sort', 'ASC');
    // }

    // public function Akses(): HasOne
    // {
    //     return $this->hasOne(Permission::class, 'id', 'permission_id');
    // }

    public function subMenus()
    {
        return $this->hasMany(SubMenu::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'menu_permission');
    }
}

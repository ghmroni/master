<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class SubMenu extends Model
{
    use HasRoles, SoftDeletes;

    protected $table = 'sub_menus';
    protected $guarded = ['id'];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'submenu_permission', 'submenu_id', 'permission_id');
    }
}

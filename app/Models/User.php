<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'status',
        'token',
        'created_token',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function pemanggilan(): HasMany
    {
        return $this->hasMany(Pemanggilan::class, 'user_id', 'id');
    }

    // public function aksesproject(): HasMany
    // {
    //     return $this->hasMany(AksesProject::class, 'user_id', 'id');
    // }

    public function aksesproject()
    {
        // Parameter: Model Tujuan, Nama Tabel Pivot, Foreign Key User, Foreign Key Project
        return $this->belongsToMany(AksesProject::class, 'akses_project', 'user_id', 'project_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'akses_project', 'user_id', 'project_id');
    }

    // public function getProjectAccess()
    // {
    //     $roles = $this->roles->pluck('id')->toArray();

    //     if (in_array(7, $roles)) {
    //         $idRoot = 1;
    //         $rootProject = Project::find($idRoot);

    //         return $rootProject
    //             ? $rootProject->descendantsAndSelf()->pluck('id')->toArray()
    //             : [$idRoot];
    //     }

    //     return $this->projects()->pluck('akses_project.project_id')->toArray();
    // }

    public function getProjectAccess()
    {
        $roles = $this->roles->pluck('id')->toArray();

        switch (true) {
            ## SA & ADMIN AMBIL SEMUA
            case in_array(1, $roles) || in_array(2, $roles):
                return Project::pluck('id')->toArray();

                ## ADMIN BPJS
            case in_array(4, $roles):
                $aksesProject = $this->projects()->pluck('akses_project.project_id')->toArray();

                return !empty($aksesProject)
                    ? $aksesProject
                    : Project::pluck('id')->toArray();

                ## DEFAULT AMBIL DARI AKSES PROJECT
            default:
                return $this->projects()->pluck('akses_project.project_id')->toArray();
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * A user may has many roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function roles(): BelongsToMany {
        return $this->belongsToMany('App\Models\Role', 'user_has_roles');
    }

    /**
     * A user may has many permission
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function permissions(): BelongsToMany {
        return $this->belongsToMany('App\Models\Permission', 'user_has_permissions');
    }

    /**
     * A user may has many roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function posts(): hasMany {
        return $this->hasMany('App\Models\Post', 'posts');
    }
}

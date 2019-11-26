<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use App\Exceptions\PermissionDoesNotExist;
use App\Exceptions\PermissionAlreadyExists;
use App\Traits\HasRoles;
use App\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract {
	use HasRoles;

      /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany {
        return $this->belongsToMany('App\Models\Role', 'role_has_permissions');
    }

    /**
     * A permission can be applied to users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users() {
        return $this->belongsToMany('App\Models\User', 'user_has_permissions');
    }

    /**
     * Find a permission by its name 
     *
     * @param string $name
     *
     * @throws App\Exceptions\PermissionDoesNotExist
     *
     * @return App\Contracts\Permission
     */
    public static function findByName(string $name): PermissionContract {
        // $permission = static::getPermissions(['name' => $name])->first();
        $permission = Permission::where('name', $name)->first();
        if (! $permission) {
            throw PermissionDoesNotExist::create($name);
        }

        return $permission;
    }

    /**
     * Find a permission by its id
     *
     * @param int $id
     *
     * @throws App\Exceptions\PermissionDoesNotExist
     *
     * @return App\Contracts\Permission
     */
    public static function findById(int $id): PermissionContract {
        $permission = static::getPermissions(['id' => $id])->first();

        if (! $permission) {
            throw PermissionDoesNotExist::withId($id);
        }

        return $permission;
    }

    /**
     * Find or create permission by its name 
     *
     * @param string $name
     *
     * @return App\Contracts\Permission
     */
    public static function findOfCreate(string $name): PermissionContract {
        $permission = static::getPermissions(['name' => $name])->first();

        if (! $permission) {
            return static::query()->create(['name' => $name]);
        }

        return $permission;
    }
}

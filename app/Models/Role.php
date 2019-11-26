<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Exceptions\RoleDoesNotExist;
use App\Exceptions\RoleAlreadyExists;
use App\Traits\HasPermissions;
use App\Contracts\Role as RoleContract;
use App\Models\Permission; 

class Role extends Model implements RoleContract {
	use HasPermissions;

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function permissions(): BelongsToMany {
        return $this->belongsToMany('App\Models\Permission', 'role_has_permissions');
    }

    /**
     * A role can be applied to users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users() {
        return $this->belongsToMany('App\Models\User', 'user_has_roles');
    }

    /**
     * Find a role by its name
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Role|\Spatie\Permission\Models\Role
     *
     * @throws \Spatie\Permission\Exceptions\RoleDoesNotExist
     */
    public static function findByName(string $name): RoleContract {
        $role = static::where('name', $name)->first();
        if (! $role) {
            throw RoleDoesNotExist::named($name);
        }
        return $role;
    }

    public static function findById(int $id): RoleContract {
        $role = static::where('id', $id)->first();
        if (! $role) {
            throw RoleDoesNotExist::withId($id);
        }
        return $role;
    }

    /**
     * Find or create role by its name
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public static function findOrCreate(string $name): RoleContract {
        $role = static::where('name', $name)->first();
        if (! $role) {
            return static::query()->create(['name' => $name]);
        }
        return $role;
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     *
     * @throws \Spatie\Permission\Exceptions\GuardDoesNotMatch
     */
    public function hasPermissionTo($permission): bool {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }

        if (is_int($permission)) {
            $permission = Permission::findById($permission);
        }

        return $this->permissions->contains('id', $permission->id);
    }

   
}

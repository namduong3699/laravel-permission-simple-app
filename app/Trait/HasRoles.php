<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use App\Contracts\Role as RoleContracts;
use App\Models\Role;

trait HasRoles {
    use HasPermissions;

    /**
     * Scope the model query to certain roles only.
     * Pham vi truy van query cua role
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole(Builder $query, $roles): Builder {
        if ($roles instanceof Collection) {
            $roles = $roles->all();
        }

        if (! is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) use ($guard) {
            if ($role instanceof Role) {
                return $role;
            }
            $method = is_numeric($role) ? 'findById' : 'findByName';

            return $this->getRoleClass()->{$method}($role);
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere('roles.id', $role->id);
                }
            });
        });
    }

    /**
     * Assign the given role to the model.
     * cap role cho user
     *
     * @param array|string|\Spatie\Permission\Contracts\Role ...$roles
     *
     * @return $this
     */
    public function assignRole(...$roles) {
       
    }

    /**
     * Thu hoi role khoi user
     *
     * @param string|\Spatie\Permission\Contracts\Role $role
     */
    public function removeRole($role) {
        $this->roles()->detach($this->getStoredRole($role));
        return $this;
    }

    /**
     * Thu hoi tat ca role hien tai va cap moi
     *
     * @param  array|\Spatie\Permission\Contracts\Role|string  ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles) {
        $this->roles()->detach();
        return $this->assignRole($roles);
    }

    /**
     * Xac dinh xem user co role khong
     *
     * @param string|int|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     * @return bool
     */
    public function hasRole($roles): bool {
        if(is_string($roles)) {
            return $this->roles->contain('name', $roles);
        }
        if(is_int($roles)) {
            return $this->roles->contain('id', $roles);
        }
        if($roles instanceof Role) {
            return $this->roles->contain('id', $roles->id);
        }
        if(is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }
        return $roles->intersect($this->roles)->isNotEmpty();
    }

    /**
     * Xac dinh xem user co it nhat 1 role trong danh sach khong
     *
     * @param string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles): bool {
        return $this->hasRole($roles);
    }

    /**
     * Xac dinh xem user co tat ca role trong danh sach khong
     *
     * @param  string|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection  $roles
     * @return bool
     */
    public function hasAllRoles($roles): bool {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }
        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->roles->plucks('name')) == $roles;
    }

    /**
     * Return all permissions directly coupled to the model.
     */
    public function getDirectPermissions(): Collection {
        return $this->permissions;
    }

    protected function getStoredRole($role): Role {
        if (is_numeric($role)) {
            return Role::findByid($role);
        }
        if (is_string($role)) {
            return Role::findByName($role);
        }
        return $role;
    }

}

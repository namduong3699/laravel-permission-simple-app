<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

use App\Contracts\Permission as PermissionConstract;
use App\Models\Permission;

trait HasPermissions {
    /**
     * Kiem tra xem user co so huu permission khong
     *
     * @param string|int|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasPermissionTo($permission): bool {

        if (is_string($permission)) {
            $permission = Permission::findByName($permission);
        }
        if (is_int($permission)) {
            $permission = Permission::findById($permission);
        }
        if (!$permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }
        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has, via roles, the given permission.
     * Quyet dinh xem user co permission thong qua role khong
     *
     * @param \Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission): bool {
        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|int|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     * @throws PermissionDoesNotExist
     */
    public function hasDirectPermission($permission): bool {
        if (is_string($permission)) {
            $permission = $permissionClass->findByName($permission);
        }

        if (is_int($permission)) {
            $permission = $permissionClass->findById($permission);
        }

        if (! $permission instanceof Permission) {
            throw new PermissionDoesNotExist;
        }

        return $this->permissions->contains('id', $permission->id);
    }

    /**
     * Co tac dung giong ham hasPermissionTo() nhung khong nem ngoai le
     *
     * @param string|int|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function checkPermissionTo($permission): bool {
        try {
            return $this->hasPermissionTo($permission);
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Check xem user co bat ky permission nao trong danh sach permission khong
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAnyPermission(...$permissions): bool {
        // if (is_array($permissions)) {
            
        // }

        foreach($permissions as $permission) {
            if ($this->checkPermissionTo($permissions)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check user co tat ca permission trong danh sach dua vao khong
     *
     * @param array ...$permissions
     *
     * @return bool
     * @throws \Exception
     */
    public function hasAllPermissions(...$permissions): bool {
        if (is_array($permissions)) {

        }
        foreach($permissions as $permission) {
            if (!$this->checkPermissionTo($permission)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Tra ve permission ma user co thong qua role (user_has_role, role_has_permission)
     */
    public function getPermissionsViaRoles(): Collection {
        $permissions = $this->roles;
        return $permissions->sort()->value();
    }

    /**
     * Tra ve tat ca permission ma user co (user_has_roles, user_has_permission)
     *
     * @throws \Exception
     */
    public function getAllPermissions(): Collection {
        $permissions = $this->permissions;
        if ($this->roles) {
            $permissions = $permissions->merge($this->getPermissionsViaRoles());
        }
        return $permission->sort()->value();
    }

    /**
     * Cap permission cho role
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo(...$permissions) {
         $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->all();
        $this->permissions()->save($permissions);
        return $this;
    }

    /**
     * Xoa tat ca permission dang co va gan lai
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions) {
        $this->permissions()->detach();
        //detach(): xoa ban ghi thich hop ra khoi bang trung gian
        return $this->givePermissionTo($permission);
    }

    /**
     * Thu hoi permission da cap
     *
     * @param \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Permission[]|string|string[] $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission) {
        $this->permissions()->detach($this->getStoredPermission($permission));
        return $this;
    }

    public function getPermissionNames(): Collection {
        
    }

    /**
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Permission|\Spatie\Permission\Contracts\Permission[]|\Illuminate\Support\Collection
     */
    protected function getStoredPermission($permissions) {
        if (is_numeric($permissions)) {
            return Permission::findById($permissions);
        }
        if (is_string($permissions)) {
            return Permission::findByName($permissions);
        }
        if (is_array($permissions)) {
            return Permission::whereIn('name', $permissions)->get();
        }
        return $permissions;
    }

}

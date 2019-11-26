<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Permission {
	/**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
	public function roles(): BelongsToMany;

	/**
     * Find a permission by its name.
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return Permission
     */
	public static function findByName(string $name): self;

	/**
     * Find a permission by its id.
     *
     * @param int $id
     * @param string|null $guardName
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return Permission
     */
	public static function findById(int $id): self;

	/**
     * Find or Create a permission by its name and guard name.
     *
     * @param string $name
     *
     * @return Permission
     */
	public static function findOfCreate(string $name): self;
}
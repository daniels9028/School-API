<?php

namespace App\Services\Admin;

use Spatie\Permission\Models\Role;

class RoleService
{
    public function getAll()
    {
        return Role::all();
    }

    public function store(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    public function assignPermission(Role $role, array $data): Role
    {
        $role->syncPermissions($data['permissions']);

        return $role;
    }
}

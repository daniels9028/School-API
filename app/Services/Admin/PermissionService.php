<?php

namespace App\Services\Admin;

use Spatie\Permission\Models\Permission;

class PermissionService
{
    public function getAll()
    {
        return Permission::all();
    }

    public function store(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission;
    }

    public function delete(Permission $permission): bool
    {
        return $permission->delete();
    }
}

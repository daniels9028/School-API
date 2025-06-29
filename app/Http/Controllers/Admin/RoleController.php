<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\AssignPermissionRequest;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'All roles retrieved successfully.',
            'data'    => $this->roleService->getAll()
        ],);
    }

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
            'data'    => $role,
        ], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $updatedRole = $this->roleService->update($role, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'data'    => $updatedRole,
        ]);
    }

    public function destroy(Role $role)
    {
        $this->roleService->delete($role);

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.',
            'data'    => null,
        ]);
    }

    public function assignPermissions(AssignPermissionRequest $request, Role $role)
    {
        $role = $this->roleService->assignPermission($role, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permissions assigned to role.',
            'data'    => [
                'role' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }
}

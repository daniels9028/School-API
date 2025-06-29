<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Permission\StorePermissionRequest;
use App\Http\Requests\Admin\Permission\UpdatePermissionRequest;
use App\Services\Admin\PermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $permissionService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'All permissions retrieved successfully.',
            'data'    => $this->permissionService->getAll()
        ],);
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = $this->permissionService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully.',
            'data'    => $permission,
        ], 201);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        $updatedPermission = $this->permissionService->update($permission, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully.',
            'data'    => $updatedPermission,
        ]);
    }

    public function destroy(Permission $permission)
    {
        $this->permissionService->delete($permission);

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted successfully.',
            'data'    => null,
        ]);
    }
}

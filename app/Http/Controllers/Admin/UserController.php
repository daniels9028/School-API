<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\AssignRoleRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $this->userService->getAll()
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->store($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->update($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
            'data' => null
        ]);
    }

    public function assignRole(AssignRoleRequest $request, User $user)
    {
        $user = $this->userService->assignRole($user, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Role assigned to user.',
            'data' => [
                'user' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ]
        ]);
    }
}

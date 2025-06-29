<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        $permissions = [
            'manage users',
            'view dashboard',
            'create class',
            'manage students',
            'manage teachers',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles
        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $student = Role::firstOrCreate(['name' => 'student']);

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
        $teacher->givePermissionTo(['create class', 'view dashboard']);
        $student->givePermissionTo(['view dashboard']);

        // Assign role to first user
        $user = User::create([
            'name'     => 'admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $user->assignRole('admin');
    }
}

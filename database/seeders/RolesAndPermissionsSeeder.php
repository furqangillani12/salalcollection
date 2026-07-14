<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'view dashboard',
            'manage employees',
            'manage attendance',
            'manage products',
            'manage variants',
            'manage categories',
            'manage inventory',
            'manage purchases',
            'manage suppliers',
            'access pos',
            'view reports',
            'manage roles',
            'manage permissions',
            'assign roles',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define roles with permissions
        $roles = [
            'admin' => $permissions,
            'manager' => [
                'view dashboard',
                'manage employees',
                'manage attendance',
                'manage products',
                'manage variants',
                'manage categories',
                'manage inventory',
                'manage purchases',
                'manage suppliers',
                'access pos',
                'view reports',
            ],
            'cashier' => [
                'access pos',
                'view dashboard',
                'manage products',
                'view reports',
            ],
            'employee' => [
                'view dashboard',
                'manage attendance',
            ],
        ];

        // Create roles and assign permissions
        foreach ($roles as $role => $rolePermissions) {
            $roleInstance = Role::firstOrCreate(['name' => $role]);
            $roleInstance->syncPermissions($rolePermissions);
        }
    }
}


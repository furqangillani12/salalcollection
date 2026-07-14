<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up()
    {
        // Create permissions
        $permissions = [
            'manage credit',
            'view credit dashboard',
            'enable credit',
            'disable credit',
            'collect credit payment',
            'view credit statement',
            'export credit statement',
            'view overdue report'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
    }

    public function down()
    {
        $permissions = [
            'manage credit',
            'view credit dashboard',
            'enable credit',
            'disable credit',
            'collect credit payment',
            'view credit statement',
            'export credit statement',
            'view overdue report'
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
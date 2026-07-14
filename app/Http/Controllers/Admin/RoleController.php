<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        $totalUsers = User::count();
        $totalPermissions = Permission::count();

        return view('roles.index', compact('roles', 'totalUsers', 'totalPermissions'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $grouped = $permissions->groupBy(function ($p) {
            $parts = explode(' ', $p->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        return view('roles.create', compact('permissions', 'grouped'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array|nullable',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" created successfully.");
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $grouped = $permissions->groupBy(function ($p) {
            $parts = explode(' ', $p->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'grouped'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array|nullable',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', "Role \"{$role->name}\" updated successfully.");
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', "Cannot delete role \"{$role->name}\" — it has {$role->users()->count()} user(s) assigned.");
        }

        $role->delete();
        return back()->with('success', "Role \"{$role->name}\" deleted.");
    }
}

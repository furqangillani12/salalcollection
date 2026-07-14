<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function assignRoleForm()
    {
        $users = User::with(['roles', 'branch'])->orderBy('name')->get();
        $roles = Role::withCount('users')->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('users.assign_role', compact('users', 'roles', 'branches'));
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->syncRoles([$request->role]);
        $user->update(['branch_id' => $request->branch_id]);

        $branchName = $request->branch_id
            ? Branch::find($request->branch_id)->name
            : 'All (Admin)';

        return redirect()->back()->with('success', "\"{$user->name}\" assigned role \"{$request->role}\" — Branch: {$branchName}");
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use BranchScoped;
    public function index(Request $request)
    {
        $query = $this->scopeBranch(Employee::query())->with(['user.roles']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('user.roles', fn ($q) => $q->where('name', $role));
        }

        $employees = $query->latest()->paginate(12)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $user->assignRole($request->role);

        $branchId = $this->branchId();

        Employee::create([
            'user_id' => $user->id,
            'branch_id' => ($branchId && $branchId !== 'all') ? $branchId : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'salary' => $request->salary,
            'joining_date' => $request->joining_date
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user.roles', 'branch']);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $roles = Role::all();
        $employee->load(['user.roles', 'branch']);
        return view('admin.employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'role' => 'required|exists:roles,name'
        ]);

        $user = $employee->user;
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }
        $user->update($userData);
        $user->syncRoles([$request->role]);

        $employee->update([
            'phone' => $request->phone,
            'address' => $request->address,
            'salary' => $request->salary,
            'joining_date' => $request->joining_date
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete(); // relations will auto-delete
        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully');
    }

}

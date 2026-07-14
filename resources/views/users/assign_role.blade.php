@extends('layouts.admin')

@section('title', 'Assign Roles & Branches')

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div>
            <h2 class="text-xl font-bold text-gray-800">Assign Roles & Branches</h2>
            <p class="text-sm text-gray-500">Manage user role and branch assignments</p>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Assign Form --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden sticky top-5">
                    <div class="px-5 py-4 border-b bg-gray-50">
                        <h3 class="font-semibold text-gray-700 text-sm">Assign Role & Branch</h3>
                    </div>
                    <form action="{{ route('users.assign_role') }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <div>
                            <label for="user_id" class="block text-xs font-medium text-gray-500 mb-1">Select User</label>
                            <select name="user_id" id="user_id"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">-- Choose User --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" data-branch="{{ $user->branch_id }}">
                                        {{ $user->name }}
                                        @if($user->roles->count())
                                            ({{ $user->roles->pluck('name')->join(', ') }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="role" class="block text-xs font-medium text-gray-500 mb-1">Select Role</label>
                            <select name="role" id="role"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">-- Choose Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }} ({{ $role->users_count }} users)</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="branch_id" class="block text-xs font-medium text-gray-500 mb-1">Assign Branch</label>
                            <select name="branch_id" id="branch_id"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">No branch (Admin — can switch)</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">
                                <i class="fas fa-info-circle"></i>
                                Users with a branch can only access that branch. Leave empty for admin access to all branches.
                            </p>
                        </div>

                        <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition">
                            <i class="fas fa-user-tag mr-1"></i> Assign Role & Branch
                        </button>
                    </form>
                </div>
            </div>

            {{-- Users List --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b bg-gray-50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700 text-sm">All Users ({{ $users->count() }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                                <tr>
                                    <th class="px-5 py-3 text-left">User</th>
                                    <th class="px-5 py-3 text-left">Email</th>
                                    <th class="px-5 py-3 text-left">Role</th>
                                    <th class="px-5 py-3 text-left">Branch</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($users as $user)
                                    @php
                                        $roleColors = [
                                            'admin' => 'bg-red-100 text-red-700',
                                            'manager' => 'bg-purple-100 text-purple-700',
                                            'cashier' => 'bg-blue-100 text-blue-700',
                                            'employee' => 'bg-green-100 text-green-700',
                                        ];
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-gray-800">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3 text-gray-500">{{ $user->email }}</td>
                                        <td class="px-5 py-3">
                                            @forelse($user->roles as $r)
                                                @php $rc = $roleColors[strtolower($r->name)] ?? 'bg-gray-100 text-gray-700'; @endphp
                                                <span class="inline-block {{ $rc }} text-xs font-bold px-2.5 py-1 rounded-full capitalize">{{ $r->name }}</span>
                                            @empty
                                                <span class="text-xs text-gray-400 italic">No role</span>
                                            @endforelse
                                        </td>
                                        <td class="px-5 py-3">
                                            @if($user->branch)
                                                <span class="inline-flex items-center gap-1 bg-indigo-50 text-indigo-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                    <i class="fas fa-store text-[10px]"></i> {{ $user->branch->name }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-semibold px-2.5 py-1 rounded-full">
                                                    <i class="fas fa-globe text-[10px]"></i> All Branches
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Role Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-{{ min($roles->count(), 6) }} gap-3">
            @foreach($roles as $role)
                @php
                    $colors = [
                        'admin' => 'border-red-200 bg-red-50 text-red-700',
                        'manager' => 'border-purple-200 bg-purple-50 text-purple-700',
                        'cashier' => 'border-blue-200 bg-blue-50 text-blue-700',
                        'employee' => 'border-green-200 bg-green-50 text-green-700',
                    ];
                    $c = $colors[strtolower($role->name)] ?? 'border-gray-200 bg-gray-50 text-gray-700';
                @endphp
                <div class="border rounded-xl p-4 text-center {{ $c }}">
                    <p class="text-2xl font-bold">{{ $role->users_count }}</p>
                    <p class="text-xs font-medium capitalize mt-0.5">{{ $role->name }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        // Auto-select branch when user is selected
        document.getElementById('user_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const branchId = selected.getAttribute('data-branch') || '';
            document.getElementById('branch_id').value = branchId;
        });
    </script>
@endsection

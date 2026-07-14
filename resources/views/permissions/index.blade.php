@extends('layouts.admin')

@section('title', 'Manage Permissions')

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Permissions</h2>
                <p class="text-sm text-gray-500">{{ $permissions->count() }} permissions across {{ $grouped->count() }} groups</p>
            </div>
            <a href="{{ route('permissions.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> New Permission
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Permission Groups --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($grouped as $group => $perms)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-3 border-b bg-gray-50 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700 text-sm capitalize flex items-center gap-2">
                            @php
                                $icons = [
                                    'manage' => 'fa-cog', 'access' => 'fa-key', 'view' => 'fa-eye',
                                    'enable' => 'fa-toggle-on', 'disable' => 'fa-toggle-off',
                                    'collect' => 'fa-hand-holding-usd', 'export' => 'fa-file-export',
                                    'assign' => 'fa-user-tag',
                                ];
                                $icon = $icons[$group] ?? 'fa-shield-alt';
                            @endphp
                            <i class="fas {{ $icon }} text-gray-400 text-xs"></i>
                            {{ $group }}
                        </h3>
                        <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full">{{ $perms->count() }}</span>
                    </div>
                    <ul class="divide-y divide-gray-50">
                        @foreach($perms as $permission)
                            <li class="px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                                    <span class="text-sm text-gray-700">{{ $permission->name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    {{-- Show which roles have this permission --}}
                                    @php
                                        $assignedRoles = $roles->filter(fn($r) => $r->permissions->contains('name', $permission->name));
                                    @endphp
                                    @foreach($assignedRoles as $r)
                                        <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded font-medium">{{ $r->name }}</span>
                                    @endforeach
                                    <a href="{{ route('permissions.edit', $permission) }}"
                                       class="w-7 h-7 rounded-lg bg-gray-100 hover:bg-blue-100 text-gray-400 hover:text-blue-600 flex items-center justify-center transition"
                                       title="Edit">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        {{-- Role-Permission Matrix --}}
        @if($roles->count() && $permissions->count())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b bg-gray-50">
                    <h3 class="font-semibold text-gray-700">Role-Permission Matrix</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Overview of which roles have which permissions</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600 sticky left-0 bg-gray-50 min-w-[180px]">Permission</th>
                                @foreach($roles as $r)
                                    <th class="px-3 py-3 text-center font-semibold text-gray-600 capitalize min-w-[80px]">{{ $r->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($permissions as $permission)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2.5 text-gray-700 font-medium sticky left-0 bg-white">{{ $permission->name }}</td>
                                    @foreach($roles as $r)
                                        <td class="px-3 py-2.5 text-center">
                                            @if($r->permissions->contains('name', $permission->name))
                                                <span class="text-green-500"><i class="fas fa-check-circle"></i></span>
                                            @else
                                                <span class="text-gray-200"><i class="fas fa-minus-circle"></i></span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

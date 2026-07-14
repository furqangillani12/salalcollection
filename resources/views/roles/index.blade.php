@extends('layouts.admin')

@section('title', 'Manage Roles')

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Roles & Access</h2>
                <p class="text-sm text-gray-500">{{ $roles->count() }} roles — {{ $totalUsers }} users — {{ $totalPermissions }} permissions</p>
            </div>
            <a href="{{ route('roles.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> New Role
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Role Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($roles as $role)
                @php
                    $colors = [
                        'admin' => ['bg-red-100', 'text-red-700', 'border-red-200', 'bg-red-50'],
                        'manager' => ['bg-purple-100', 'text-purple-700', 'border-purple-200', 'bg-purple-50'],
                        'cashier' => ['bg-blue-100', 'text-blue-700', 'border-blue-200', 'bg-blue-50'],
                        'employee' => ['bg-green-100', 'text-green-700', 'border-green-200', 'bg-green-50'],
                    ];
                    $c = $colors[strtolower($role->name)] ?? ['bg-gray-100', 'text-gray-700', 'border-gray-200', 'bg-gray-50'];
                    $permCount = $role->permissions->count();
                    $pct = $totalPermissions > 0 ? round(($permCount / $totalPermissions) * 100) : 0;
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    {{-- Role Header --}}
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ $c[0] }} {{ $c[1] }} flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($role->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 capitalize">{{ $role->name }}</h3>
                                    <p class="text-xs text-gray-400">{{ $role->users_count }} user(s) assigned</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('roles.edit', $role) }}"
                                   class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-blue-100 text-gray-500 hover:text-blue-600 flex items-center justify-center transition"
                                   title="Edit">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                @if($role->users_count === 0)
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST"
                                          onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 flex items-center justify-center transition"
                                                title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        {{-- Permission Progress --}}
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500">{{ $permCount }}/{{ $totalPermissions }} permissions</span>
                                <span class="font-semibold {{ $c[1] }}">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ str_replace('text-', 'bg-', explode(' ', $c[1])[0]) }}-500 transition-all"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Permissions Tags --}}
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($role->permissions as $permission)
                                <span class="inline-block {{ $c[3] }} {{ $c[1] }} text-[10px] font-medium px-2 py-0.5 rounded-full border {{ $c[2] }}">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">No permissions assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Empty State --}}
        @if($roles->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <i class="fas fa-shield-alt text-4xl text-gray-300 mb-3"></i>
                <p class="font-medium text-gray-600 mb-1">No roles found</p>
                <p class="text-sm text-gray-400">Create your first role to get started.</p>
            </div>
        @endif
    </div>
@endsection

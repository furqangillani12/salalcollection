@extends('layouts.admin')

@section('title', 'Branch Management')

@section('content')
    <div class="space-y-5">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Branches</h2>
                <p class="text-sm text-gray-500">{{ $branches->count() }} branches configured</p>
            </div>
            @if(!auth()->user()->branch_id)
                <a href="{{ route('admin.branches.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> New Branch
                </a>
            @endif
        </div>

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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($branches as $branch)
                @php
                    $isActive = $branch->is_active;
                    $isCurrent = isset($currentBranch) && $currentBranch !== 'all' && $currentBranch->id === $branch->id;
                @endphp
                <div class="bg-white rounded-xl shadow-sm border {{ $isCurrent ? 'border-blue-300 ring-2 ring-blue-100' : 'border-gray-100' }} overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg {{ $isActive ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-400' }} flex items-center justify-center text-lg font-bold">
                                    {{ strtoupper(substr($branch->code ?? $branch->name, 0, 2)) }}
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                                        {{ $branch->name }}
                                        @if($isCurrent)
                                            <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full">CURRENT</span>
                                        @endif
                                    </h3>
                                    @if($branch->code)
                                        <p class="text-xs text-gray-400">Code: {{ $branch->code }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $isActive ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    {{ $isActive ? 'Active' : 'Inactive' }}
                                </span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $branch->show_on_website ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                    <i class="fas {{ $branch->show_on_website ? 'fa-globe' : 'fa-eye-slash' }} mr-0.5"></i> {{ $branch->show_on_website ? 'On web' : 'Off web' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="bg-gray-50 rounded-lg p-2.5 text-center">
                                <p class="text-lg font-bold text-gray-700">{{ $branch->orders_count ?? 0 }}</p>
                                <p class="text-[10px] text-gray-500">Orders</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 text-center">
                                <p class="text-lg font-bold text-gray-700">{{ $branch->employees_count ?? 0 }}</p>
                                <p class="text-[10px] text-gray-500">Employees</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2.5 text-center">
                                <p class="text-lg font-bold text-gray-700">{{ $branch->users_count ?? 0 }}</p>
                                <p class="text-[10px] text-gray-500">Users</p>
                            </div>
                        </div>

                        @if($branch->address || $branch->phone)
                            <div class="text-sm text-gray-500 space-y-1 mb-4">
                                @if($branch->address)
                                    <p><i class="fas fa-map-marker-alt text-xs text-gray-400 mr-1"></i> {{ $branch->address }}</p>
                                @endif
                                @if($branch->phone)
                                    <p><i class="fas fa-phone text-xs text-gray-400 mr-1"></i> {{ $branch->phone }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center gap-2 pt-3 border-t">
                            <a href="{{ route('admin.branches.edit', $branch) }}"
                               class="bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-700 text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            @if(!auth()->user()->branch_id)
                                <form method="POST" action="{{ route('admin.branches.toggle', $branch) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="bg-gray-100 hover:bg-yellow-100 text-gray-700 hover:text-yellow-700 text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                        <i class="fas fa-{{ $isActive ? 'toggle-off' : 'toggle-on' }}"></i>
                                        {{ $isActive ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.branches.toggle-website', $branch) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="bg-gray-100 hover:bg-blue-100 text-gray-700 hover:text-blue-700 text-xs px-3 py-1.5 rounded-lg font-medium transition"
                                            title="Show/hide this branch on the website">
                                        <i class="fas fa-{{ $branch->show_on_website ? 'eye-slash' : 'globe' }}"></i>
                                        {{ $branch->show_on_website ? 'Hide web' : 'Show web' }}
                                    </button>
                                </form>
                                @if(($branch->orders_count ?? 0) === 0)
                                    <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}"
                                          onsubmit="return confirm('Delete branch {{ $branch->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="bg-gray-100 hover:bg-red-100 text-gray-700 hover:text-red-700 text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

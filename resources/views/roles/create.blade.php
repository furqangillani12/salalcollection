@extends('layouts.admin')

@section('title', 'Create Role')

@section('content')
    <div class="max-w-3xl mx-auto space-y-5">

        {{-- Back --}}
        <a href="{{ route('roles.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Roles
        </a>

        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Create New Role</h2>
                <p class="text-xs text-gray-500 mt-0.5">Define a role and assign permissions to it</p>
            </div>

            {{-- Errors --}}
            @if($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('roles.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                {{-- Role Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Role Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="e.g. Manager, Cashier, Warehouse..."
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>

                {{-- Permissions by Group --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">Permissions</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleAll(true)" class="text-xs text-blue-600 hover:underline">Select All</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" onclick="toggleAll(false)" class="text-xs text-gray-500 hover:underline">Clear All</button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach($grouped as $group => $perms)
                            <div class="bg-gray-50 rounded-lg border border-gray-100 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ ucfirst($group) }}</h4>
                                    <button type="button" onclick="toggleGroup('{{ $group }}')" class="text-[10px] text-blue-500 hover:underline">Toggle</button>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($perms as $permission)
                                        <label class="flex items-center gap-2 px-3 py-2 bg-white rounded-lg border border-gray-100 hover:border-blue-200 cursor-pointer transition group">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                   data-group="{{ $group }}"
                                                   {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-300">
                                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $permission->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-3 border-t">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-check mr-1"></i> Create Role
                    </button>
                    <a href="{{ route('roles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleAll(state) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = state);
        }
        function toggleGroup(group) {
            const boxes = document.querySelectorAll(`input[data-group="${group}"]`);
            const allChecked = [...boxes].every(cb => cb.checked);
            boxes.forEach(cb => cb.checked = !allChecked);
        }
    </script>
@endsection

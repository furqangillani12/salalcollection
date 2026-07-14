@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Unit: {{ $unit->name }}</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('units.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Back to Units
                </a>
                <a href="{{ route('units.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Add New
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($unit->products()->count() > 0)
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded mb-4">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    This unit is being used by <strong>{{ $unit->products()->count() }}</strong> product(s).
                    Changing it will affect all associated products.
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <form action="{{ route('units.update', $unit->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <!-- Unit Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Unit Name *</label>
                            <input type="text" name="name" id="name" required
                                value="{{ old('name', $unit->name) }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Abbreviation -->
                        <div>
                            <label for="abbreviation" class="block text-sm font-medium text-gray-700">Abbreviation</label>
                            <input type="text" name="abbreviation" id="abbreviation"
                                value="{{ old('abbreviation', $unit->abbreviation) }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('abbreviation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description', $unit->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $unit->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active Unit
                            </label>
                        </div>
                        <p class="text-sm text-gray-500">Inactive units won't appear in dropdowns for new products</p>

                        <!-- Products using this unit -->
                        @if ($unit->products()->count() > 0)
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-700 mb-3">Products using this unit:</h3>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                        ID</th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                        Product Name</th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                        Stock</th>
                                                    <th
                                                        class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                        Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach ($unit->products()->limit(10)->get() as $product)
                                                    <tr>
                                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $product->id }}
                                                        </td>
                                                        <td class="px-4 py-2 text-sm">
                                                            <a href="{{ route('products.edit', $product->id) }}"
                                                                class="text-blue-500 hover:text-blue-700">
                                                                {{ $product->name }}
                                                            </a>
                                                        </td>
                                                        <td class="px-4 py-2 text-sm text-gray-500">
                                                            {{ $product->stock_quantity }}</td>
                                                        <td class="px-4 py-2">
                                                            <span
                                                                class="px-2 py-1 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if ($unit->products()->count() > 10)
                                        <p class="mt-3 text-sm text-gray-500">
                                            ... and {{ $unit->products()->count() - 10 }} more products
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('units.index') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                Cancel
                            </a>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path
                                        d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                                </svg>
                                Update Unit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Create New Unit</h1>
            <a href="{{ route('units.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
                Back to Units
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf

                    <div class="space-y-6">
                        <!-- Unit Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Unit Name *</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">e.g., Piece, Kilogram, Dozen, Liter, Meter</p>
                        </div>

                        <!-- Abbreviation -->
                        <div>
                            <label for="abbreviation" class="block text-sm font-medium text-gray-700">Abbreviation</label>
                            <input type="text" name="abbreviation" id="abbreviation" value="{{ old('abbreviation') }}"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('abbreviation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Short form, e.g., pcs, kg, dz, L, m</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Optional description of the unit</p>
                        </div>

                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                Active Unit
                            </label>
                        </div>
                        <p class="text-sm text-gray-500">Inactive units won't appear in dropdowns for new products</p>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <a href="{{ route('units.index') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                                Cancel
                            </a>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Create Unit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Common Units Quick Reference -->
        <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-800">Common Units Reference</h2>
                <p class="text-sm text-gray-500">Here are some commonly used units:</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-medium text-gray-700">Quantity</h3>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600">
                            <li>• Piece (pcs)</li>
                            <li>• Dozen (dz)</li>
                            <li>• Pair (pr)</li>
                            <li>• Set (set)</li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-medium text-gray-700">Weight</h3>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600">
                            <li>• Kilogram (kg)</li>
                            <li>• Gram (g)</li>
                            <li>• Milligram (mg)</li>
                            <li>• Pound (lb)</li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="font-medium text-gray-700">Volume/Length</h3>
                        <ul class="mt-2 space-y-1 text-sm text-gray-600">
                            <li>• Liter (L)</li>
                            <li>• Milliliter (mL)</li>
                            <li>• Meter (m)</li>
                            <li>• Centimeter (cm)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

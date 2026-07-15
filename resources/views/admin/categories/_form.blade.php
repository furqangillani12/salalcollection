<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Category Name *</label>
        <input type="text" name="name" id="name" required
               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
               value="{{ old('name', $category->name ?? '') }}">
        @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="3"
                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>


    <div>
        <label class="block text-sm font-medium text-gray-700">Category image</label>
        @if (!empty($category) && $category->photo)
            <img src="{{ asset('storage/' . $category->photo) }}" alt="" class="w-20 h-20 object-cover rounded-lg border mt-1 mb-2">
        @endif
        <input type="file" name="photo" accept="image/png,image/jpeg,image/webp"
               class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        <p class="mt-1 text-xs text-gray-500">PNG/JPG/WebP, up to 2 MB. Shown on the storefront category menu and cards.</p>
        @error('photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <p class="text-xs text-gray-500">New categories show on the website by default. Use the <span class="font-medium">Website</span> toggle in the list to hide one.</p>
</div>

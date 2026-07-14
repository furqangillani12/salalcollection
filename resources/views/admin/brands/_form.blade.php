@csrf
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
        <input type="text" name="name" required value="{{ old('name', $brand->name ?? '') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Description</label>
        <textarea name="description" rows="3" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">{{ old('description', $brand->description ?? '') }}</textarea>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Logo</label>
        <input type="file" name="logo" accept="image/*" class="w-full text-sm">
        @if (!empty($brand?->logo))
            <img src="{{ asset('storage/' . $brand->logo) }}" class="mt-2 h-16 rounded border">
        @endif
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sort order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="flex items-center gap-4">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', ($brand->is_active ?? true)) ? 'checked' : '' }} class="rounded text-cyan-600">
            Active
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', ($brand->is_featured ?? false)) ? 'checked' : '' }} class="rounded text-cyan-600">
            Featured
        </label>
    </div>
</div>
<div class="mt-6 flex gap-3 justify-end">
    <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-sm font-semibold">Cancel</a>
    <button class="px-6 py-2.5 text-white rounded-lg text-sm font-semibold shadow-sm" style="background:linear-gradient(135deg,#0891b2,#0e7490);"><i class="fas fa-check"></i> Save</button>
</div>

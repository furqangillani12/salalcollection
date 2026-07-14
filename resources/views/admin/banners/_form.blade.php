@csrf
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Title <span class="text-gray-400 font-normal">(optional)</span></label>
        <input type="text" name="title" value="{{ old('title', $banner->title ?? '') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Subtitle</label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle ?? '') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">CTA text</label>
        <input type="text" name="cta_text" value="{{ old('cta_text', $banner->cta_text ?? '') }}" placeholder="Shop now" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">CTA URL</label>
        <input type="text" name="cta_url" value="{{ old('cta_url', $banner->cta_url ?? '') }}" placeholder="/shop" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Position <span class="text-rose-500">*</span></label>
        <select name="position" required class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
            @foreach (['hero','mid','side','footer'] as $pos)
                <option value="{{ $pos }}" @selected(old('position', $banner->position ?? 'hero') === $pos)>{{ ucfirst($pos) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Sort order</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $banner->sort_order ?? 0) }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Image URL (or upload below)</label>
        <input type="text" name="image" value="{{ old('image', $banner->image ?? '') }}" placeholder="https://... or banners/banner.jpg" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm">
    </div>
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Upload image</label>
        <input type="file" name="image_file" accept="image/*" class="w-full text-sm">
        @if (!empty($banner?->image))
            <img src="{{ shop_image($banner->image) }}" class="mt-2 max-h-32 rounded border">
        @endif
    </div>
    <div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', ($banner->is_active ?? true)) ? 'checked' : '' }} class="rounded text-cyan-600">
            Active
        </label>
    </div>
</div>
<div class="mt-6 flex gap-3 justify-end">
    <a href="{{ route('admin.banners.index') }}" class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-sm font-semibold">Cancel</a>
    <button class="px-6 py-2.5 text-white rounded-lg text-sm font-semibold shadow-sm" style="background:linear-gradient(135deg,#0891b2,#0e7490);"><i class="fas fa-check"></i> Save</button>
</div>

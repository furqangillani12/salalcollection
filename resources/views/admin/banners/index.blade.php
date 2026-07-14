@extends('layouts.admin')
@section('title', 'Banners')
@section('content')
<div class="p-3 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-image text-cyan-600"></i> Banners</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Storefront promotional banners (hero, mid, side, footer).</p>
        </div>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg text-sm font-semibold shadow-sm" style="background:linear-gradient(135deg,#0891b2,#0e7490);">
            <i class="fas fa-plus"></i> Add Banner
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200 text-sm"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] uppercase tracking-wide text-gray-600">
                    <th class="px-4 py-3">Banner</th>
                    <th class="px-4 py-3">Position</th>
                    <th class="px-4 py-3">CTA</th>
                    <th class="px-4 py-3">Active</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($banners as $b)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <img src="{{ shop_image($b->image) }}" class="w-20 h-12 rounded object-cover bg-gray-50">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $b->title }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $b->subtitle }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 capitalize text-xs">{{ $b->position }} ({{ $b->sort_order }})</td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            @if ($b->cta_text) <strong>{{ $b->cta_text }}</strong><br><span class="text-gray-400 truncate inline-block max-w-[200px]">{{ $b->cta_url }}</span> @else - @endif
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.banners.toggle', $b) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="{{ $b->is_active ? 'text-emerald-600' : 'text-gray-400' }}"><i class="fas fa-{{ $b->is_active ? 'toggle-on' : 'toggle-off' }} text-lg"></i></button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.banners.edit', $b) }}" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-medium border border-amber-200"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('admin.banners.destroy', $b) }}" method="POST" class="inline" onsubmit="return confirm('Delete?')">
                                @csrf @method('DELETE')
                                <button class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-medium border border-rose-200"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-image text-3xl mb-2 block"></i> No banners yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($banners->hasPages())<div class="px-4 py-3 border-t border-gray-200">{{ $banners->links() }}</div>@endif
    </div>
</div>
@endsection

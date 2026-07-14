@extends('layouts.admin')
@section('title', 'Brands')
@section('content')
<div class="p-3 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-tag text-cyan-600"></i> Brands</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage product brands shown on the storefront.</p>
        </div>
        <a href="{{ route('admin.brands.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg text-sm font-semibold shadow-sm" style="background:linear-gradient(135deg,#0891b2,#0e7490);">
            <i class="fas fa-plus"></i> Add Brand
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200 text-sm"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left text-[11px] uppercase tracking-wide text-gray-600">
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3 text-center">Products</th>
                    <th class="px-4 py-3">Featured</th>
                    <th class="px-4 py-3">Active</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($brands as $b)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($b->logo)
                                    <img src="{{ asset('storage/' . $b->logo) }}" class="w-10 h-10 rounded object-contain bg-gray-50 border border-gray-100">
                                @else
                                    <span class="w-10 h-10 rounded bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($b->name, 0, 2)) }}</span>
                                @endif
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $b->name }}</div>
                                    <div class="text-[11px] text-gray-500 truncate max-w-xs">{{ $b->description }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-gray-500">{{ $b->slug }}</td>
                        <td class="px-4 py-3 text-center">{{ $b->products_count }}</td>
                        <td class="px-4 py-3">
                            @if ($b->is_featured) <span class="chip" style="background:#fef3c7;color:#92400e;">Featured</span> @else - @endif
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.brands.toggle', $b) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button class="{{ $b->is_active ? 'text-emerald-600' : 'text-gray-400' }}">
                                    <i class="fas fa-{{ $b->is_active ? 'toggle-on' : 'toggle-off' }} text-lg"></i>
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.brands.edit', $b) }}" class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-medium border border-amber-200" title="Edit"><i class="fas fa-pen"></i></a>
                            <form action="{{ route('admin.brands.destroy', $b) }}" method="POST" class="inline" onsubmit="return confirm('Delete brand?')">
                                @csrf @method('DELETE')
                                <button class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-medium border border-rose-200"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-tag text-3xl mb-2 block"></i> No brands yet. <a href="{{ route('admin.brands.create') }}" class="text-cyan-600 underline">Add one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($brands->hasPages())<div class="px-4 py-3 border-t border-gray-200">{{ $brands->links() }}</div>@endif
    </div>
</div>
@endsection
@push('styles')<style>.chip{display:inline-flex;align-items:center;padding:2px 10px;border-radius:9999px;font-size:11px;font-weight:600;}</style>@endpush

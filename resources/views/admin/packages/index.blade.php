@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Packages / پیکجز</h1>
            <p class="text-sm text-gray-500 mt-1">Bundle products at a special price — applied as a named discount on the receipt</p>
        </div>
        <a href="{{ route('admin.packages.create') }}"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
            <i class="fas fa-plus"></i> New Package
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    @if($packages->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-400">
            <i class="fas fa-box-open text-4xl mb-3 block opacity-30"></i>
            No packages yet. <a href="{{ route('admin.packages.create') }}" class="text-blue-500 hover:underline">Create one</a>
        </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($packages as $pkg)
        <div class="bg-white rounded-xl shadow-sm border {{ $pkg->is_active ? 'border-gray-100' : 'border-gray-200 opacity-60' }} overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-2">
                <div>
                    <h3 class="font-bold text-gray-800">{{ $pkg->name }}</h3>
                    @if($pkg->code)
                    <span class="text-xs font-mono text-gray-400">{{ $pkg->code }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if(!$pkg->is_active)
                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                    @endif
                    <a href="{{ route('admin.packages.edit', $pkg) }}"
                        class="text-yellow-500 hover:text-yellow-700 text-sm"><i class="fas fa-edit"></i></a>
                </div>
            </div>

            {{-- Items --}}
            <div class="px-5 py-3 space-y-1.5">
                @foreach($pkg->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">{{ $item->product?->name ?? 'Unknown' }} × {{ $item->quantity }}</span>
                    <span class="text-gray-400 text-xs">
                        Cost: Rs.{{ number_format($item->product ? $item->product->cost_price * $item->quantity : 0, 0) }}
                        · Sale: Rs.{{ number_format($item->product ? $item->product->sale_price * $item->quantity : 0, 0) }}
                    </span>
                </div>
                @endforeach
            </div>

            {{-- Pricing summary --}}
            <div class="px-5 py-3 bg-blue-50 border-t border-blue-100 text-sm space-y-1">
                <div class="flex justify-between text-gray-600">
                    <span>Retail Total (individual prices)</span>
                    <span>Rs. {{ number_format($pkg->retail_total, 0) }}</span>
                </div>
                <div class="flex justify-between text-green-700 font-semibold">
                    <span>Package Sale Price</span>
                    <span>Rs. {{ number_format($pkg->sale_price, 0) }}</span>
                </div>
                @if($pkg->discount_amount > 0)
                <div class="flex justify-between text-orange-600 text-xs">
                    <span>Customer saves</span>
                    <span>Rs. {{ number_format($pkg->discount_amount, 0) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-gray-500 text-xs border-t border-blue-100 pt-1 mt-1">
                    <span>Cost price</span>
                    <span>Rs. {{ number_format($pkg->cost_price, 0) }}</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="px-5 py-3 border-t border-gray-100 flex gap-2">
                <form action="{{ route('admin.packages.toggle', $pkg) }}" method="POST" class="flex-1">
                    @csrf @method('PATCH')
                    <button class="w-full text-xs py-1.5 rounded border
                        {{ $pkg->is_active ? 'border-gray-200 text-gray-500 hover:bg-gray-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                        {{ $pkg->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                <form action="{{ route('admin.packages.destroy', $pkg) }}" method="POST"
                    onsubmit="return confirm('Delete this package?')">
                    @csrf @method('DELETE')
                    <button class="text-xs py-1.5 px-3 rounded border border-red-200 text-red-500 hover:bg-red-50">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

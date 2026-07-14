@extends('layouts.admin')

@section('title', 'Create Branch')

@section('content')
    <div class="max-w-xl mx-auto space-y-5">

        <a href="{{ route('admin.branches.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Branches
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">New Branch</h2>
                <p class="text-xs text-gray-500 mt-0.5">Add a new store/branch location</p>
            </div>

            @if($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.branches.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. Salal Collection Downtown"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Code</label>
                    <input type="text" name="code" value="{{ old('code') }}"
                           placeholder="e.g. ASM, AMM, AMB (used as order number prefix)"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="text-xs text-gray-400 mt-1">Short code used as order number prefix (e.g. ASM313, AMM313)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Start Number</label>
                    <input type="number" name="order_start_number" value="{{ old('order_start_number') }}"
                           placeholder="e.g. 313 (orders will start from this number)"
                           min="1"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="text-xs text-gray-400 mt-1">Orders for this branch will start from this number (e.g. Code AMM + Start 313 = AMM313, AMM314...)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           placeholder="Branch address"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           placeholder="Branch phone number"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Branch Logo</label>
                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp"
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <p class="text-xs text-gray-400 mt-1">Upload branch logo (PNG, JPG, WEBP - max 2MB). Shown on receipts.</p>
                </div>

                <div class="flex items-center gap-3 pt-3 border-t">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-check mr-1"></i> Create Branch
                    </button>
                    <a href="{{ route('admin.branches.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

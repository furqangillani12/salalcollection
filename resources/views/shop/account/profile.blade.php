@extends('shop.layouts.app')
@section('title', 'My Profile')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-3xl mx-auto px-4 reveal">
        <a href="{{ route('shop.account') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-4"><i class="fas fa-arrow-left"></i> Back to account</a>
        <h1 class="display text-3xl font-bold mb-6">My Profile</h1>

        <form method="POST" action="{{ route('shop.account.profile.update') }}" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Name</label>
                    <input type="text" name="name" required value="{{ old('name', $customer->name) }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Email</label>
                    <input type="email" name="email" required value="{{ old('email', $customer->email) }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Address</label>
                    <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <button class="btn btn-dark"><i class="fas fa-check"></i> Save changes</button>
        </form>
    </div>
</section>
@endsection

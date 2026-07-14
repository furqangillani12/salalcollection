@extends('shop.layouts.app')
@section('title', 'Change Password')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-md mx-auto px-4 reveal">
        <a href="{{ route('shop.account') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-4"><i class="fas fa-arrow-left"></i> Back to account</a>
        <h1 class="display text-3xl font-bold mb-6">Change Password</h1>

        <form method="POST" action="{{ route('shop.account.password.update') }}" x-data="{ s1:false, s2:false, s3:false }" class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Current password</label>
                <div style="position:relative;">
                    <input :type="s1 ? 'text' : 'password'" name="current_password" required
                           style="padding-right:42px;"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" @click="s1 = !s1" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;"><i class="fas" :class="s1 ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-700 mb-1.5 block">New password</label>
                <div style="position:relative;">
                    <input :type="s2 ? 'text' : 'password'" name="password" required minlength="8"
                           style="padding-right:42px;"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" @click="s2 = !s2" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;"><i class="fas" :class="s2 ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Confirm new password</label>
                <div style="position:relative;">
                    <input :type="s3 ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                           style="padding-right:42px;"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" @click="s3 = !s3" tabindex="-1" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;"><i class="fas" :class="s3 ? 'fa-eye-slash' : 'fa-eye'"></i></button>
                </div>
            </div>
            <button class="btn btn-dark btn-block"><i class="fas fa-shield-halved"></i> Update password</button>
        </form>
    </div>
</section>
@endsection

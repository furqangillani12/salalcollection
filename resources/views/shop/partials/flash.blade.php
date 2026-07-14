@php
    $success = session('shop_success') ?? session('success');
    $error   = session('shop_error') ?? session('error');
@endphp
@if ($success || $error || $errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-2">
        @if ($success)
            <div class="rounded-xl px-4 py-3 text-sm flex items-start gap-3 border bg-emerald-50 border-emerald-200 text-emerald-800">
                <i class="fas fa-circle-check mt-0.5"></i>
                <span class="flex-1">{{ $success }}</span>
            </div>
        @endif
        @if ($error)
            <div class="rounded-xl px-4 py-3 text-sm flex items-start gap-3 border bg-red-50 border-red-200 text-red-700">
                <i class="fas fa-circle-exclamation mt-0.5"></i>
                <span class="flex-1">{{ $error }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl px-4 py-3 text-sm border bg-amber-50 border-amber-200 text-amber-800">
                <div class="flex items-start gap-3">
                    <i class="fas fa-triangle-exclamation mt-0.5"></i>
                    <ul class="list-disc list-inside space-y-0.5 flex-1">
                        @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
@endif

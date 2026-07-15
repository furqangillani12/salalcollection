@extends('layouts.admin')

@section('content')
    <div class="p-3 sm:p-6">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-6">POS Settings</h1>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- ═══════════════════════════════════════
             WEBSITE / SOCIAL (storefront contact + social links)
        ═══════════════════════════════════════ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-globe text-rose-500"></i> Website &amp; Social Links
                </h2>
                <span class="text-xs text-gray-400">Shown on the storefront footer</span>
            </div>
            <form action="{{ route('admin.settings.site.update') }}" method="POST" enctype="multipart/form-data" class="p-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Contact phone</label>
                        <input type="text" name="site_phone" value="{{ old('site_phone', $site['site_phone'] ?? '') }}" placeholder="+92 300 7951919" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">WhatsApp number <span class="text-gray-400 font-normal">(digits, e.g. 923007951919)</span></label>
                        <input type="text" name="site_whatsapp" value="{{ old('site_whatsapp', $site['site_whatsapp'] ?? '') }}" placeholder="923007951919" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Contact email</label>
                        <input type="email" name="site_email" value="{{ old('site_email', $site['site_email'] ?? '') }}" placeholder="hello@salalcollection.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Address</label>
                        <input type="text" name="site_address" value="{{ old('site_address', $site['site_address'] ?? '') }}" placeholder="PanjGirain, Tehsil Darya Khan, Bhakkar" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Top bar message <span class="text-gray-400 font-normal">(the strip at the very top of the website)</span></label>
                        <input type="text" name="topbar_text" value="{{ old('topbar_text', $site['topbar_text'] ?? 'Free delivery across Pakistan on orders above Rs. 5,000') }}" placeholder="Free delivery across Pakistan on orders above Rs. 5,000" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Top bar location <span class="text-gray-400 font-normal">(shown after the message · leave empty to use the Address above)</span></label>
                        <input type="text" name="topbar_location" value="{{ old('topbar_location', $site['topbar_location'] ?? '') }}" placeholder="PanjGirain, Tehsil Darya Khan, District Bhakkar" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-[11px] text-gray-400 mt-1">Leave both fields empty to hide the top bar entirely.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fab fa-facebook-f text-blue-600 mr-1"></i> Facebook URL</label>
                        <input type="url" name="social_facebook" value="{{ old('social_facebook', $site['social_facebook'] ?? '') }}" placeholder="https://facebook.com/..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fab fa-instagram text-pink-600 mr-1"></i> Instagram URL</label>
                        <input type="url" name="social_instagram" value="{{ old('social_instagram', $site['social_instagram'] ?? '') }}" placeholder="https://instagram.com/..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fab fa-whatsapp text-green-600 mr-1"></i> WhatsApp link number <span class="text-gray-400 font-normal">(for chat button)</span></label>
                        <input type="text" name="social_whatsapp" value="{{ old('social_whatsapp', $site['social_whatsapp'] ?? '') }}" placeholder="923007951919" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fab fa-tiktok mr-1"></i> TikTok URL</label>
                        <input type="url" name="social_tiktok" value="{{ old('social_tiktok', $site['social_tiktok'] ?? '') }}" placeholder="https://tiktok.com/@..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fab fa-x-twitter mr-1"></i> X (Twitter) URL</label>
                        <input type="url" name="social_x" value="{{ old('social_x', $site['social_x'] ?? '') }}" placeholder="https://x.com/..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fab fa-youtube text-red-600 mr-1"></i> YouTube URL</label>
                        <input type="url" name="social_youtube" value="{{ old('social_youtube', $site['social_youtube'] ?? '') }}" placeholder="https://youtube.com/@..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>

                    {{-- ── Online store tax (applied on website orders, like the POS receipt) ── --}}
                    <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-1">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2"><i class="fas fa-percent text-emerald-500 mr-1"></i> Online store tax</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tax rate</label>
                        <input type="number" step="0.01" min="0" name="shop_tax_rate" value="{{ old('shop_tax_rate', $site['shop_tax_rate'] ?? '') }}" placeholder="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-[11px] text-gray-400 mt-1">Leave 0 for no tax. Applied on (subtotal − discount + delivery), same as POS.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tax type</label>
                        <select name="shop_tax_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="percent" {{ ($site['shop_tax_type'] ?? 'percent') === 'percent' ? 'selected' : '' }}>Percent (%)</option>
                            <option value="fixed" {{ ($site['shop_tax_type'] ?? '') === 'fixed' ? 'selected' : '' }}>Fixed (Rs.)</option>
                        </select>
                    </div>

                    {{-- ── Khushkhabri / good-news note shown on cart & checkout ── --}}
                    <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-1">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2"><i class="fas fa-gift text-amber-500 mr-1"></i> Cart / checkout note (Khushkhabri)</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Note title</label>
                        <input type="text" name="notice_title" value="{{ old('notice_title', $site['notice_title'] ?? '') }}" placeholder="خوشخبری / Good news" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Short line (teaser)</label>
                        <input type="text" name="notice_short" value="{{ old('notice_short', $site['notice_short'] ?? '') }}" placeholder="Easy returns & exchange…" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Full text (shown on “Read more”)</label>
                        <textarea name="notice_full" rows="3" placeholder="یہاں تجارت اسلامی اصولوں کے مطابق ہوتی ہے…" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('notice_full', $site['notice_full'] ?? '') }}</textarea>
                    </div>

                    {{-- ── Dispatch slip ── --}}
                    <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-1">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2"><i class="fas fa-print text-cyan-500 mr-1"></i> Dispatch slip</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Business name (on slip)</label>
                        <input type="text" name="site_name" value="{{ old('site_name', $site['site_name'] ?? '') }}" placeholder="SALAL COLLECTION" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Slip language</label>
                        @php $slipLang = old('dispatch_slip_lang', $site['dispatch_slip_lang'] ?? 'en'); @endphp
                        <select name="dispatch_slip_lang" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                            <option value="en" {{ $slipLang === 'en' ? 'selected' : '' }}>English</option>
                            <option value="ur" {{ $slipLang === 'ur' ? 'selected' : '' }}>اردو (Urdu)</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Default language the dispatch slip opens in.</p>
                    </div>

                    {{-- Language-specific slip logos: English slip → EN logo, Urdu slip → UR logo --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Slip logo — English</label>
                        @if (!empty($site['dispatch_logo_en']))
                            <div class="flex items-center gap-2 mb-1">
                                <img src="{{ asset('storage/'.$site['dispatch_logo_en']) }}" alt="" class="h-10 border border-gray-200 rounded bg-white p-1">
                                <label class="text-[11px] text-rose-600 inline-flex items-center gap-1"><input type="checkbox" name="remove_dispatch_logo_en" value="1"> Remove</label>
                            </div>
                        @endif
                        <input type="file" name="dispatch_logo_en" accept="image/*" class="w-full text-xs text-gray-600 file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700">
                        <p class="text-[11px] text-gray-400 mt-1">Shown on the English / Both slip. PNG/JPG/SVG, ≤1&nbsp;MB.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Slip logo — اردو</label>
                        @if (!empty($site['dispatch_logo_ur']))
                            <div class="flex items-center gap-2 mb-1">
                                <img src="{{ asset('storage/'.$site['dispatch_logo_ur']) }}" alt="" class="h-10 border border-gray-200 rounded bg-white p-1">
                                <label class="text-[11px] text-rose-600 inline-flex items-center gap-1"><input type="checkbox" name="remove_dispatch_logo_ur" value="1"> Remove</label>
                            </div>
                        @endif
                        <input type="file" name="dispatch_logo_ur" accept="image/*" class="w-full text-xs text-gray-600 file:mr-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-gray-100 file:text-gray-700">
                        <p class="text-[11px] text-gray-400 mt-1">Shown on the Urdu slip. Falls back to the English logo if empty.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Postman note — English</label>
                        <textarea name="dispatch_postman_note" rows="2" placeholder="Dear postman: if you face any difficulty…" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('dispatch_postman_note', $site['dispatch_postman_note'] ?? '') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Postman note — اردو</label>
                        <textarea name="dispatch_postman_note_ur" rows="2" dir="rtl" placeholder="معزز پوسٹ مین…" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('dispatch_postman_note_ur', $site['dispatch_postman_note_ur'] ?? '') }}</textarea>
                    </div>

                    {{-- ── Reward points ── --}}
                    <div class="sm:col-span-2 border-t border-gray-100 pt-4 mt-1">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2"><i class="fas fa-star text-amber-500 mr-1"></i> Reward points</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Rupees per 1 point earned (on delivered orders)</label>
                        <input type="number" step="any" min="0" name="points_rupees_per_point" value="{{ old('points_rupees_per_point', $site['points_rupees_per_point'] ?? '') }}" placeholder="0 = off, e.g. 100" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-[11px] text-gray-400 mt-1">e.g. 100 → customer earns 1 point per Rs.100 when the order is delivered. 0 disables.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Points per approved review</label>
                        <input type="number" step="1" min="0" name="points_per_review" value="{{ old('points_per_review', $site['points_per_review'] ?? '') }}" placeholder="0 = off, e.g. 20" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-[11px] text-gray-400 mt-1">Photo/video/social-media bonuses can be awarded manually from the customer page.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Rupee value of 1 point (redemption)</label>
                        <input type="number" step="any" min="0" name="points_value_rupees" value="{{ old('points_value_rupees', $site['points_value_rupees'] ?? '') }}" placeholder="0 = off, e.g. 0.20" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <p class="text-[11px] text-gray-400 mt-1">Decimals allowed. e.g. 0.20 → 5 points = Rs.1 discount at checkout. 0 disables redemption.</p>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold"><i class="fas fa-check mr-1"></i> Save website settings</button>
                </div>
            </form>
        </div>

        {{-- ═══════════════════════════════════════
             ORDER STATUS MESSAGES (sent to customer on each status)
        ═══════════════════════════════════════ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="px-5 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-comment-dots text-cyan-500"></i> Order status messages
                </h2>
                <p class="text-xs text-gray-500 mt-1">Sent with each status (email + WhatsApp). Placeholders: <code class="bg-gray-100 px-1 rounded">{name}</code> <code class="bg-gray-100 px-1 rounded">{order}</code> <code class="bg-gray-100 px-1 rounded">{courier}</code> <code class="bg-gray-100 px-1 rounded">{tracking}</code> <code class="bg-gray-100 px-1 rounded">{track_link}</code> <code class="bg-gray-100 px-1 rounded">{total}</code>. Add review-points text here, e.g. for “Confirmed”.</p>
            </div>
            <form action="{{ route('admin.settings.status-templates.update') }}" method="POST" class="p-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach (config('order_flow.statuses') as $key => $meta)
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1"><i class="fas {{ $meta['icon'] }} mr-1" style="color:{{ $meta['text'] }};"></i> {{ $meta['label'] }}</label>
                            <textarea name="status_msg_{{ $key }}" rows="2" placeholder="Leave blank for the default message"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-cyan-500 focus:border-cyan-500">{{ $site['status_msg_' . $key] ?? '' }}</textarea>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end">
                    <button class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg text-sm font-semibold"><i class="fas fa-check mr-1"></i> Save status messages</button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ═══════════════════════════════════════
                 PAYMENT METHODS
            ═══════════════════════════════════════ --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                        Payment Methods
                    </h2>
                    <span class="text-xs text-gray-400">{{ $paymentMethods->where('is_active', true)->count() }} active</span>
                </div>

                <div class="p-5">
                    {{-- Add New --}}
                    <form action="{{ route('admin.settings.payment-methods.store') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input type="text" name="name" placeholder="Value (e.g. jazzcash)" required
                                class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <input type="text" name="label" placeholder="Label (e.g. Jazz)" required
                                class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700 whitespace-nowrap">
                                + Add
                            </button>
                        </div>
                    </form>

                    {{-- List --}}
                    <div class="space-y-2">
                        @forelse($paymentMethods as $pm)
                            <div x-data="{ editing: false }" class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-lg border {{ $pm->is_active ? 'border-gray-200 bg-white' : 'border-gray-100 bg-gray-50 opacity-60' }}">
                                <div class="flex-1 min-w-0">
                                    {{-- Display mode --}}
                                    <div x-show="!editing" class="flex items-center gap-2 flex-wrap">
                                        <span class="font-medium text-gray-800 text-sm">{{ $pm->label }}</span>
                                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $pm->name }}</span>
                                        @if($pm->show_on_website)<span class="text-[10px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded"><i class="fas fa-globe"></i> Web</span>@endif
                                        @if($pm->is_cod)<span class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded">COD</span>@endif
                                        @if($pm->account_number)<span class="text-[10px] text-gray-500">· {{ $pm->bank_name }} {{ $pm->account_number }}</span>@endif
                                    </div>

                                    {{-- Edit mode --}}
                                    <form x-show="editing" x-cloak
                                        action="{{ route('admin.settings.payment-methods.update', $pm) }}" method="POST"
                                        class="space-y-2">
                                        @csrf @method('PUT')
                                        <div class="flex flex-wrap gap-2">
                                            <input type="text" name="name" value="{{ $pm->name }}" placeholder="value"
                                                class="w-full sm:w-28 border border-gray-300 rounded px-2 py-1 text-xs">
                                            <input type="text" name="label" value="{{ $pm->label }}" placeholder="label"
                                                class="w-full sm:w-28 border border-gray-300 rounded px-2 py-1 text-xs">
                                        </div>
                                        <div class="flex flex-wrap gap-3">
                                            <label class="flex items-center gap-1 text-xs text-gray-600"><input type="hidden" name="show_on_website" value="0"><input type="checkbox" name="show_on_website" value="1" {{ $pm->show_on_website ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600"> Show on website</label>
                                            <label class="flex items-center gap-1 text-xs text-gray-600"><input type="hidden" name="is_cod" value="0"><input type="checkbox" name="is_cod" value="1" {{ $pm->is_cod ? 'checked' : '' }} class="rounded border-gray-300 text-amber-600"> Cash on delivery</label>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="account_title" value="{{ $pm->account_title }}" placeholder="Account title" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                            <input type="text" name="account_number" value="{{ $pm->account_number }}" placeholder="Account / IBAN" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                            <input type="text" name="bank_name" value="{{ $pm->bank_name }}" placeholder="Bank / wallet name" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                            <input type="text" name="instructions" value="{{ $pm->instructions }}" placeholder="Instructions (optional)" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Save</button>
                                            <button type="button" @click="editing = false" class="text-gray-400 hover:text-gray-600 text-xs">Cancel</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    {{-- Edit --}}
                                    <button @click="editing = !editing" class="text-blue-500 hover:text-blue-700" title="Edit">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>

                                    {{-- Toggle Active --}}
                                    <form action="{{ route('admin.settings.payment-methods.toggle', $pm) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="{{ $pm->is_active ? 'Disable' : 'Enable' }}"
                                            class="{{ $pm->is_active ? 'text-green-500 hover:text-green-700' : 'text-gray-400 hover:text-gray-600' }}">
                                            @if($pm->is_active)
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.settings.payment-methods.destroy', $pm) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Delete this payment method?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">No payment methods yet. Add one above.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════
                 DISPATCH METHODS
            ═══════════════════════════════════════ --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-orange-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z"/>
                        </svg>
                        Dispatch Methods
                    </h2>
                    <span class="text-xs text-gray-400">{{ $dispatchMethods->where('is_active', true)->count() }} active</span>
                </div>

                <div class="p-5">
                    {{-- Add New --}}
                    <form action="{{ route('admin.settings.dispatch-methods.store') }}" method="POST" class="mb-4 space-y-2">
                        @csrf
                        <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                            <input type="text" name="name" placeholder="Name (e.g. TCS)" required
                                class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500">
                            <div class="flex items-center gap-2">
                                <label class="flex items-center gap-1.5 text-sm text-gray-600 whitespace-nowrap">
                                    <input type="checkbox" name="has_tracking" value="1" class="rounded border-gray-300 text-orange-600">
                                    Tracking
                                </label>
                                <button type="submit"
                                    class="px-4 py-2 bg-orange-600 text-white rounded text-sm hover:bg-orange-700 whitespace-nowrap">
                                    + Add
                                </button>
                            </div>
                        </div>
                        <input type="text" name="note" placeholder="Customer note (optional) — e.g. Estimated delivery 5–7 working days"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500">
                    </form>

                    {{-- List --}}
                    <div class="space-y-2">
                        @forelse($dispatchMethods as $dm)
                            <div x-data="{ editing: false }" class="flex flex-col sm:flex-row sm:items-center gap-2 p-3 rounded-lg border {{ $dm->is_active ? 'border-gray-200 bg-white' : 'border-gray-100 bg-gray-50 opacity-60' }}">
                                <div class="flex-1 min-w-0">
                                    {{-- Display mode --}}
                                    <div x-show="!editing">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($dm->logo)<img src="{{ asset('storage/'.$dm->logo) }}" class="w-6 h-6 object-contain rounded">@endif
                                            <span class="font-medium text-gray-800 text-sm">{{ $dm->name }}</span>
                                            @if($dm->has_tracking)
                                                <span class="text-xs text-orange-600 bg-orange-50 px-2 py-0.5 rounded">Tracking</span>
                                            @endif
                                            @if($dm->show_on_website)<span class="text-[10px] text-blue-600 bg-blue-50 px-2 py-0.5 rounded"><i class="fas fa-globe"></i> Web</span>@endif
                                        </div>
                                        @if($dm->note)
                                            <div class="text-xs text-gray-500 mt-0.5"><i class="fas fa-circle-info mr-1 text-gray-300"></i>{{ $dm->note }}</div>
                                        @endif
                                    </div>

                                    {{-- Edit mode --}}
                                    <form x-show="editing" x-cloak
                                        action="{{ route('admin.settings.dispatch-methods.update', $dm) }}" method="POST" enctype="multipart/form-data"
                                        class="space-y-2">
                                        @csrf @method('PUT')
                                        <div class="flex flex-wrap gap-2 items-center">
                                            <input type="text" name="name" value="{{ $dm->name }}"
                                                class="w-full sm:w-32 border border-gray-300 rounded px-2 py-1 text-xs">
                                            <label class="flex items-center gap-1 text-xs text-gray-600">
                                                <input type="checkbox" name="has_tracking" value="1" {{ $dm->has_tracking ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-orange-600">
                                                Track
                                            </label>
                                            <label class="flex items-center gap-1 text-xs text-gray-600">
                                                <input type="hidden" name="show_on_website" value="0">
                                                <input type="checkbox" name="show_on_website" value="1" {{ $dm->show_on_website ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-blue-600">
                                                Show on website
                                            </label>
                                        </div>
                                        <input type="text" name="note" value="{{ $dm->note }}" placeholder="Customer note (optional)"
                                            class="w-full border border-gray-300 rounded px-2 py-1 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500">Courier logo:</span>
                                            <input type="file" name="logo" accept="image/*" class="text-xs">
                                        </div>
                                        <div class="flex gap-3">
                                            <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Save</button>
                                            <button type="button" @click="editing = false" class="text-gray-400 hover:text-gray-600 text-xs">Cancel</button>
                                        </div>
                                    </form>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    {{-- Edit --}}
                                    <button @click="editing = !editing" class="text-blue-500 hover:text-blue-700" title="Edit">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>

                                    {{-- Toggle Active --}}
                                    <form action="{{ route('admin.settings.dispatch-methods.toggle', $dm) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="{{ $dm->is_active ? 'Disable' : 'Enable' }}"
                                            class="{{ $dm->is_active ? 'text-green-500 hover:text-green-700' : 'text-gray-400 hover:text-gray-600' }}">
                                            @if($dm->is_active)
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form action="{{ route('admin.settings.dispatch-methods.destroy', $dm) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Delete this dispatch method?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">No dispatch methods yet. Add one above.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════════════════
             DELIVERY CHARGES PER DISPATCH METHOD
        ═══════════════════════════════════════ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-weight-hanging text-green-500"></i>
                    Delivery Charges (Per Dispatch Method)
                </h2>
                <span class="text-xs text-gray-400">{{ $deliverySlabs->where('is_active', true)->count() }} active slabs</span>
            </div>

            <div class="p-5">
                <p class="text-xs text-gray-500 mb-4">Set weight-based delivery charges for each dispatch method. In POS, when a dispatch method is selected, delivery charges auto-calculate based on order weight.</p>

                {{-- Add New Slab --}}
                <form action="{{ route('admin.settings.delivery-slabs.store') }}" method="POST" class="mb-5 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    @csrf
                    <div class="text-xs font-semibold text-gray-600 uppercase mb-2">Add New Rate</div>
                    <div class="flex flex-col sm:flex-row gap-2 sm:items-end">
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Dispatch Method</label>
                            <select name="dispatch_method_id" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                <option value="">Select Method</option>
                                @foreach($dispatchMethods->where('has_tracking', true) as $dm)
                                    <option value="{{ $dm->id }}">{{ $dm->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Min Weight (kg)</label>
                            <input type="number" name="min_weight" step="0.001" min="0" placeholder="e.g. 0.5" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Max Weight (kg)</label>
                            <input type="number" name="max_weight" step="0.001" min="0" placeholder="e.g. 1.0" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs text-gray-500 mb-1">Charge (Rs.)</label>
                            <input type="number" name="charge" step="0.01" min="0" placeholder="e.g. 250" required
                                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded text-sm hover:bg-green-700 whitespace-nowrap">
                            + Add
                        </button>
                    </div>
                </form>

                {{-- List Slabs Grouped by Dispatch Method --}}
                @php $trackingMethods = $dispatchMethods->where('has_tracking', true); @endphp
                @foreach($trackingMethods as $dm)
                    @php $methodSlabs = $deliverySlabs->where('dispatch_method_id', $dm->id); @endphp
                    <div class="mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-bold text-gray-800">{{ $dm->name }}</span>
                            <span class="text-xs text-orange-600 bg-orange-50 px-2 py-0.5 rounded">{{ $methodSlabs->count() }} rates</span>
                        </div>

                        @if($methodSlabs->count())
                            <div class="space-y-1.5 pl-3 border-l-2 border-green-200">
                                @foreach($methodSlabs as $slab)
                                    <div x-data="{ editing: false }" class="flex flex-col sm:flex-row sm:items-center gap-2 p-2.5 rounded-lg border {{ $slab->is_active ? 'border-gray-200 bg-white' : 'border-gray-100 bg-gray-50 opacity-60' }}">
                                        <div class="flex-1 min-w-0">
                                            <div x-show="!editing" class="flex items-center gap-3 flex-wrap">
                                                <span class="text-sm text-gray-700">
                                                    {{ rtrim(rtrim(number_format($slab->min_weight, 3), '0'), '.') }} kg
                                                    —
                                                    {{ rtrim(rtrim(number_format($slab->max_weight, 3), '0'), '.') }} kg
                                                </span>
                                                <span class="text-sm font-bold text-green-700 bg-green-50 px-2 py-0.5 rounded">
                                                    Rs. {{ number_format($slab->charge, 0) }}
                                                </span>
                                            </div>
                                            <form x-show="editing" x-cloak
                                                action="{{ route('admin.settings.delivery-slabs.update', $slab) }}" method="POST"
                                                class="flex flex-wrap gap-2 items-center">
                                                @csrf @method('PUT')
                                                <input type="number" name="min_weight" value="{{ $slab->min_weight }}" step="0.001" min="0"
                                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-xs">
                                                <span class="text-xs text-gray-400">to</span>
                                                <input type="number" name="max_weight" value="{{ $slab->max_weight }}" step="0.001" min="0"
                                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-xs">
                                                <span class="text-xs text-gray-400">kg =</span>
                                                <input type="number" name="charge" value="{{ $slab->charge }}" step="0.01" min="0"
                                                    class="w-20 border border-gray-300 rounded px-2 py-1 text-xs" placeholder="Rs.">
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium">Save</button>
                                                <button type="button" @click="editing = false" class="text-gray-400 text-xs">Cancel</button>
                                            </form>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0">
                                            <button @click="editing = !editing" class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit text-xs"></i></button>
                                            <form action="{{ route('admin.settings.delivery-slabs.toggle', $slab) }}" method="POST" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="{{ $slab->is_active ? 'text-green-500' : 'text-gray-400' }}">
                                                    <i class="fas fa-{{ $slab->is_active ? 'check-circle' : 'times-circle' }} text-xs"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.settings.delivery-slabs.destroy', $slab) }}" method="POST" class="inline" onsubmit="return confirm('Delete?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-400 pl-3 py-2">No rates added for {{ $dm->name }} yet.</p>
                        @endif
                    </div>
                @endforeach

                @if($trackingMethods->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-4">No dispatch methods with tracking found. Add one above first.</p>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             CHANGE PASSWORD
        ═══════════════════════════════════════ --}}
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 max-w-2xl">
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-key text-amber-500"></i>
                    Change Password
                </h2>
                <span class="text-xs text-gray-400">{{ auth()->user()->email }}</span>
            </div>

            <div class="p-5">
                @if (session('status') === 'password-updated')
                    <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded border border-emerald-200 text-sm"
                         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-cloak>
                        <i class="fas fa-check-circle mr-1"></i> Password updated successfully.
                    </div>
                @endif

                @if ($errors->updatePassword->any())
                    <div class="mb-4 p-3 bg-red-50 text-red-800 rounded border border-red-200 text-sm">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->updatePassword->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}"
                      x-data="{ show1:false, show2:false, show3:false }"
                      class="space-y-4">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="block text-xs font-medium text-gray-600 mb-1.5">
                            Current Password <span class="text-rose-500">*</span>
                        </label>
                        <div style="position:relative;">
                            <input id="update_password_current_password" name="current_password" required
                                   :type="show1 ? 'text' : 'password'" autocomplete="current-password"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" @click="show1=!show1" tabindex="-1"
                                    class="text-gray-400 hover:text-gray-700"
                                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
                                <i class="fas" :class="show1 ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="update_password_password" class="block text-xs font-medium text-gray-600 mb-1.5">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <div style="position:relative;">
                            <input id="update_password_password" name="password" required minlength="8"
                                   :type="show2 ? 'text' : 'password'" autocomplete="new-password"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" @click="show2=!show2" tabindex="-1"
                                    class="text-gray-400 hover:text-gray-700"
                                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
                                <i class="fas" :class="show2 ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500">Use at least 8 characters. Mixing letters, numbers and symbols is best.</p>
                    </div>

                    <div>
                        <label for="update_password_password_confirmation" class="block text-xs font-medium text-gray-600 mb-1.5">
                            Confirm New Password <span class="text-rose-500">*</span>
                        </label>
                        <div style="position:relative;">
                            <input id="update_password_password_confirmation" name="password_confirmation" required minlength="8"
                                   :type="show3 ? 'text' : 'password'" autocomplete="new-password"
                                   class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" @click="show3=!show3" tabindex="-1"
                                    class="text-gray-400 hover:text-gray-700"
                                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
                                <i class="fas" :class="show3 ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2 text-white rounded-lg text-sm font-semibold shadow-sm"
                                style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                            <i class="fas fa-shield-alt"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection

@extends('shop.layouts.app')
@section('title', 'Checkout')

@section('content')
@php
    $countries = ['Pakistan','Saudi Arabia','United Arab Emirates','United Kingdom','United States','Canada','Australia','Qatar','Oman','Bahrain','Kuwait','Other'];
@endphp
<section class="py-10 sm:py-14"
    x-data="checkoutForm({
        provinces: @js($provinces),
        countries: @js($countries),
        charges: @js($deliveryCharges),
        payments: @js($paymentMethods->map(fn($p)=>['name'=>$p->name,'label'=>$p->label,'is_cod'=>(bool)$p->is_cod,'account_title'=>$p->account_title,'account_number'=>$p->account_number,'bank_name'=>$p->bank_name,'instructions'=>$p->instructions])->values()),
        initDispatch: @js($dispatchMethods->first()?->name),
        initPayment: @js($paymentMethods->first()?->name),
        sub: {{ $totals['subtotal'] }}, disc: {{ $totals['discount'] }},
        taxRate: {{ $totals['tax_rate'] }}, taxType: @js($totals['tax_type']),
        pointsBalance: {{ $pointsBalance ?? 0 }}, pointValue: {{ $pointValue ?? 0 }}, maxRedeemable: {{ $maxRedeemable ?? 0 }},
        old: {
            first: @js(old('shipping_first_name', $customer ? (explode(' ', $customer->name)[0] ?? '') : '')),
            last:  @js(old('shipping_last_name', $customer ? \Str::after($customer->name, ' ') : '')),
            phone: @js(old('shipping_phone', $customer?->phone)),
            address1: @js(old('shipping_address1', $customer?->address)),
            address2: @js(old('shipping_address2')),
            city: @js(old('shipping_city')),
            tehsil: @js(old('shipping_tehsil')),
            district: @js(old('shipping_district')),
            province: @js(old('shipping_province')),
            country: @js(old('shipping_country', 'Pakistan')),
            postcode: @js(old('shipping_post_code')),
        }
    })">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 reveal">
            <h1 class="display text-3xl sm:text-4xl font-bold">Checkout</h1>
            <p class="text-gray-500 text-sm mt-2">A few details and your order is on the way.</p>
        </div>

        @include('shop.partials.notice', ['class' => 'mb-6 reveal'])

        @if ($isGuest)
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 mb-6 flex items-center gap-3 reveal">
                <i class="fas fa-circle-info text-lg" style="color:var(--brand-cyan);"></i>
                <div class="text-xs text-gray-600">Checking out as a guest. Track your order any time at <strong>/track-order</strong>, or <a href="{{ route('shop.login') }}" class="font-semibold underline" style="color:var(--brand-cyan);">sign in</a> for faster checkout.</div>
            </div>
        @endif

        <form method="POST" action="{{ route('shop.checkout.place') }}" enctype="multipart/form-data" class="grid lg:grid-cols-[1fr_360px] gap-8">
            @csrf

            <div class="space-y-6 reveal">
                {{-- Contact + phone lookup --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-address-card" style="color:var(--brand-cyan);"></i> Contact</h2>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 mb-1 block">Email *</label>
                        <input type="email" name="email" required value="{{ old('email', $customer?->email) }}" placeholder="you@example.com"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-[11px] text-gray-500 mt-1">Order confirmation & updates are sent here.</p>
                    </div>
                </div>

                {{-- Shipping address --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-location-dot" style="color:var(--brand-cyan);"></i> Delivery address</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">First name *</label>
                            <input type="text" name="shipping_first_name" required x-model="f.first" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Last name</label>
                            <input type="text" name="shipping_last_name" x-model="f.last" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Cell number *</label>
                            <div class="relative">
                                <input type="text" name="shipping_phone" required x-model="f.phone" placeholder="03xx-xxxxxxx"
                                       class="w-full px-3 py-2.5 pr-10 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" @click="lookupPhone()" title="Find my saved address"
                                        class="absolute right-1.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-md text-gray-500 hover:bg-gray-100 flex items-center justify-center">
                                    <i class="fas" :class="looking ? 'fa-circle-notch fa-spin' : 'fa-magnifying-glass'"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1">Tap the search icon to auto-fill from a previous order (district, tehsil, everything). More than one number? Add it in the address.</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Country *</label>
                            <div class="relative" @click.away="openDd.country=false">
                                <input type="text" name="shipping_country" x-model="f.country" autocomplete="off" placeholder="Select or search country"
                                       @focus="openDd.country=true" @click="openDd.country=true" @input="openDd.country=true"
                                       class="w-full px-3 py-2.5 pr-9 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                <ul x-show="openDd.country" x-cloak x-transition.opacity.duration.100ms
                                    class="absolute z-40 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg text-sm">
                                    <template x-for="opt in filterList(countries, f.country)" :key="opt">
                                        <li @click="f.country=opt; openDd.country=false" class="px-3 py-2.5 hover:bg-cyan-50 cursor-pointer" :class="{'bg-cyan-50 font-semibold text-cyan-800': f.country===opt}" x-text="opt"></li>
                                    </template>
                                    <template x-if="filterList(countries, f.country).length===0"><li class="px-3 py-2.5 text-gray-400">No match</li></template>
                                </ul>
                            </div>
                        </div>

                        {{-- Pakistan cascade --}}
                        <template x-if="isPk">
                            <div class="sm:col-span-2 grid sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Province</label>
                                    <div class="relative" @click.away="openDd.province=false">
                                        <input type="text" name="shipping_province" x-model="f.province" autocomplete="off" placeholder="Select province"
                                               @focus="openDd.province=true" @click="openDd.province=true" @input="openDd.province=true"
                                               class="w-full px-3 py-2.5 pr-9 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                        <ul x-show="openDd.province" x-cloak x-transition.opacity.duration.100ms
                                            class="absolute z-40 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg text-sm">
                                            <template x-for="opt in filterList(provinceNames, f.province)" :key="opt">
                                                <li @click="f.province=opt; f.district=''; openDd.province=false" class="px-3 py-2.5 hover:bg-cyan-50 cursor-pointer" :class="{'bg-cyan-50 font-semibold text-cyan-800': f.province===opt}" x-text="opt"></li>
                                            </template>
                                            <template x-if="filterList(provinceNames, f.province).length===0"><li class="px-3 py-2.5 text-gray-400">No match</li></template>
                                        </ul>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">District</label>
                                    <div class="relative" @click.away="openDd.district=false">
                                        <input type="text" name="shipping_district" x-model="f.district" autocomplete="off" :disabled="!f.province"
                                               :placeholder="f.province ? 'Select district' : 'Pick a province first'"
                                               @focus="f.province && (openDd.district=true)" @click="f.province && (openDd.district=true)" @input="openDd.district=true"
                                               class="w-full px-3 py-2.5 pr-9 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100">
                                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                        <ul x-show="openDd.district" x-cloak x-transition.opacity.duration.100ms
                                            class="absolute z-40 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-lg shadow-lg text-sm">
                                            <template x-for="opt in filterList(districts, f.district)" :key="opt">
                                                <li @click="f.district=opt; openDd.district=false" class="px-3 py-2.5 hover:bg-cyan-50 cursor-pointer" :class="{'bg-cyan-50 font-semibold text-cyan-800': f.district===opt}" x-text="opt"></li>
                                            </template>
                                            <template x-if="districts.length===0"><li class="px-3 py-2.5 text-gray-400">Pick a province first</li></template>
                                        </ul>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Tehsil</label>
                                    <input type="text" name="shipping_tehsil" x-model="f.tehsil" placeholder="Tehsil" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </template>

                        {{-- International: free-text state + city --}}
                        <template x-if="!isPk">
                            <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">State / Province</label>
                                    <input type="text" name="shipping_province" x-model="f.province" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-600 mb-1 block">City</label>
                                    <input type="text" name="shipping_city" x-model="f.city" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </template>

                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Address line 1 *</label>
                            <input type="text" name="shipping_address1" required x-model="f.address1" placeholder="House / street / area" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Address line 2</label>
                            <input type="text" name="shipping_address2" x-model="f.address2" placeholder="Landmark, extra phone number, etc." class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        {{-- City (Pakistan, optional) + post code --}}
                        <template x-if="isPk">
                            <div>
                                <label class="text-xs font-semibold text-gray-600 mb-1 block">City / Town</label>
                                <input type="text" name="shipping_city" x-model="f.city" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </template>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">Post code</label>
                            <input type="text" name="shipping_post_code" x-model="f.postcode" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                {{-- Dispatch method --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-box" style="color:var(--brand-cyan);"></i> Delivery method</h2>
                    @if ($dispatchMethods->isEmpty())
                        <p class="text-sm text-gray-500">No delivery methods available right now. Please contact us.</p>
                    @else
                        <div class="space-y-2">
                            @foreach ($dispatchMethods as $dm)
                                <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
                                       :class="dispatch === @js($dm->name) ? 'border-blue-500 bg-blue-50/40' : 'border-gray-100 hover:border-gray-200'">
                                    <input type="radio" name="dispatch_method" value="{{ $dm->name }}" x-model="dispatch" class="text-blue-600 mt-0.5">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-semibold text-gray-800 text-sm">{{ $dm->name }}</span>
                                            <span class="text-sm font-bold whitespace-nowrap" style="color:var(--brand-navy);">{{ ($deliveryCharges[$dm->name] ?? 0) > 0 ? shop_price($deliveryCharges[$dm->name]) : 'Free' }}</span>
                                        </div>
                                        @if ($dm->note)
                                            <div class="text-xs text-gray-500 mt-0.5 flex items-start gap-1.5"><i class="fas fa-circle-info text-gray-300 mt-0.5"></i><span>{{ $dm->note }}</span></div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if (shop_is_reseller())
                {{-- Reseller "From" address (printed as sender on the dispatch slip) --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6" x-data="{ addFrom: {{ old('from_name') ? 'true' : 'false' }} }">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="addFrom" class="mt-1 text-blue-600 rounded">
                        <span>
                            <span class="font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-user-tag" style="color:var(--brand-cyan);"></i> Add my own "From" address</span>
                            <span class="block text-xs text-gray-500 mt-0.5">For resellers — the parcel's sender will show your name & address instead of ours.</span>
                        </span>
                    </label>
                    <div x-show="addFrom" x-cloak class="grid sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">From name</label>
                            <input type="text" name="from_name" value="{{ old('from_name') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">From phone</label>
                            <input type="text" name="from_phone" value="{{ old('from_phone') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">From address</label>
                            <input type="text" name="from_address" value="{{ old('from_address') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                @endif

                {{-- Payment method --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-credit-card" style="color:var(--brand-cyan);"></i> Payment method</h2>
                    @if ($paymentMethods->isEmpty())
                        <p class="text-sm text-gray-500">No payment methods configured. Please contact us.</p>
                    @else
                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach ($paymentMethods as $pm)
                                <label class="flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition"
                                       :class="payment === @js($pm->name) ? 'border-blue-500 bg-blue-50/40' : 'border-gray-100 hover:border-gray-200'">
                                    <input type="radio" name="payment_method" value="{{ $pm->name }}" x-model="payment" class="mt-1 text-blue-600">
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $pm->label ?: $pm->name }}</div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $pm->is_cod ? 'Pay when you receive your order' : 'Bank / wallet transfer' }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- Bank account details + proof for the selected non-COD method --}}
                        <div x-show="selectedPayment && !selectedPayment.is_cod" x-cloak class="mt-4 rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                            <div class="text-sm font-semibold text-gray-800 mb-2"><i class="fas fa-building-columns text-blue-500 mr-1"></i> Send payment to:</div>
                            <div class="grid sm:grid-cols-2 gap-x-6 gap-y-1 text-sm">
                                <template x-if="selectedPayment.account_title"><div><span class="text-gray-500">Title:</span> <span class="font-semibold" x-text="selectedPayment.account_title"></span></div></template>
                                <template x-if="selectedPayment.account_number"><div><span class="text-gray-500">Account:</span> <span class="font-semibold font-mono" x-text="selectedPayment.account_number"></span> <button type="button" @click="copyText(selectedPayment.account_number,'Account number copied')" class="text-blue-500 ml-1"><i class="far fa-copy"></i></button></div></template>
                                <template x-if="selectedPayment.bank_name"><div><span class="text-gray-500">Bank:</span> <span class="font-semibold" x-text="selectedPayment.bank_name"></span></div></template>
                            </div>
                            <template x-if="selectedPayment.instructions"><p class="text-xs text-gray-600 mt-2 whitespace-pre-line" x-text="selectedPayment.instructions"></p></template>

                            <div class="mt-4 pt-3 border-t border-blue-100">
                                <div class="text-sm font-semibold text-gray-800 mb-2">Already paid? Attach your proof (optional)</div>
                                <div class="grid sm:grid-cols-2 gap-3">
                                    <input type="text" name="payment_sender_name" placeholder="Account title you sent from" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                    <input type="text" name="payment_sender_bank" placeholder="Bank you sent from" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                    <input type="number" step="0.01" name="payment_sender_amount" placeholder="Amount sent (Rs.)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                    <input type="file" name="payment_proof" accept="image/*" class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200">
                                </div>
                                <p class="text-[11px] text-gray-500 mt-1">Screenshot of the transfer. We'll verify & mark your order paid. You can also send it later.</p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <label class="text-xs font-semibold text-gray-600 mb-1 block">Order notes (optional)</label>
                    <textarea name="order_notes_customer" rows="2" placeholder="Anything you'd like us to know..." class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('order_notes_customer') }}</textarea>
                </div>
            </div>

            {{-- Summary --}}
            <aside class="lg:sticky lg:top-24 lg:self-start reveal">
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <h2 class="font-bold text-gray-900 mb-4">Order summary</h2>
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-2 mb-4">
                        @foreach ($items as $i)
                            <div class="flex gap-3">
                                <img src="{{ shop_image($i->product?->image) }}" class="w-14 h-16 object-cover rounded-lg" style="background:#f5f1e8;">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ $i->product?->name }}</div>
                                    <div class="text-[11px] text-gray-500">Qty {{ (int) $i->qty }}</div>
                                </div>
                                <div class="text-sm font-bold whitespace-nowrap" style="color:var(--brand-navy);">{{ shop_price($i->qty * $i->unit_price) }}</div>
                            </div>
                        @endforeach
                    </div>
                    {{-- Reward points redemption (#B) — only when the customer has usable points --}}
                    @if (($maxRedeemable ?? 0) > 0)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 mb-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="usePoints" class="rounded text-amber-600">
                                <span class="text-sm font-semibold text-amber-800"><i class="fas fa-star"></i> Use my reward points</span>
                            </label>
                            <p class="text-[11px] text-amber-700 mt-1">Balance: {{ number_format($pointsBalance) }} points · 1 point = {{ shop_price($pointValue) }}. You can redeem up to <strong>{{ number_format($maxRedeemable) }}</strong> on this order.</p>
                            <div x-show="usePoints" x-cloak class="mt-2 flex items-center gap-2">
                                <input type="number" min="0" max="{{ $maxRedeemable }}" step="1" x-model.number="redeemPoints"
                                       class="w-28 px-3 py-2 border border-amber-300 rounded-lg text-sm" placeholder="0">
                                <button type="button" @click="redeemPoints = maxRedeemable" class="text-xs font-semibold text-amber-700 underline">Use max</button>
                                <span class="text-xs text-amber-800 ml-auto" x-text="'-' + money(pointsDiscount)"></span>
                            </div>
                        </div>
                    @endif

                    <hr class="my-4 border-gray-100">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">{{ shop_price($totals['subtotal']) }}</span></div>
                        @if ($totals['discount'] > 0)
                            <div class="flex justify-between text-emerald-600"><span>Coupon ({{ $coupon->code }})</span><span>-{{ shop_price($totals['discount']) }}</span></div>
                        @endif
                        <div class="flex justify-between text-amber-600" x-show="pointsDiscount > 0" x-cloak><span><i class="fas fa-star text-[11px]"></i> Points (<span x-text="appliedPoints"></span>)</span><span x-text="'-' + money(pointsDiscount)"></span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Delivery</span><span class="font-semibold" x-text="charge > 0 ? money(charge) : 'Free'"></span></div>
                        <div class="flex justify-between" x-show="taxAmt > 0"><span class="text-gray-500">Tax<template x-if="taxType==='percent'"><span> (<span x-text="taxRate"></span>%)</span></template></span><span class="font-semibold" x-text="money(taxAmt)"></span></div>
                    </div>
                    <input type="hidden" name="redeem_points" :value="appliedPoints">
                    <hr class="my-4 border-gray-100">
                    <div class="flex items-baseline justify-between">
                        <span class="font-bold">Total</span>
                        <span class="text-2xl font-extrabold" style="color:var(--brand-navy);" x-text="money(grand)"></span>
                    </div>

                    {{-- Returning customer's previous khata, so they see the full amount due --}}
                    @if ($customer && (float) ($customer->current_balance ?? 0) > 0)
                        <div class="mt-4 rounded-lg bg-amber-50 border border-amber-100 p-3 text-sm">
                            <div class="flex justify-between"><span class="text-amber-800">Previous balance (khata)</span><span class="font-semibold">{{ shop_price($customer->current_balance) }}</span></div>
                            <div class="flex justify-between mt-1 pt-1 border-t border-amber-200 font-bold text-amber-900"><span>Total to send (incl. previous)</span><span x-text="money(grand + {{ (float) $customer->current_balance }})"></span></div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary btn-block mt-5" {{ $dispatchMethods->isEmpty() || $paymentMethods->isEmpty() ? 'disabled' : '' }}><i class="fas fa-lock"></i> Place order</button>
                    <p class="text-[11px] text-gray-400 text-center mt-3">By placing your order you agree to our <a href="{{ route('shop.terms') }}" class="underline">terms</a>.</p>
                </div>
            </aside>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    window.checkoutForm = function (cfg) {
        return {
            provinces: cfg.provinces || {},
            countries: cfg.countries || [],
            openDd: { country: false, province: false, district: false },
            charges: cfg.charges || {},
            payments: cfg.payments || [],
            sub: Number(cfg.sub) || 0,
            disc: Number(cfg.disc) || 0,
            taxRate: Number(cfg.taxRate) || 0,
            taxType: cfg.taxType || 'percent',
            pointsBalance: Number(cfg.pointsBalance) || 0,
            pointValue: Number(cfg.pointValue) || 0,
            maxRedeemable: Number(cfg.maxRedeemable) || 0,
            usePoints: false,
            redeemPoints: 0,
            dispatch: cfg.initDispatch || '',
            payment: cfg.initPayment || '',
            looking: false,
            f: {
                first: cfg.old.first || '', last: cfg.old.last || '', phone: cfg.old.phone || '',
                address1: cfg.old.address1 || '', address2: cfg.old.address2 || '',
                city: cfg.old.city || '', tehsil: cfg.old.tehsil || '',
                district: cfg.old.district || '', province: cfg.old.province || '',
                country: cfg.old.country || 'Pakistan', postcode: cfg.old.postcode || '',
            },
            get isPk() { return this.f.country === 'Pakistan'; },
            get provinceNames() { return Object.keys(this.provinces); },
            get districts() { return this.provinces[this.f.province] || []; },
            get charge() { return Number(this.charges[this.dispatch] ?? 0); },
            get selectedPayment() { return this.payments.find(p => p.name === this.payment) || {}; },
            // Points redemption (#B): clamp the entered points to what's allowed,
            // and convert to a rupee discount that also lowers the taxable base.
            get appliedPoints() {
                if (!this.usePoints || this.pointValue <= 0) return 0;
                let p = Math.floor(Number(this.redeemPoints) || 0);
                return Math.max(0, Math.min(p, this.maxRedeemable));
            },
            get pointsDiscount() { return Math.round(this.appliedPoints * this.pointValue * 100) / 100; },
            get afterDiscount() { return Math.max(0, this.sub - this.disc - this.pointsDiscount); },
            get taxAmt() {
                if (this.taxRate <= 0) return 0;
                if (this.taxType === 'fixed') return this.taxRate;
                const base = this.afterDiscount + this.charge;
                return Math.round(base * this.taxRate / 100 * 100) / 100;
            },
            get grand() { return Math.max(0, this.afterDiscount + this.taxAmt + this.charge); },
            money(n) { return 'Rs. ' + Math.round(Number(n) || 0).toLocaleString(); },
            // Filter a dropdown list by what's typed. Empty query (or an exact
            // match) shows the whole list so the box opens as a full dropdown.
            filterList(list, q) {
                list = list || [];
                q = (q || '').toString().toLowerCase().trim();
                if (!q) return list;
                if (list.some(x => String(x).toLowerCase() === q)) return list;
                const hit = list.filter(x => String(x).toLowerCase().includes(q));
                return hit.length ? hit : list;
            },
            async lookupPhone() {
                const phone = (this.f.phone || '').replace(/\D+/g, '');
                if (phone.length < 7) { window.toast && window.toast('Enter a valid phone first', 'error'); return; }
                this.looking = true;
                try {
                    const res = await fetch('{{ route('shop.checkout.lookup') }}?phone=' + encodeURIComponent(phone), { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    if (data.ok && data.address) {
                        const a = data.address;
                        this.f.first = a.shipping_first_name || this.f.first;
                        this.f.last = a.shipping_last_name || this.f.last;
                        this.f.address1 = a.shipping_address1 || '';
                        this.f.address2 = a.shipping_address2 || '';
                        this.f.country = a.shipping_country || 'Pakistan';
                        this.f.province = a.shipping_province || '';
                        // district depends on province list — set after province
                        this.$nextTick(() => { this.f.district = a.shipping_district || ''; });
                        this.f.tehsil = a.shipping_tehsil || '';
                        this.f.city = a.shipping_city || '';
                        this.f.postcode = a.shipping_post_code || '';
                        window.toast && window.toast('Saved address filled in', 'success');
                    } else {
                        window.toast && window.toast('No saved address found for this number', 'info');
                    }
                } catch (e) {
                    window.toast && window.toast('Lookup failed', 'error');
                } finally {
                    this.looking = false;
                }
            },
        };
    };
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Customer Khata — ' . $customer->name)

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-5xl">

        {{-- ── Header ── --}}
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <a href="{{ route('admin.customers.show', $customer) }}"
                    class="text-sm text-blue-600 hover:underline mb-1 block">← Back to Customer</a>
                <h1 class="text-2xl font-bold text-gray-800">📒 Customer Khata</h1>
                <p class="text-sm text-gray-500 mt-1">Account statement for <strong>{{ $customer->name }}</strong>
                    <br><span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</span>
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.customers.khata', ['customer' => $customer->id, 'export' => 'csv'] + request()->query()) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <button onclick="window.print()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-5 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-5">
                {{ session('error') }}
            </div>
        @endif

        {{-- ── LINKED-SUPPLIER BANNER (when this customer is also a supplier) ── --}}
        @if ($linkedSupplier)
            @php
                // Round to 2 dp (DB precision) so tiny float residues like 2.27e-13 don't
                // sneak through and pre-fill nonsense into the modal.
                $maxOffset = round(min(max(0, (float)($customer->current_balance ?? 0)), max(0, (float)$linkedSupplierBalance)), 2);
            @endphp
            <div class="mb-5 bg-white border-2 rounded-xl overflow-hidden" style="border-color:#0891b2;"
                 x-data="{ showOffset:false }">
                <div class="px-4 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                     style="background:linear-gradient(135deg,#ecfeff,#f0f9ff);">
                    <div class="flex items-start gap-3">
                        <span class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                              style="background:#0891b2;color:#fff;">
                            <i class="fas fa-link"></i>
                        </span>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wide" style="color:#0891b2;">Linked party</div>
                            <div class="text-sm font-semibold text-gray-800">
                                Also a supplier:
                                <a href="{{ route('suppliers.ledger', $linkedSupplier) }}"
                                   class="hover:underline" style="color:#0e7490;">{{ $linkedSupplier->name }}</a>
                                @if ($linkedSupplier->company_name)
                                    <span class="text-gray-500">· {{ $linkedSupplier->company_name }}</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                Customer ka khata: <span class="font-semibold text-rose-700">Rs. {{ number_format(max(0,(float)$customer->current_balance), 0) }}</span> <span class="text-gray-400">(لینا)</span>
                                · Hamara dena: <span class="font-semibold text-amber-700">Rs. {{ number_format(max(0,(float)$linkedSupplierBalance), 0) }}</span> <span class="text-gray-400">(دینا)</span>
                                · <span class="font-bold {{ $linkedNetBalance > 0 ? 'text-rose-700' : ($linkedNetBalance < 0 ? 'text-emerald-700' : 'text-gray-600') }}">
                                    Net: Rs. {{ number_format(abs($linkedNetBalance), 0) }}
                                    {{ $linkedNetBalance > 0 ? '(Lena / لینا)' : ($linkedNetBalance < 0 ? '(Dena / دینا)' : '(Hisaab saaf / حساب صاف)') }}
                                  </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 items-center">
                        @if ($maxOffset > 0)
                            <button type="button" @click="showOffset = true"
                                    class="inline-flex items-center gap-2 px-3 py-2 text-white text-xs font-semibold rounded-lg shadow-sm"
                                    style="background:linear-gradient(135deg,#0891b2,#0e7490);"
                                    title="Lena-Dena ek dosray say katwa do — koi cash nahi chalega">
                                <i class="fas fa-right-left"></i> Adjust Rs. {{ number_format($maxOffset, 0) }} <span class="opacity-80">(لینا/دینا کاٹیں)</span>
                            </button>
                        @else
                            <span class="text-[11px] text-gray-500 italic">Adjust ke liye kuch nahi (hisaab saaf)</span>
                        @endif
                        <a href="{{ route('admin.customers.combined-statement', $customer) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-xs font-semibold rounded-lg">
                            <i class="fas fa-file-invoice"></i> Combined statement
                        </a>
                        <form action="{{ route('admin.linked-party.unlink') }}" method="POST"
                              onsubmit="return confirm('Unlink {{ addslashes($customer->name) }} from supplier {{ addslashes($linkedSupplier->name) }}? This does NOT undo any past offsets.')">
                            @csrf
                            <input type="hidden" name="from_type" value="customer">
                            <input type="hidden" name="from_id" value="{{ $customer->id }}">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-rose-200 hover:bg-rose-50 text-rose-600 text-xs font-semibold rounded-lg">
                                <i class="fas fa-link-slash"></i> Unlink
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Offset modal --}}
                <div x-show="showOffset" x-cloak
                     style="position:fixed;inset:0;background:rgba(15,23,42,.6);z-index:60;display:flex;align-items:center;justify-content:center;padding:16px;">
                    <div @click.outside="showOffset = false"
                         class="bg-white rounded-xl shadow-2xl w-full max-w-md p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-right-left" style="color:#0891b2;"></i> Lena-Dena Adjust (حساب ملانا)
                            </h3>
                            <button type="button" @click="showOffset = false" class="text-gray-400 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="text-xs text-gray-600 mb-4 leading-relaxed bg-cyan-50/50 border border-cyan-100 rounded-md p-3">
                            <strong class="text-cyan-800">Yeh kya karega?</strong>
                            <br>Customer <strong>{{ $customer->name }}</strong> ka khata aur supplier <strong>{{ $linkedSupplier->name }}</strong> ka dena — dono ek saath barabar amount say kam ho jayenge.
                            <br><em class="text-gray-500">Cash ka koi lain-dain nahi hota — sirf hisaab apas mein adjust hota hai.</em>
                            <br><span class="text-gray-500">(Misaal: Customer ka 1000 Lena hai, supplier ka 600 Dena hai → 600 ka Adjust karne se: Customer pe 400 Lena rah jayega, supplier ka hisaab saaf.)</span>
                        </div>

                        <form action="{{ route('admin.linked-party.offset') }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                            <input type="hidden" name="supplier_id" value="{{ $linkedSupplier->id }}">
                            <input type="hidden" name="redirect_to" value="customer">

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Amount (Rs.)</label>
                                <input type="number" name="amount" step="0.01" min="0.01"
                                       max="{{ number_format($maxOffset, 2, '.', '') }}"
                                       value="{{ number_format($maxOffset, 2, '.', '') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                <p class="text-[11px] text-gray-500 mt-1">Max possible: Rs. {{ number_format($maxOffset, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Date</label>
                                <input type="date" name="transaction_date" value="{{ now()->toDateString() }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Notes (optional)</label>
                                <input type="text" name="notes" maxlength="500" placeholder="e.g. Mar-26 reconciliation"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                            </div>

                            <div class="flex gap-2 pt-2">
                                <button type="button" @click="showOffset = false"
                                        class="flex-1 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">
                                    Cancel
                                </button>
                                <button type="submit"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-lg"
                                        style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                                    <i class="fas fa-check"></i> Adjust Karein (لینا/دینا کاٹیں)
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            {{-- Not linked yet — small "link to supplier" affordance --}}
            @if ($availableSuppliers->count())
                <div class="mb-5 bg-white border border-dashed border-gray-300 rounded-xl p-4"
                     x-data="{ showLink:false }">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-link text-sm"></i>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Is this customer also a supplier?</div>
                                <div class="text-xs text-gray-500">Link them once and you can offset receivables against payables in one click.</div>
                            </div>
                        </div>
                        <button type="button" @click="showLink = !showLink"
                                class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                            <i class="fas fa-link mr-1"></i> Link supplier
                        </button>
                    </div>

                    <div x-show="showLink" x-cloak class="mt-4 pt-4 border-t border-gray-200">
                        <form action="{{ route('admin.linked-party.link') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                            <input type="hidden" name="redirect_to" value="customer">
                            <select name="supplier_id" required
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="">— Select supplier —</option>
                                @foreach ($availableSuppliers as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->name }}{{ $s->company_name ? ' (' . $s->company_name . ')' : '' }}{{ $s->phone ? ' · ' . $s->phone : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-white text-sm font-semibold rounded-lg"
                                    style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                                <i class="fas fa-link"></i> Link
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endif

        {{-- ── MAIN LAYOUT: 2 columns on large screens ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ════════════════════════════════════════
             LEFT COLUMN: Customer Info + Payment Form
             ════════════════════════════════════════ --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- Customer Info Card --}}
                <div class="bg-white rounded-lg shadow p-5 border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-circle text-blue-400"></i> Customer Info
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400">Name</p>
                            <p class="font-bold text-gray-800">{{ $customer->name }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ $customer->customer_type }}</p>
                        </div>
                        @if ($customer->phone)
                            <div>
                                <p class="text-xs text-gray-400">Phone</p>
                                <p class="font-medium text-gray-700">{{ $customer->phone }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-gray-400">Total Orders</p>
                            <p class="text-xl font-bold text-blue-600">{{ $orders->count() }}</p>
                        </div>
                    </div>

                    {{-- Balance Status --}}
                    @php $balance = $customer->current_balance ?? 0; @endphp
                    <div
                        class="mt-4 rounded-lg p-4 text-center
                    {{ $balance > 0 ? 'bg-red-50 border border-red-200' : ($balance < 0 ? 'bg-blue-50 border border-blue-200' : 'bg-green-50 border border-green-200') }}">
                        <p
                            class="text-xs font-medium
                        {{ $balance > 0 ? 'text-red-500' : ($balance < 0 ? 'text-blue-500' : 'text-green-500') }}">
                            {{ $balance > 0 ? '⚠️ Balance Due' : ($balance < 0 ? '✅ Advance Credit' : '✅ Account Clear') }}
                        </p>
                        <p
                            class="text-2xl font-bold mt-1
                        {{ $balance > 0 ? 'text-red-600' : ($balance < 0 ? 'text-blue-600' : 'text-green-600') }}">
                            Rs. {{ number_format(abs($balance), 0) }}
                        </p>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                 KHATA PAYMENT / PAYOUT FORM
                 ══════════════════════════════════════ --}}
                <div x-data="{ direction: 'in' }" class="bg-white rounded-lg shadow border"
                    :class="direction === 'in' ? 'border-blue-200' : 'border-orange-200'">
                    <div class="text-white px-5 py-3 rounded-t-lg transition-colors"
                        :class="direction === 'in' ? 'bg-blue-600' : 'bg-orange-600'">
                        <h3 class="font-semibold flex items-center gap-2">
                            <i class="fas" :class="direction === 'in' ? 'fa-hand-holding-usd' : 'fa-money-bill-wave'"></i>
                            <span x-show="direction === 'in'">Receive Payment (رقم وصول کریں)</span>
                            <span x-show="direction === 'out'" x-cloak>Pay Out / Refund (رقم واپس کریں)</span>
                        </h3>
                        <p class="text-xs mt-0.5"
                            :class="direction === 'in' ? 'text-blue-200' : 'text-orange-200'">
                            <span x-show="direction === 'in'">Record cash received from customer</span>
                            <span x-show="direction === 'out'" x-cloak>Record cash given back to customer (refund advance)</span>
                        </p>
                    </div>

                    {{-- Direction Toggle --}}
                    <div class="grid grid-cols-2 gap-0 border-b">
                        <button type="button" @click="direction = 'in'"
                            class="py-3 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                            :class="direction === 'in' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-600' : 'text-gray-500 hover:bg-gray-50'">
                            <i class="fas fa-arrow-down"></i> Cash IN
                        </button>
                        <button type="button" @click="direction = 'out'"
                            class="py-3 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                            :class="direction === 'out' ? 'bg-orange-50 text-orange-700 border-b-2 border-orange-600' : 'text-gray-500 hover:bg-gray-50'">
                            <i class="fas fa-arrow-up"></i> Cash OUT
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.customers.khata.payment', $customer) }}"
                        class="p-5 space-y-4">
                        @csrf
                        <input type="hidden" name="direction" :value="direction">

                        {{-- Current Balance Reminder (Cash IN context) --}}
                        <template x-if="direction === 'in'">
                            <div>
                                @if ($balance > 0)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm">
                                        <p class="text-red-600 font-medium">
                                            Customer ka baqaya / Hamara lena: <strong>Rs. {{ number_format($balance, 0) }}</strong> <span class="text-red-400">(لینا)</span>
                                        </p>
                                        <button type="button"
                                            onclick="document.getElementById('payAmount').value = {{ $balance }}; calcRemaining({{ $balance }})"
                                            class="mt-1 text-xs text-red-500 underline hover:text-red-700">
                                            → Click to fill full amount
                                        </button>
                                    </div>
                                @elseif($balance == 0)
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">
                                        ✅ Account is currently settled. Any payment will create advance credit.
                                    </div>
                                @else
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700">
                                        ℹ️ Customer has Rs. {{ number_format(abs($balance), 0) }} advance credit. New payment increases their advance.
                                    </div>
                                @endif
                            </div>
                        </template>

                        {{-- Current Balance Reminder (Cash OUT context) --}}
                        <template x-if="direction === 'out'">
                            <div>
                                @if ($balance < 0)
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                                        <p class="text-blue-700 font-medium">
                                            Customer advance: <strong>Rs. {{ number_format(abs($balance), 0) }}</strong>
                                        </p>
                                        <button type="button"
                                            onclick="document.getElementById('payAmount').value = {{ abs($balance) }}; calcRemaining({{ abs($balance) }})"
                                            class="mt-1 text-xs text-blue-600 underline hover:text-blue-800">
                                            → Click to refund full advance
                                        </button>
                                    </div>
                                @elseif($balance == 0)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                        ⚠️ Hisaab saaf hai. Cash-out karne se customer pe baqaya ban jayega (لینا).
                                    </div>
                                @else
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                        ⚠️ Customer ka pehle se baqaya Rs. {{ number_format($balance, 0) }} hai. Cash-out karne se aur barh jayega (مزید لینا).
                                    </div>
                                @endif
                            </div>
                        </template>

                        {{-- Amount --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <span x-show="direction === 'in'">Amount Received (Rs.)</span>
                                <span x-show="direction === 'out'" x-cloak>Amount Paid Out (Rs.)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="payAmount" name="amount" min="1" step="0.01" required
                                placeholder="Enter amount..."
                                class="w-full border-2 rounded-lg px-3 py-2 text-lg font-bold focus:outline-none transition-colors"
                                :class="direction === 'in' ? 'border-blue-300 focus:border-blue-500' : 'border-orange-300 focus:border-orange-500'"
                                oninput="calcRemaining(this.value)">

                            {{-- Live remaining preview --}}
                            <div id="remainingPreview" class="mt-2 text-sm hidden">
                                <div class="bg-gray-50 rounded-lg p-2 space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Current Balance:</span>
                                        <span class="font-medium {{ $balance > 0 ? 'text-red-600' : ($balance < 0 ? 'text-blue-600' : 'text-green-600') }}">
                                            Rs. {{ number_format(abs($balance), 0) }}
                                            {{ $balance > 0 ? '(Due)' : ($balance < 0 ? '(Adv)' : '') }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">
                                            <span x-show="direction === 'in'">Receiving:</span>
                                            <span x-show="direction === 'out'" x-cloak>Paying out:</span>
                                        </span>
                                        <span class="font-medium" id="payingDisplay"
                                            :class="direction === 'in' ? 'text-green-600' : 'text-orange-600'">Rs. 0</span>
                                    </div>
                                    <div class="flex justify-between border-t pt-1">
                                        <span class="font-semibold">After:</span>
                                        <span class="font-bold" id="afterDisplay">Rs. 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <span x-show="direction === 'in'">Payment Method</span>
                                <span x-show="direction === 'out'" x-cloak>Payout Method</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" required
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="">Select method...</option>
                                @foreach($paymentMethods as $pm)
                                    @if($pm->name !== 'pending')
                                        <option value="{{ $pm->name }}">{{ $pm->label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        {{-- Payment Date --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        {{-- Notes --}}
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Notes (optional)</label>
                            <input type="text" name="notes"
                                :placeholder="direction === 'in' ? 'e.g. Paid via JazzCash, cheque no...' : 'e.g. Refund of advance, returned in cash...'"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <button type="submit"
                            class="w-full text-white py-3 rounded-lg font-bold text-base transition-colors"
                            :class="direction === 'in' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-600 hover:bg-orange-700'">
                            <span x-show="direction === 'in'">✅ Record Payment</span>
                            <span x-show="direction === 'out'" x-cloak>📤 Record Cash Out</span>
                        </button>
                    </form>
                </div>

            </div>

            {{-- ════════════════════════════════════════
             RIGHT COLUMN: Transaction History
             ════════════════════════════════════════ --}}
            <div class="lg:col-span-2">

                {{-- Period Summary Cards --}}
                @php $hasPayouts = ($summary['payouts_count'] ?? 0) > 0; @endphp
                <div class="grid grid-cols-2 {{ $hasPayouts ? 'sm:grid-cols-5' : 'sm:grid-cols-4' }} gap-3 mb-5">
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                        <p class="text-xs text-gray-500">Total Billed</p>
                        <p class="text-lg font-bold text-blue-600">Rs. {{ number_format($summary['total_billed'], 0) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                        <p class="text-xs text-gray-500">Total Paid</p>
                        <p class="text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid'], 0) }}</p>
                    </div>
                    @if ($hasPayouts)
                        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
                            <p class="text-xs text-gray-500">Cash Paid Out</p>
                            <p class="text-lg font-bold text-orange-600">Rs. {{ number_format($summary['total_khata_payouts'], 0) }}</p>
                        </div>
                    @endif
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
                        <p class="text-xs text-gray-500">Outstanding</p>
                        <p class="text-lg font-bold text-red-600">Rs. {{ number_format($summary['total_balance'], 0) }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
                        <p class="text-xs text-gray-500">Entries</p>
                        <p class="text-lg font-bold text-purple-600">
                            {{ $summary['payments_count'] }}@if ($hasPayouts) <span class="text-xs text-orange-500">+ {{ $summary['payouts_count'] }}</span>@endif
                        </p>
                    </div>
                </div>

                {{-- Date Filter --}}
                <form method="GET" action="{{ route('admin.customers.khata', $customer) }}"
                    class="bg-white rounded-lg shadow p-3 mb-5 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From Date</label>
                        <input type="date" name="from_date" value="{{ $fromDate }}"
                            class="border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To Date</label>
                        <input type="date" name="to_date" value="{{ $toDate }}"
                            class="border rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
                        Filter
                    </button>
                    <a href="{{ route('admin.customers.khata', $customer) }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                        Reset
                    </a>
                </form>

                {{-- Print-only header --}}
                <div id="print-header" style="display:none;">
                    <h1 style="font-size:20px;font-weight:900;margin-bottom:4px;text-align:center;">{{ is_object($currentBranch ?? null) ? $currentBranch->name : config('app.name', 'Salal Collection') }}</h1>
                    <h2 style="font-size:16px;font-weight:bold;margin-bottom:2px;text-align:center;">Customer Khata — {{ $customer->name }}</h2>
                    <p style="font-size:12px;color:#666;margin-bottom:2px;text-align:center;">{{ $customer->phone ?? '' }}</p>
                    <p style="font-size:11px;color:#888;text-align:center;">Statement: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
                    <p style="font-size:11px;color:#888;margin-bottom:10px;text-align:center;">Balance: Rs. {{ number_format(abs($customer->current_balance ?? 0), 0) }} {{ ($customer->current_balance ?? 0) > 0 ? '(Due)' : (($customer->current_balance ?? 0) < 0 ? '(Advance)' : '(Clear)') }}</p>
                    <hr style="margin-bottom:8px;">
                </div>

                {{-- ── Unified Transaction History ── --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-4 border-b bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-700">Transaction History (کھاتہ)</h3>
                        <div class="flex items-center gap-3 text-xs flex-wrap">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-blue-200 inline-block"></span> Sale Bill
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-green-200 inline-block"></span> Payment Received
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-orange-200 inline-block"></span> Cash Paid Out
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                                <tr>
                                    <th class="px-3 py-3 text-left">Date</th>
                                    <th class="px-3 py-3 text-left">Details</th>
                                    <th class="px-3 py-3 text-right text-red-500">Debit (Bill / Cash Out)</th>
                                    <th class="px-3 py-3 text-right text-green-600">Credit (Paid)</th>
                                    <th class="px-3 py-3 text-right bg-yellow-50">Balance</th>
                                    <th class="px-3 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">

                                @forelse($transactions as $txn)
                                    @php
                                        $isPayment = $txn['type'] === 'payment';
                                        $isPayout  = $txn['type'] === 'payout';
                                        $isOffset  = $txn['type'] === 'offset';
                                        $isOrder   = $txn['type'] === 'order';
                                        $rowClass  = $isPayment ? 'bg-green-50/50' : ($isPayout ? 'bg-orange-50/50' : ($isOffset ? 'bg-cyan-50/50' : ''));
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition {{ $rowClass }}">

                                        <td class="px-3 py-3 text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($txn['date'])->format('d M Y') }}
                                            @if ($isOrder)
                                                <br><span
                                                    class="text-gray-300">{{ \Carbon\Carbon::parse($txn['date'])->format('h:i A') }}</span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3">
                                            @if ($isPayment)
                                                <div>
                                                    <p class="font-semibold text-green-700">Payment Received</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ ucfirst(str_replace('_', ' ', $txn['method'] ?? '')) }}
                                                        @if (!empty($txn['notes']))
                                                            — {{ $txn['notes'] }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @elseif ($isPayout)
                                                <div>
                                                    <p class="font-semibold text-orange-700">Cash Paid Out</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ ucfirst(str_replace('_', ' ', $txn['method'] ?? '')) }}
                                                        @if (!empty($txn['notes']))
                                                            — {{ $txn['notes'] }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @elseif ($isOffset)
                                                <div>
                                                    <p class="font-semibold" style="color:#0e7490;"><i class="fas fa-right-left text-xs mr-1"></i>Adjust — Supplier ke saath (حساب کٹا)</p>
                                                    <p class="text-xs text-gray-500">
                                                        Ref: {{ $txn['reference'] }}
                                                        @if (!empty($txn['notes']))
                                                            — {{ $txn['notes'] }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @else
                                                <div>
                                                    <a href="{{ route('admin.pos.receipt', $txn['id']) }}"
                                                        target="_blank"
                                                        class="font-semibold text-blue-600 hover:underline font-mono text-xs">
                                                        {{ $txn['reference'] }}
                                                    </a>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        {{ $txn['items_count'] }} item(s)
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        <td
                                            class="px-3 py-3 text-right {{ $isOrder ? 'text-red-600 font-semibold' : ($isPayout ? 'text-orange-600 font-bold' : 'text-gray-200') }}">
                                            @if ($isOrder)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @elseif ($isPayout)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td
                                            class="px-3 py-3 text-right {{ $isPayment ? 'text-green-600 font-bold' : ($isOffset ? 'font-bold' : (($isOrder && $txn['paid'] > 0) ? 'text-green-500' : 'text-gray-200')) }}"
                                            @if ($isOffset) style="color:#0891b2;" @endif>
                                            @if ($isPayment)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @elseif($isOffset)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @elseif($isOrder && $txn['paid'] > 0)
                                                Rs. {{ number_format($txn['paid'], 0) }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td
                                            class="px-3 py-3 text-right bg-yellow-50 font-bold
                                    {{ $txn['running_balance'] > 0 ? 'text-red-600' : ($txn['running_balance'] < 0 ? 'text-blue-600' : 'text-green-600') }}">
                                            @if ($txn['running_balance'] > 0)
                                                Rs. {{ number_format($txn['running_balance'], 0) }}
                                            @elseif($txn['running_balance'] < 0)
                                                <span class="text-xs text-blue-500">Adv.</span>
                                                Rs. {{ number_format(abs($txn['running_balance']), 0) }}
                                            @else
                                                <span class="text-green-500 font-bold">✓ Clear</span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-center">
                                            @if ($isPayment || $isPayout)
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route('admin.customers.khata.payment.voucher', [$customer, $txn['id']]) }}"
                                                        class="text-xs {{ $isPayout ? 'text-orange-400 hover:text-orange-600 hover:bg-orange-50' : 'text-blue-400 hover:text-blue-600 hover:bg-blue-50' }} px-2 py-1 rounded"
                                                        title="View Voucher">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('admin.customers.khata.payment.delete', [$customer, $txn['id']]) }}"
                                                        onsubmit="return confirm('Delete this {{ $isPayout ? 'cash-out' : 'payment' }} of Rs.{{ number_format($txn['amount'], 0) }}? Customer balance will be adjusted.')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded hover:bg-red-50"
                                                            title="Delete / Reverse">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @elseif ($isOffset)
                                                <span class="text-[10px] text-gray-400 italic" title="Yeh entry supplier ke saath jori hui hai — sirf yahan se delete nahi hoti">Mila hua</span>
                                            @else
                                                <a href="{{ route('admin.pos.receipt', $txn['id']) }}" target="_blank"
                                                    class="text-blue-400 hover:text-blue-600 text-xs"
                                                    title="View Receipt">
                                                    <i class="fas fa-receipt"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                            No transactions found for this period.
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>

                            @if (count($transactions))
                                <tfoot class="bg-gray-100 font-bold text-sm border-t-2">
                                    <tr>
                                        <td colspan="2" class="px-3 py-3 text-gray-600">Period Total</td>
                                        <td class="px-3 py-3 text-right text-red-600">
                                            Rs. {{ number_format($summary['total_billed'] + ($summary['total_khata_payouts'] ?? 0), 0) }}
                                            @if (($summary['total_khata_payouts'] ?? 0) > 0)
                                                <br><span class="text-xs font-normal text-orange-600">incl. Rs. {{ number_format($summary['total_khata_payouts'], 0) }} cash out</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-right text-green-600">Rs.
                                            {{ number_format($summary['total_paid'], 0) }}
                                        </td>
                                        <td
                                            class="px-3 py-3 text-right bg-yellow-100 {{ ($customer->current_balance ?? 0) > 0 ? 'text-red-700' : 'text-green-700' }}">
                                            Rs. {{ number_format(abs($customer->current_balance ?? 0), 0) }}
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {
                .no-print,
                nav,
                aside,
                header,
                form,
                button,
                .flex.gap-2.flex-wrap {
                    display: none !important;
                }

                /* Show print header */
                #print-header {
                    display: block !important;
                }

                /* Hide action column in print */
                table th:last-child,
                table td:last-child {
                    display: none !important;
                }

                /* Keep order number links visible in print */
                a[href] {
                    color: #000 !important;
                    text-decoration: none !important;
                }

                body {
                    background: white;
                    font-size: 11px;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .shadow {
                    box-shadow: none !important;
                }

                .lg\:col-span-1 {
                    display: none !important;
                }

                .lg\:col-span-2 {
                    grid-column: span 3 !important;
                }

                .container {
                    max-width: 100% !important;
                    padding: 0 !important;
                }

                h1 { font-size: 16px !important; }
                p.text-sm { font-size: 11px !important; }

                table {
                    width: 100% !important;
                    border-collapse: collapse !important;
                }
                table th, table td {
                    border: 1px solid #d1d5db !important;
                    padding: 4px 6px !important;
                    font-size: 10px !important;
                }
                table thead {
                    background: #f3f4f6 !important;
                }

                .grid.grid-cols-2 {
                    display: flex !important;
                    gap: 8px !important;
                }
                .grid.grid-cols-2 > div {
                    flex: 1 !important;
                    border: 1px solid #d1d5db !important;
                    padding: 6px !important;
                }

                .rounded-lg { border-radius: 0 !important; }
                tfoot td { font-weight: bold !important; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function calcRemaining(value) {
                const amount = parseFloat(value) || 0;
                const balance = {{ $customer->current_balance ?? 0 }};
                // Read direction from the hidden input bound to Alpine
                const dirInput = document.querySelector('input[name="direction"]');
                const direction = dirInput ? dirInput.value : 'in';
                const after = direction === 'out' ? balance + amount : balance - amount;
                const preview = document.getElementById('remainingPreview');
                const paying = document.getElementById('payingDisplay');
                const afterEl = document.getElementById('afterDisplay');

                if (amount > 0) {
                    preview.classList.remove('hidden');
                    paying.textContent = 'Rs. ' + amount.toLocaleString('en-PK');
                    let suffix = '';
                    if (after === 0) suffix = ' ✅ Settled';
                    else if (after < 0) suffix = ' (Advance)';
                    else suffix = ' (Due)';
                    afterEl.textContent = 'Rs. ' + Math.abs(after).toLocaleString('en-PK') + suffix;
                    afterEl.className = 'font-bold ' + (after > 0 ? 'text-red-600' : (after < 0 ? 'text-blue-600' : 'text-green-600'));
                } else {
                    preview.classList.add('hidden');
                }
            }
        </script>
    @endpush

@endsection

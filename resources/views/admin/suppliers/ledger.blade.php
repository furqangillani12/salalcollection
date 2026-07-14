@extends('layouts.admin')

@section('title', 'Supplier Ledger — ' . $supplier->name)

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-5xl">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <a href="{{ route('suppliers.show', $supplier) }}"
                    class="text-sm text-blue-600 hover:underline mb-1 block">&larr; Back to Supplier</a>
                <h1 class="text-2xl font-bold text-gray-800">Supplier Ledger (سپلائر کھاتہ)</h1>
                <p class="text-sm text-gray-500 mt-1">Account statement for <strong>{{ $supplier->name }}</strong>
                    @if($supplier->company_name) · {{ $supplier->company_name }} @endif
                </p>
            </div>
            <button onclick="window.print()"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-print"></i> Print
            </button>
        </div>

        {{-- Flash --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-5 flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-5">
                {{ session('error') }}
            </div>
        @endif

        {{-- ── LINKED-CUSTOMER BANNER (when this supplier is also a customer) ── --}}
        @if ($linkedCustomer)
            @php
                // Round to 2 dp (DB precision) so tiny float residues like 2.27e-13 don't
                // sneak through and pre-fill nonsense into the modal.
                $maxOffsetSup = round(min(max(0, (float) $linkedCustomerBalance), max(0, (float)($summary['balance'] ?? 0))), 2);
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
                                Also a customer:
                                <a href="{{ route('admin.customers.khata', $linkedCustomer) }}"
                                   class="hover:underline" style="color:#0e7490;">{{ $linkedCustomer->name }}</a>
                            </div>
                            <div class="text-[11px] text-gray-500 mt-0.5">
                                Hamara dena: <span class="font-semibold text-amber-700">Rs. {{ number_format(max(0,(float)($summary['balance'] ?? 0)), 0) }}</span> <span class="text-gray-400">(دینا)</span>
                                · Customer ka khata: <span class="font-semibold text-rose-700">Rs. {{ number_format(max(0,(float)$linkedCustomerBalance), 0) }}</span> <span class="text-gray-400">(لینا)</span>
                                · <span class="font-bold {{ $linkedNetBalance > 0 ? 'text-amber-700' : ($linkedNetBalance < 0 ? 'text-emerald-700' : 'text-gray-600') }}">
                                    Net: Rs. {{ number_format(abs($linkedNetBalance), 0) }}
                                    {{ $linkedNetBalance > 0 ? '(Dena / دینا)' : ($linkedNetBalance < 0 ? '(Lena / لینا)' : '(Hisaab saaf / حساب صاف)') }}
                                  </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 items-center">
                        @if ($maxOffsetSup > 0)
                            <button type="button" @click="showOffset = true"
                                    class="inline-flex items-center gap-2 px-3 py-2 text-white text-xs font-semibold rounded-lg shadow-sm"
                                    style="background:linear-gradient(135deg,#0891b2,#0e7490);"
                                    title="Lena-Dena ek dosray say katwa do — koi cash nahi chalega">
                                <i class="fas fa-right-left"></i> Adjust Rs. {{ number_format($maxOffsetSup, 0) }} <span class="opacity-80">(لینا/دینا کاٹیں)</span>
                            </button>
                        @else
                            <span class="text-[11px] text-gray-500 italic">Adjust ke liye kuch nahi (hisaab saaf)</span>
                        @endif
                        <a href="{{ route('admin.customers.combined-statement', $linkedCustomer) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-xs font-semibold rounded-lg">
                            <i class="fas fa-file-invoice"></i> Combined statement
                        </a>
                        <form action="{{ route('admin.linked-party.unlink') }}" method="POST"
                              onsubmit="return confirm('Unlink supplier {{ addslashes($supplier->name) }} from customer {{ addslashes($linkedCustomer->name) }}? This does NOT undo any past offsets.')">
                            @csrf
                            <input type="hidden" name="from_type" value="supplier">
                            <input type="hidden" name="from_id" value="{{ $supplier->id }}">
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
                            <br>Supplier <strong>{{ $supplier->name }}</strong> ka dena aur customer <strong>{{ $linkedCustomer->name }}</strong> ka khata — dono ek saath barabar amount say kam ho jayenge.
                            <br><em class="text-gray-500">Cash ka koi lain-dain nahi hota — sirf hisaab apas mein adjust hota hai.</em>
                            <br><span class="text-gray-500">(Misaal: Supplier ka 600 Dena hai, customer ka 1000 Lena hai → 600 ka Adjust karne se: Supplier ka hisaab saaf, customer pe 400 Lena rah jayega.)</span>
                        </div>

                        <form action="{{ route('admin.linked-party.offset') }}" method="POST" class="space-y-3">
                            @csrf
                            <input type="hidden" name="customer_id" value="{{ $linkedCustomer->id }}">
                            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                            <input type="hidden" name="redirect_to" value="supplier">

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Amount (Rs.)</label>
                                <input type="number" name="amount" step="0.01" min="0.01"
                                       max="{{ number_format($maxOffsetSup, 2, '.', '') }}"
                                       value="{{ number_format($maxOffsetSup, 2, '.', '') }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                <p class="text-[11px] text-gray-500 mt-1">Max possible: Rs. {{ number_format($maxOffsetSup, 2) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Date</label>
                                <input type="date" name="transaction_date" value="{{ now()->toDateString() }}" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Notes (optional)</label>
                                <input type="text" name="notes" maxlength="500"
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
            @if ($availableCustomers->count())
                <div class="mb-5 bg-white border border-dashed border-gray-300 rounded-xl p-4"
                     x-data="{ showLink:false }">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <span class="w-9 h-9 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-link text-sm"></i>
                            </span>
                            <div>
                                <div class="text-sm font-semibold text-gray-700">Is this supplier also a customer?</div>
                                <div class="text-xs text-gray-500">Link them once and you can offset payables against receivables in one click.</div>
                            </div>
                        </div>
                        <button type="button" @click="showLink = !showLink"
                                class="text-xs font-semibold px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50">
                            <i class="fas fa-link mr-1"></i> Link customer
                        </button>
                    </div>
                    <div x-show="showLink" x-cloak class="mt-4 pt-4 border-t border-gray-200">
                        <form action="{{ route('admin.linked-party.link') }}" method="POST" class="flex flex-col sm:flex-row gap-2">
                            @csrf
                            <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
                            <input type="hidden" name="redirect_to" value="supplier">
                            <select name="customer_id" required
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                                <option value="">— Select customer —</option>
                                @foreach ($availableCustomers as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->name }} ({{ $c->customer_type ?? 'normal' }}){{ $c->phone ? ' · ' . $c->phone : '' }}
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

        {{-- Main Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT: Supplier Info + Payment Form --}}
            <div class="lg:col-span-1 space-y-5">

                {{-- Supplier Info --}}
                <div class="bg-white rounded-lg shadow p-5 border border-gray-200">
                    <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-truck text-blue-400"></i> Supplier Info
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-gray-400">Name</p>
                            <p class="font-bold text-gray-800">{{ $supplier->name }}</p>
                            @if($supplier->company_name)
                                <p class="text-xs text-gray-500">{{ $supplier->company_name }}</p>
                            @endif
                        </div>
                        @if ($supplier->phone)
                            <div>
                                <p class="text-xs text-gray-400">Phone</p>
                                <p class="font-medium text-gray-700">{{ $supplier->phone }}</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-gray-400">Total Purchases</p>
                            <p class="text-xl font-bold text-blue-600">{{ $supplier->purchases->count() }}</p>
                        </div>
                    </div>

                    {{-- Balance Status --}}
                    @php
                        $due = $summary['total_due'];
                        $advance = $summary['advance'] ?? 0;
                    @endphp
                    <div class="mt-4 rounded-lg p-4 text-center
                        {{ $due > 0 ? 'bg-red-50 border border-red-200' : ($advance > 0 ? 'bg-blue-50 border border-blue-200' : 'bg-green-50 border border-green-200') }}">
                        <p class="text-xs font-medium {{ $due > 0 ? 'text-red-500' : ($advance > 0 ? 'text-blue-500' : 'text-green-500') }}">
                            @if($advance > 0)
                                Advance Payment (ایڈوانس)
                            @elseif($due > 0)
                                Balance Due (بقایا)
                            @else
                                All Clear (حساب برابر)
                            @endif
                        </p>
                        <p class="text-2xl font-bold mt-1 {{ $due > 0 ? 'text-red-600' : ($advance > 0 ? 'text-blue-600' : 'text-green-600') }}">
                            Rs. {{ number_format($advance > 0 ? $advance : $due, 0) }}
                        </p>
                    </div>
                </div>

                {{-- Payment / Receipt Form --}}
                @php
                    $unpaidPurchases = $supplier->purchases->filter(fn($p) => $p->total_amount > $p->paid_amount);
                @endphp
                <div x-data="{ direction: 'out' }" class="bg-white rounded-lg shadow border"
                    :class="direction === 'out' ? 'border-blue-200' : 'border-orange-200'">
                    <div class="text-white px-5 py-3 rounded-t-lg transition-colors"
                        :class="direction === 'out' ? 'bg-blue-600' : 'bg-orange-600'">
                        <h3 class="font-semibold flex items-center gap-2">
                            <i class="fas" :class="direction === 'out' ? 'fa-hand-holding-usd' : 'fa-money-bill-wave'"></i>
                            <span x-show="direction === 'out'">Record Payment (ادائیگی)</span>
                            <span x-show="direction === 'in'" x-cloak>Record Cash Receipt (رقم وصول)</span>
                        </h3>
                        <p class="text-xs mt-0.5"
                            :class="direction === 'out' ? 'text-blue-200' : 'text-orange-200'">
                            <span x-show="direction === 'out'">Pay supplier for purchases</span>
                            <span x-show="direction === 'in'" x-cloak>Cash received back from supplier (refund / advance return)</span>
                        </p>
                    </div>

                    {{-- Direction Toggle --}}
                    <div class="grid grid-cols-2 gap-0 border-b">
                        <button type="button" @click="direction = 'out'"
                            class="py-3 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                            :class="direction === 'out' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-600' : 'text-gray-500 hover:bg-gray-50'">
                            <i class="fas fa-arrow-up"></i> Cash OUT
                        </button>
                        <button type="button" @click="direction = 'in'"
                            class="py-3 text-sm font-semibold transition-colors flex items-center justify-center gap-2"
                            :class="direction === 'in' ? 'bg-orange-50 text-orange-700 border-b-2 border-orange-600' : 'text-gray-500 hover:bg-gray-50'">
                            <i class="fas fa-arrow-down"></i> Cash IN
                        </button>
                    </div>

                    <form method="POST" action="{{ route('suppliers.payment.store', $supplier) }}" class="p-5 space-y-4">
                        @csrf
                        <input type="hidden" name="direction" :value="direction">

                        {{-- Cash OUT reminder --}}
                        <template x-if="direction === 'out'">
                            <div>
                                @if($due > 0)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm">
                                        <p class="text-red-600 font-medium">
                                            Due Amount: <strong>Rs. {{ number_format($due, 0) }}</strong>
                                        </p>
                                        <button type="button"
                                            onclick="document.getElementById('payAmount').value = {{ $due }}"
                                            class="mt-1 text-xs text-red-500 underline hover:text-red-700">
                                            &rarr; Click to fill full amount
                                        </button>
                                    </div>
                                @elseif($advance > 0)
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                                        <p class="text-blue-600 font-medium">
                                            Advance with supplier: <strong>Rs. {{ number_format($advance, 0) }}</strong>
                                        </p>
                                        <p class="text-xs text-blue-400">Any payment will add more advance.</p>
                                    </div>
                                @else
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-700">
                                        Account is settled. Any payment will be recorded as advance.
                                    </div>
                                @endif
                            </div>
                        </template>

                        {{-- Cash IN reminder --}}
                        <template x-if="direction === 'in'">
                            <div>
                                @if($advance > 0)
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                                        <p class="text-blue-700 font-medium">
                                            Advance with supplier: <strong>Rs. {{ number_format($advance, 0) }}</strong>
                                        </p>
                                        <button type="button"
                                            onclick="document.getElementById('payAmount').value = {{ $advance }}"
                                            class="mt-1 text-xs text-blue-600 underline hover:text-blue-800">
                                            &rarr; Click to receive full advance back
                                        </button>
                                    </div>
                                @elseif($due > 0)
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                        ⚠️ Supplier ka pehle se hamara dena Rs. {{ number_format($due, 0) }} hai. Cash-in karne se kam ho jayega (دینا کم).
                                    </div>
                                @else
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                                        ⚠️ Hisaab saaf hai. Cash-in karne se supplier pe hamara lena ban jayega (لینا).
                                    </div>
                                @endif
                            </div>
                        </template>

                        {{-- Against specific purchase (only for Cash OUT) --}}
                        @if($unpaidPurchases->count() > 0)
                            <div x-show="direction === 'out'">
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Against Purchase (optional)</label>
                                <select name="purchase_id" id="purchaseSelect"
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300"
                                    onchange="fillPurchaseBalance()">
                                    <option value="">General Payment</option>
                                    @foreach($unpaidPurchases as $p)
                                        <option value="{{ $p->id }}" data-due="{{ $p->total_amount - $p->paid_amount }}">
                                            {{ $p->invoice_number }} — Due: Rs. {{ number_format($p->total_amount - $p->paid_amount, 0) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <span x-show="direction === 'out'">Amount Paid (Rs.)</span>
                                <span x-show="direction === 'in'" x-cloak>Amount Received (Rs.)</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="payAmount" name="amount" min="1" step="0.01" required
                                placeholder="Enter amount..."
                                class="w-full border-2 rounded-lg px-3 py-2 text-lg font-bold focus:outline-none transition-colors"
                                :class="direction === 'out' ? 'border-blue-300 focus:border-blue-500' : 'border-orange-300 focus:border-orange-500'">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                <span x-show="direction === 'out'">Payment Method</span>
                                <span x-show="direction === 'in'" x-cloak>Receipt Method</span>
                                <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" required
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="cash">Cash</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">
                                Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Notes (optional)</label>
                            <input type="text" name="notes"
                                :placeholder="direction === 'out' ? 'e.g. Cheque #, reference...' : 'e.g. Refund of advance, returned goods...'"
                                class="w-full border rounded-lg px-3 py-2 text-sm">
                        </div>

                        <button type="submit"
                            class="w-full text-white py-3 rounded-lg font-bold text-base transition-colors"
                            :class="direction === 'out' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-orange-600 hover:bg-orange-700'">
                            <span x-show="direction === 'out'">Record Payment</span>
                            <span x-show="direction === 'in'" x-cloak>📥 Record Cash Receipt</span>
                        </button>
                    </form>
                </div>

            </div>

            {{-- RIGHT: Transaction History --}}
            <div class="lg:col-span-2">

                {{-- Summary Cards --}}
                @php $hasReceipts = ($summary['receipts_count'] ?? 0) > 0; @endphp
                <div class="grid grid-cols-2 {{ $hasReceipts ? 'sm:grid-cols-5' : 'sm:grid-cols-4' }} gap-3 mb-5">
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500">
                        <p class="text-xs text-gray-500">Total Purchased</p>
                        <p class="text-lg font-bold text-blue-600">Rs. {{ number_format($summary['total_purchased'], 0) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500">
                        <p class="text-xs text-gray-500">Total Paid</p>
                        <p class="text-lg font-bold text-green-600">Rs. {{ number_format($summary['total_paid'], 0) }}</p>
                    </div>
                    @if ($hasReceipts)
                        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-orange-500">
                            <p class="text-xs text-gray-500">Cash Received</p>
                            <p class="text-lg font-bold text-orange-600">Rs. {{ number_format($summary['total_received'] ?? 0, 0) }}</p>
                        </div>
                    @endif
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500">
                        <p class="text-xs text-gray-500">Balance Due</p>
                        <p class="text-lg font-bold text-red-600">Rs. {{ number_format($summary['total_due'], 0) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-3 border-l-4 border-purple-500">
                        <p class="text-xs text-gray-500">Entries</p>
                        <p class="text-lg font-bold text-purple-600">
                            {{ $summary['payments_count'] }}@if ($hasReceipts) <span class="text-xs text-orange-500">+ {{ $summary['receipts_count'] }}</span>@endif
                        </p>
                    </div>
                </div>

                {{-- Transaction Table --}}
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-5 py-4 border-b bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-700">Transaction History (کھاتہ)</h3>
                        <div class="flex items-center gap-3 text-xs flex-wrap">
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-red-200 inline-block"></span> Purchase
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-green-200 inline-block"></span> Payment
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-3 h-3 rounded-full bg-orange-200 inline-block"></span> Cash Received
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                                <tr>
                                    <th class="px-3 py-3 text-left">Date</th>
                                    <th class="px-3 py-3 text-left">Details</th>
                                    <th class="px-3 py-3 text-right text-red-500">Debit (Purchase / Cash In)</th>
                                    <th class="px-3 py-3 text-right text-green-600">Credit (Paid)</th>
                                    <th class="px-3 py-3 text-right bg-yellow-50">Balance</th>
                                    <th class="px-3 py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($transactions as $txn)
                                    @php
                                        $isPayment  = $txn['type'] === 'payment';
                                        $isReceipt  = $txn['type'] === 'receipt';
                                        $isPurchase = $txn['type'] === 'purchase';
                                        $isOffset   = $isPayment && (($txn['method'] ?? null) === 'offset');
                                        $rowClass   = $isOffset ? 'bg-cyan-50/50' : ($isPayment ? 'bg-green-50/50' : ($isReceipt ? 'bg-orange-50/50' : ''));
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition {{ $rowClass }}">
                                        <td class="px-3 py-3 text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($txn['date'])->format('d M Y') }}
                                        </td>

                                        <td class="px-3 py-3">
                                            @if ($isOffset)
                                                <div class="flex items-center gap-2">
                                                    <span style="color:#0e7490;" class="text-base"><i class="fas fa-right-left text-sm"></i></span>
                                                    <div>
                                                        <p class="font-semibold" style="color:#0e7490;">Adjust — Customer ke saath (حساب کٹا)</p>
                                                        <p class="text-xs text-gray-400">
                                                            @if ($txn['notes']) {{ $txn['notes'] }} @endif
                                                        </p>
                                                        <p class="text-xs font-mono text-gray-300">{{ $txn['reference'] }}</p>
                                                    </div>
                                                </div>
                                            @elseif ($isPayment)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-green-500 text-base">💳</span>
                                                    <div>
                                                        <p class="font-semibold text-green-700">Payment Made</p>
                                                        <p class="text-xs text-gray-400">
                                                            {{ ucfirst(str_replace('_', ' ', $txn['method'])) }}
                                                            @if ($txn['notes']) · {{ $txn['notes'] }} @endif
                                                        </p>
                                                        <p class="text-xs font-mono text-gray-300">{{ $txn['reference'] }}</p>
                                                    </div>
                                                </div>
                                            @elseif ($isReceipt)
                                                <div class="flex items-center gap-2">
                                                    <span class="text-orange-500 text-base">📥</span>
                                                    <div>
                                                        <p class="font-semibold text-orange-700">Cash Received</p>
                                                        <p class="text-xs text-gray-400">
                                                            {{ ucfirst(str_replace('_', ' ', $txn['method'])) }}
                                                            @if ($txn['notes']) · {{ $txn['notes'] }} @endif
                                                        </p>
                                                        <p class="text-xs font-mono text-gray-300">{{ $txn['reference'] }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div>
                                                    <a href="{{ route('purchases.invoice', $txn['id']) }}"
                                                        class="font-semibold text-blue-600 hover:underline font-mono text-xs">
                                                        {{ $txn['reference'] }}
                                                    </a>
                                                    <p class="text-xs text-gray-500 mt-0.5">
                                                        {{ $txn['items_count'] }} item(s)
                                                        @if($txn['paid'] > 0)
                                                            · Paid at purchase: Rs. {{ number_format($txn['paid'], 0) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-right {{ $isPurchase ? 'text-red-600 font-semibold' : ($isReceipt ? 'text-orange-600 font-bold' : 'text-gray-200') }}">
                                            @if ($isPurchase)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @elseif ($isReceipt)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-right {{ $isOffset ? 'font-bold' : ($isPayment ? 'text-green-600 font-bold' : (($isPurchase && $txn['paid'] > 0) ? 'text-green-500' : 'text-gray-200')) }}"
                                            @if ($isOffset) style="color:#0891b2;" @endif>
                                            @if ($isOffset)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @elseif ($isPayment)
                                                Rs. {{ number_format($txn['amount'], 0) }}
                                            @elseif($isPurchase && $txn['paid'] > 0)
                                                Rs. {{ number_format($txn['paid'], 0) }}
                                            @else
                                                —
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-right bg-yellow-50 font-bold
                                            {{ $txn['running_balance'] > 0 ? 'text-red-600' : ($txn['running_balance'] < 0 ? 'text-blue-600' : 'text-green-600') }}">
                                            @if ($txn['running_balance'] > 0)
                                                Rs. {{ number_format($txn['running_balance'], 0) }}
                                            @elseif($txn['running_balance'] == 0)
                                                <span class="text-green-500 font-bold">✓ Clear</span>
                                            @else
                                                <span class="text-blue-500">Adv. Rs. {{ number_format(abs($txn['running_balance']), 0) }}</span>
                                            @endif
                                        </td>

                                        <td class="px-3 py-3 text-center">
                                            @if ($isOffset)
                                                <span class="text-[10px] text-gray-400 italic" title="Yeh entry customer ke saath jori hui hai — sirf yahan se delete nahi hoti">Mila hua</span>
                                            @elseif ($isPayment || $isReceipt)
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route('suppliers.payment.voucher', [$supplier, $txn['id']]) }}"
                                                        class="text-xs {{ $isReceipt ? 'text-orange-400 hover:text-orange-600 hover:bg-orange-50' : 'text-blue-400 hover:text-blue-600 hover:bg-blue-50' }} px-2 py-1 rounded"
                                                        title="View Voucher">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                    <form method="POST"
                                                        action="{{ route('suppliers.payment.delete', [$supplier, $txn['id']]) }}"
                                                        onsubmit="return confirm('Delete this {{ $isReceipt ? 'cash receipt' : 'payment' }} of Rs.{{ number_format($txn['amount'], 0) }}?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="text-xs text-red-400 hover:text-red-600 px-2 py-1 rounded hover:bg-red-50"
                                                            title="Delete / Reverse">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <a href="{{ route('purchases.invoice', $txn['id']) }}"
                                                    class="text-blue-400 hover:text-blue-600 text-xs" title="View Invoice">
                                                    <i class="fas fa-file-invoice"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                                            No transactions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if (count($transactions))
                                <tfoot class="bg-gray-100 font-bold text-sm border-t-2">
                                    <tr>
                                        <td colspan="2" class="px-3 py-3 text-gray-600">Total</td>
                                        <td class="px-3 py-3 text-right text-red-600">
                                            Rs. {{ number_format($summary['total_purchased'] + ($summary['total_received'] ?? 0), 0) }}
                                            @if (($summary['total_received'] ?? 0) > 0)
                                                <br><span class="text-xs font-normal text-orange-600">incl. Rs. {{ number_format($summary['total_received'], 0) }} received</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-right text-green-600">Rs. {{ number_format($summary['total_paid'], 0) }}</td>
                                        <td class="px-3 py-3 text-right bg-yellow-100 {{ $summary['total_due'] > 0 ? 'text-red-700' : 'text-green-700' }}">
                                            Rs. {{ number_format($summary['total_due'], 0) }}
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
                .no-print, nav, aside, header, form, button { display: none !important; }
                body { background: white; }
                .shadow { box-shadow: none; }
                .lg\:col-span-1 { display: none; }
                .lg\:col-span-2 { grid-column: span 3; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function fillPurchaseBalance() {
                const sel = document.getElementById('purchaseSelect');
                if (!sel) return;
                const opt = sel.options[sel.selectedIndex];
                const due = opt?.dataset?.due;
                if (due) {
                    document.getElementById('payAmount').value = due;
                }
            }
        </script>
    @endpush

@endsection

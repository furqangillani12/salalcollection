@extends('layouts.admin')

@section('title', 'Cash In / Out')

@section('content')
<div class="px-4 pt-4 pb-0 flex items-center gap-3">
    <a href="{{ route('admin.cash.available') }}"
        class="inline-flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-emerald-100 transition">
        <i class="fas fa-wallet"></i> View Available Cash / دستیاب رقم
    </a>
    <a href="{{ route('admin.cash.history') }}"
        class="inline-flex items-center gap-2 bg-gray-100 border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
        <i class="fas fa-history"></i> Transaction History
    </a>
</div>
<div class="p-3 sm:p-6"
     x-data="cashForm({
        customers: @js($customers->map(fn($c) => [
            'id'             => $c->id,
            'name'           => $c->name,
            'phone'          => $c->phone,
            'type'           => $c->customer_type,
            'balance'        => (float) ($c->current_balance ?? 0),
            'credit_limit'   => (float) ($c->credit_limit ?? 0),
            'credit_enabled' => (bool)  ($c->credit_enabled ?? false),
        ])),
        suppliers: @js($suppliers),
        ledgerAccounts: @js($ledgerAccounts),
        defaultDate: '{{ now()->format('Y-m-d') }}',
     })">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-money-bill-wave text-emerald-600"></i>
                Cash In / Out
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Record cash received or paid against a customer, supplier, or ledger account.</p>
        </div>
        <a href="{{ route('admin.cash.history') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg text-sm font-medium shadow-sm">
            <i class="fas fa-history"></i> View History
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200 text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.cash.store') }}" @submit="onSubmit($event)">
        @csrf
        <input type="hidden" name="direction"   :value="direction">
        <input type="hidden" name="target_type" :value="targetType">
        <input type="hidden" name="target_id"   :value="targetId">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- ═══════════════════════ LEFT COLUMN ═══════════════════════ --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- ── Step 1: Direction ── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Step 1 — Direction</h2>
                    </div>
                    <div class="p-4 grid grid-cols-2 gap-3">
                        <button type="button" @click="direction='in'"
                                :class="direction==='in' ? 'border-emerald-500 bg-emerald-50 ring-2 ring-emerald-200' : 'border-gray-200 hover:border-emerald-300 hover:bg-emerald-50/40'"
                                class="border-2 rounded-xl p-4 sm:p-5 text-left transition">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-arrow-down text-lg"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-emerald-700 text-base">Cash In</div>
                                    <div class="text-[11px] text-gray-500">Money received into cash drawer</div>
                                </div>
                            </div>
                        </button>
                        <button type="button" @click="direction='out'"
                                :class="direction==='out' ? 'border-rose-500 bg-rose-50 ring-2 ring-rose-200' : 'border-gray-200 hover:border-rose-300 hover:bg-rose-50/40'"
                                class="border-2 rounded-xl p-4 sm:p-5 text-left transition">
                            <div class="flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                                    <i class="fas fa-arrow-up text-lg"></i>
                                </span>
                                <div>
                                    <div class="font-bold text-rose-700 text-base">Cash Out</div>
                                    <div class="text-[11px] text-gray-500">Money paid out from cash drawer</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- ── Step 2: Type tabs ── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Step 2 — Account Type</h2>
                    </div>
                    <div class="p-2 flex gap-1 bg-gray-50">
                        <template x-for="tab in [
                            {id:'customer', label:'Customer', icon:'fa-user'},
                            {id:'supplier', label:'Supplier', icon:'fa-truck'},
                            {id:'ledger',   label:'Ledger Account', icon:'fa-book'}
                        ]" :key="tab.id">
                            <button type="button" @click="setType(tab.id)"
                                    :class="targetType===tab.id ? 'bg-white text-blue-700 border border-blue-200 shadow-sm' : 'text-gray-600 hover:bg-white/60'"
                                    class="flex-1 px-3 py-2.5 rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                                <i class="fas" :class="tab.icon"></i>
                                <span x-text="tab.label"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Searchable picker --}}
                    <div class="p-4 sm:p-5 border-t border-gray-100">
                        <label class="block text-xs font-medium text-gray-600 mb-2">
                            <span x-text="'Select ' + tabLabel()"></span>
                            <span class="text-rose-500">*</span>
                        </label>

                        {{-- Picker wrapper: one @click.outside for input + dropdown together --}}
                        <div style="position:relative;" @click.outside="showList=false">
                            <div style="position:relative;">
                                <input type="text" x-model="search"
                                       @focus="showList=true"
                                       @input="showList=true"
                                       :placeholder="'Search ' + tabLabel().toLowerCase() + ' by name, phone, code...'"
                                       class="w-full px-4 py-2.5 pl-10 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <i class="fas fa-search text-gray-400"
                                   style="position:absolute;left:12px;top:50%;transform:translateY(-50%);pointer-events:none;"></i>
                                <button type="button" x-show="targetId || search" @click="clearTarget()"
                                        class="text-gray-400 hover:text-rose-500"
                                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>

                            {{-- list (absolute so it overlaps below) --}}
                            <div x-show="showList && filtered().length"
                                 x-cloak
                                 class="z-50 mt-1 border border-gray-200 rounded-lg max-h-64 overflow-y-auto bg-white shadow-lg"
                                 style="position:absolute;left:0;right:0;z-index:50;">
                                <template x-for="item in filtered()" :key="item.id">
                                    <button type="button" @click="pick(item)"
                                            class="w-full text-left px-4 py-2.5 hover:bg-blue-50 border-b border-gray-100 last:border-0 flex items-center justify-between"
                                            :class="targetId==item.id ? 'bg-blue-50' : ''">
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></div>
                                            <div class="text-[11px] text-gray-500 truncate" x-text="itemSubline(item)"></div>
                                        </div>
                                        <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                                    </button>
                                </template>
                            </div>
                            <p x-show="showList && !filtered().length" x-cloak
                               class="z-50 mt-1 border border-gray-200 rounded-lg bg-white shadow-lg px-4 py-3 text-xs text-gray-500 italic"
                               style="position:absolute;left:0;right:0;z-index:50;">
                                No matches.
                            </p>
                        </div>

                        {{-- selected pill --}}
                        <template x-if="selected">
                            <div class="mt-3 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                                <div>
                                    <div class="text-sm font-semibold text-blue-900" x-text="selected.name"></div>
                                    <div class="text-[11px] text-blue-700" x-text="selectedSubline()"></div>
                                </div>
                                <span class="text-[10px] uppercase font-bold text-blue-600 bg-blue-100 px-2 py-1 rounded">Selected</span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ── Step 3: Details ── --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Step 3 — Details</h2>
                    </div>
                    <div class="p-4 sm:p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Amount (Rs.) <span class="text-rose-500">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" required x-model="amount"
                                   placeholder="0.00"
                                   class="w-full px-4 py-3 text-2xl font-bold tracking-tight border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   :class="direction==='in' ? 'text-emerald-700' : 'text-rose-700'">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Date <span class="text-rose-500">*</span></label>
                            <input type="date" name="transaction_date" required x-model="date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Payment Method <span class="text-rose-500">*</span></label>
                            <select name="payment_method" x-model="paymentMethod" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach ($paymentMethods as $pm)
                                    <option value="{{ $pm->name }}">{{ $pm->label }}</option>
                                @endforeach
                                @if ($paymentMethods->isEmpty())
                                    <option value="cash">Cash</option>
                                @endif
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Notes</label>
                            <textarea name="notes" x-model="notes" rows="2" placeholder="Optional notes..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════ RIGHT — SUMMARY ═══════════════════════ --}}
            <div class="lg:col-span-1">
                <div class="sticky top-4">
                    <div class="rounded-xl shadow-sm border-2 overflow-hidden"
                         :class="direction==='in' ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/30'">
                        <div class="px-5 py-3 border-b"
                             :class="direction==='in' ? 'border-emerald-200 bg-emerald-100/60' : 'border-rose-200 bg-rose-100/60'">
                            <h2 class="text-sm font-semibold uppercase tracking-wide flex items-center gap-2"
                                :class="direction==='in' ? 'text-emerald-800' : 'text-rose-800'">
                                <i class="fas" :class="direction==='in' ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                                <span x-text="direction==='in' ? 'Cash In Summary' : 'Cash Out Summary'"></span>
                            </h2>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Type</span>
                                <span class="font-semibold capitalize" x-text="targetType"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Account</span>
                                <span class="font-semibold text-right" x-text="selected ? selected.name : '—'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Date</span>
                                <span class="font-semibold" x-text="date || '—'"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Method</span>
                                <span class="font-semibold capitalize" x-text="paymentMethod"></span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between items-end">
                                <span class="text-xs text-gray-500 uppercase">Amount</span>
                                <span class="text-3xl font-extrabold tracking-tight"
                                      :class="direction==='in' ? 'text-emerald-700' : 'text-rose-700'"
                                      x-text="'Rs. ' + formatAmount()"></span>
                            </div>
                            <p class="text-[11px] text-gray-500 italic" x-text="explanation()"></p>

                            <button type="submit"
                                    :disabled="!canSubmit()"
                                    :class="canSubmit() ? (direction==='in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700') : 'bg-gray-300 cursor-not-allowed'"
                                    class="w-full mt-3 py-3 text-white font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                <span x-text="direction==='in' ? 'Record Cash In' : 'Record Cash Out'"></span>
                            </button>
                        </div>
                    </div>

                    {{-- ── Selected entity details ── --}}
                    <template x-if="selected">
                        <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                                <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                                    <i class="fas" :class="targetType==='customer' ? 'fa-user' : (targetType==='supplier' ? 'fa-truck' : 'fa-book')"></i>
                                    <span x-text="tabLabel() + ' Details'"></span>
                                </h3>
                                <a x-show="targetType==='customer'" :href="`/admin/customers/${selected.id}/khata`" target="_blank"
                                   class="text-[10px] font-semibold text-blue-600 hover:underline">View Khata <i class="fas fa-external-link-alt"></i></a>
                                <a x-show="targetType==='supplier'" :href="`/suppliers/${selected.id}/ledger`" target="_blank"
                                   class="text-[10px] font-semibold text-blue-600 hover:underline">View Ledger <i class="fas fa-external-link-alt"></i></a>
                                <a x-show="targetType==='ledger'" :href="`/admin/ledger-accounts/${selected.id}`" target="_blank"
                                   class="text-[10px] font-semibold text-blue-600 hover:underline">View Account <i class="fas fa-external-link-alt"></i></a>
                            </div>
                            <div class="p-4 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Name</span>
                                    <span class="font-semibold text-gray-800 text-right" x-text="selected.name"></span>
                                </div>

                                {{-- Customer specific --}}
                                <template x-if="targetType==='customer'">
                                    <div class="space-y-2">
                                        <div class="flex justify-between" x-show="selected.phone">
                                            <span class="text-gray-500">Phone</span>
                                            <span class="font-mono text-gray-700" x-text="selected.phone"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Type</span>
                                            <span class="capitalize font-semibold text-gray-700" x-text="selected.type || 'normal'"></span>
                                        </div>
                                        <div class="flex justify-between" x-show="selected.credit_enabled">
                                            <span class="text-gray-500">Credit Limit</span>
                                            <span class="font-semibold text-gray-700" x-text="'Rs. ' + fmt(selected.credit_limit)"></span>
                                        </div>
                                        <hr class="border-gray-100">
                                        <div class="flex justify-between items-center pt-1">
                                            <span class="text-gray-500 text-[11px] uppercase font-semibold">
                                                Previous Khata
                                            </span>
                                            <span class="text-base font-extrabold"
                                                  :class="selected.balance > 0 ? 'text-rose-700' : (selected.balance < 0 ? 'text-emerald-700' : 'text-gray-500')"
                                                  x-text="'Rs. ' + fmt(Math.abs(selected.balance))"></span>
                                        </div>
                                        <p class="text-[10px] italic"
                                           :class="selected.balance > 0 ? 'text-rose-600' : (selected.balance < 0 ? 'text-emerald-600' : 'text-gray-400')"
                                           x-text="selected.balance > 0 ? 'Customer owes you (واپس لینا)' : (selected.balance < 0 ? 'Advance with you (واپسی)' : 'Account settled')"></p>

                                        {{-- After this transaction --}}
                                        <template x-if="parseFloat(amount) > 0">
                                            <div class="mt-3 p-2.5 rounded-lg border-2 border-dashed"
                                                 :class="direction==='in' ? 'border-emerald-300 bg-emerald-50/40' : 'border-rose-300 bg-rose-50/40'">
                                                <div class="text-[10px] uppercase font-semibold tracking-wide text-gray-500 mb-1">After this entry</div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-[11px] text-gray-600">New khata</span>
                                                    <span class="font-bold text-sm"
                                                          :class="newCustomerBalance() > 0 ? 'text-rose-700' : (newCustomerBalance() < 0 ? 'text-emerald-700' : 'text-gray-700')"
                                                          x-text="'Rs. ' + fmt(Math.abs(newCustomerBalance())) + (newCustomerBalance() > 0 ? ' (Due)' : (newCustomerBalance() < 0 ? ' (Advance)' : ' (Settled)'))"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Supplier specific --}}
                                <template x-if="targetType==='supplier'">
                                    <div class="space-y-2">
                                        <div class="flex justify-between" x-show="selected.phone">
                                            <span class="text-gray-500">Phone</span>
                                            <span class="font-mono text-gray-700" x-text="selected.phone"></span>
                                        </div>
                                        <div class="flex justify-between" x-show="selected.company_name">
                                            <span class="text-gray-500">Company</span>
                                            <span class="font-semibold text-gray-700 text-right" x-text="selected.company_name"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Total Purchases</span>
                                            <span class="font-semibold text-gray-700" x-text="'Rs. ' + fmt(selected.total_purchased)"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Total Paid</span>
                                            <span class="font-semibold text-gray-700" x-text="'Rs. ' + fmt(selected.total_paid)"></span>
                                        </div>
                                        <hr class="border-gray-100">
                                        <div class="flex justify-between items-center pt-1">
                                            <span class="text-gray-500 text-[11px] uppercase font-semibold">Outstanding</span>
                                            <span class="text-base font-extrabold"
                                                  :class="selected.balance > 0 ? 'text-rose-700' : (selected.balance < 0 ? 'text-emerald-700' : 'text-gray-500')"
                                                  x-text="'Rs. ' + fmt(Math.abs(selected.balance))"></span>
                                        </div>
                                        <p class="text-[10px] italic"
                                           :class="selected.balance > 0 ? 'text-rose-600' : (selected.balance < 0 ? 'text-emerald-600' : 'text-gray-400')"
                                           x-text="selected.balance > 0 ? 'You owe supplier' : (selected.balance < 0 ? 'Supplier owes you' : 'Settled')"></p>

                                        <template x-if="parseFloat(amount) > 0">
                                            <div class="mt-3 p-2.5 rounded-lg border-2 border-dashed"
                                                 :class="direction==='out' ? 'border-emerald-300 bg-emerald-50/40' : 'border-rose-300 bg-rose-50/40'">
                                                <div class="text-[10px] uppercase font-semibold tracking-wide text-gray-500 mb-1">After this entry</div>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-[11px] text-gray-600">New outstanding</span>
                                                    <span class="font-bold text-sm"
                                                          :class="newSupplierBalance() > 0 ? 'text-rose-700' : (newSupplierBalance() < 0 ? 'text-emerald-700' : 'text-gray-700')"
                                                          x-text="'Rs. ' + fmt(Math.abs(newSupplierBalance()))"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Ledger account specific --}}
                                <template x-if="targetType==='ledger'">
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Code</span>
                                            <span class="font-mono text-gray-700" x-text="selected.code"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Type</span>
                                            <span class="capitalize font-semibold text-gray-700" x-text="selected.type"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Total Debit</span>
                                            <span class="text-gray-700" x-text="'Rs. ' + fmt(selected.total_debit)"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">Total Credit</span>
                                            <span class="text-gray-700" x-text="'Rs. ' + fmt(selected.total_credit)"></span>
                                        </div>
                                        <hr class="border-gray-100">
                                        <div class="flex justify-between items-center pt-1">
                                            <span class="text-gray-500 text-[11px] uppercase font-semibold">Current Balance</span>
                                            <span class="text-base font-extrabold text-blue-700" x-text="'Rs. ' + fmt(selected.balance)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="mt-4 bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-[11px] text-gray-500 leading-relaxed">
                        <div class="font-semibold text-gray-700 mb-1.5"><i class="fas fa-info-circle text-blue-500"></i> How it works</div>
                        Every cash movement also posts a matching entry to the <span class="font-semibold">Cash</span> ledger account so your cash drawer balance stays accurate.
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function cashForm(initial) {
    return {
        // data
        customers: initial.customers,
        suppliers: initial.suppliers,
        ledgerAccounts: initial.ledgerAccounts,

        // state
        direction: 'in',           // in | out
        targetType: 'customer',    // customer | supplier | ledger
        targetId: '',
        search: '',
        showList: true,
        amount: '',
        date: initial.defaultDate,
        paymentMethod: 'cash',
        notes: '',

        get pool() {
            if (this.targetType === 'customer') return this.customers;
            if (this.targetType === 'supplier') return this.suppliers;
            return this.ledgerAccounts;
        },

        get selected() {
            if (!this.targetId) return null;
            return this.pool.find(x => x.id == this.targetId) || null;
        },

        tabLabel() {
            return { customer: 'Customer', supplier: 'Supplier', ledger: 'Ledger Account' }[this.targetType];
        },

        setType(t) {
            this.targetType = t;
            this.targetId = '';
            this.search = '';
            this.showList = true;
        },

        clearTarget() {
            this.targetId = '';
            this.search = '';
        },

        pick(item) {
            this.targetId = item.id;
            this.search = item.name;
            this.showList = false;
        },

        filtered() {
            const q = (this.search || '').toLowerCase().trim();
            const list = this.pool;
            if (!q) return list.slice(0, 30);
            return list.filter(x =>
                (x.name || '').toLowerCase().includes(q)
                || (x.phone || '').toLowerCase().includes(q)
                || (x.code  || '').toLowerCase().includes(q)
                || (x.type  || '').toLowerCase().includes(q)
            ).slice(0, 30);
        },

        fmt(n) {
            return parseFloat(n || 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        },

        itemSubline(item) {
            if (!item) return '';
            if (this.targetType === 'customer') {
                const bal = (item.balance ?? 0);
                const tag = bal > 0 ? `Due Rs. ${this.fmt(bal)}`
                          : bal < 0 ? `Advance Rs. ${this.fmt(Math.abs(bal))}`
                          : 'Settled';
                return [item.phone, item.type, tag].filter(Boolean).join(' · ');
            }
            if (this.targetType === 'supplier') {
                const bal = (item.balance ?? 0);
                const tag = bal > 0 ? `Owe Rs. ${this.fmt(bal)}`
                          : bal < 0 ? `Adv Rs. ${this.fmt(Math.abs(bal))}`
                          : 'Settled';
                return [item.phone, item.company_name, tag].filter(Boolean).join(' · ');
            }
            return [item.code, (item.type || '').toUpperCase(), `Bal Rs. ${this.fmt(item.balance)}`].filter(Boolean).join(' · ');
        },

        selectedSubline() {
            return this.itemSubline(this.selected);
        },

        // Customer balance: positive = customer owes us. Cash In reduces it; Cash Out increases it.
        newCustomerBalance() {
            if (!this.selected || this.targetType !== 'customer') return 0;
            const amt = parseFloat(this.amount || 0);
            return (this.selected.balance || 0) - (this.direction === 'in' ? amt : -amt);
        },

        // Supplier balance: positive = we owe supplier. Cash Out reduces it; Cash In (refund) increases it.
        newSupplierBalance() {
            if (!this.selected || this.targetType !== 'supplier') return 0;
            const amt = parseFloat(this.amount || 0);
            return (this.selected.balance || 0) - (this.direction === 'out' ? amt : -amt);
        },

        explanation() {
            if (!this.selected) return 'Pick an account to continue.';
            const name = this.selected.name;
            if (this.targetType === 'customer') {
                return this.direction === 'in'
                    ? `Customer ${name} pays you. Their khata balance decreases.`
                    : `You give cash to ${name}. Their khata balance increases.`;
            }
            if (this.targetType === 'supplier') {
                return this.direction === 'out'
                    ? `You pay supplier ${name}. What you owe decreases.`
                    : `Supplier ${name} refunds you. What you owe increases.`;
            }
            return this.direction === 'in'
                ? `Records income/credit to ${name}.`
                : `Records expense/debit on ${name}.`;
        },

        formatAmount() {
            const n = parseFloat(this.amount || 0);
            return n.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
        },

        canSubmit() {
            return this.targetId && parseFloat(this.amount) > 0 && this.date && this.paymentMethod;
        },

        onSubmit(e) {
            if (!this.canSubmit()) {
                e.preventDefault();
                alert('Please complete all required fields.');
            }
        },
    };
}
</script>
@endpush

@endsection

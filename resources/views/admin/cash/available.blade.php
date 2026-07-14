@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Available Cash / دستیاب رقم</h1>
            <p class="text-sm text-gray-500 mt-1">Balance per payment account — money received minus money paid out</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.cash.index') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-exchange-alt mr-1"></i> Record Transaction
            </a>
            <a href="{{ route('admin.cash.history') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-history mr-1"></i> History
            </a>
        </div>
    </div>

    {{-- Grand total banner --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div style="background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;" class="rounded-xl p-5 shadow">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;opacity:.85;">Total Received (آمد)</p>
            <p style="font-size:1.875rem;font-weight:900;margin-top:4px;">Rs. {{ number_format($totalIn, 0) }}</p>
        </div>
        <div style="background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;" class="rounded-xl p-5 shadow">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;opacity:.85;">Total Paid Out (ادائیگی)</p>
            <p style="font-size:1.875rem;font-weight:900;margin-top:4px;">Rs. {{ number_format($totalOut, 0) }}</p>
        </div>
        <div style="background:linear-gradient(135deg,{{ $totalBal >= 0 ? '#2563eb,#1d4ed8' : '#ea580c,#c2410c' }});color:#fff;" class="rounded-xl p-5 shadow">
            <p style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;opacity:.85;">Net Balance (بقیہ)</p>
            <p style="font-size:1.875rem;font-weight:900;margin-top:4px;">Rs. {{ number_format(abs($totalBal), 0) }}</p>
            @if($totalBal < 0)
            <p style="font-size:11px;opacity:.85;margin-top:4px;">⚠️ More paid out than received</p>
            @endif
        </div>
    </div>

    {{-- Per-account breakdown --}}
    @if($summary->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-400">
        <i class="fas fa-wallet text-4xl mb-3 block opacity-30"></i>
        No transactions recorded yet.
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="font-semibold text-gray-800">Balance by Account</h3>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($summary as $acc)
            @php $bal = $acc['balance']; @endphp
            <div class="px-6 py-4 flex items-center gap-4">
                <div class="text-2xl w-10 text-center flex-shrink-0">{{ $acc['icon'] }}</div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">{{ $acc['label'] }}</p>
                    <div class="flex gap-4 mt-1 text-xs text-gray-500">
                        <span class="text-green-600">↑ Received: Rs. {{ number_format($acc['in'], 0) }}</span>
                        <span class="text-red-600">↓ Paid Out: Rs. {{ number_format($acc['out'], 0) }}</span>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xl font-black {{ $bal >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                        Rs. {{ number_format(abs($bal), 0) }}
                    </p>
                    <p class="text-xs {{ $bal >= 0 ? 'text-green-600' : 'text-red-500' }}">
                        {{ $bal >= 0 ? 'Available' : 'Deficit' }}
                    </p>
                </div>
                {{-- Mini bar --}}
                <div class="w-24 hidden sm:block">
                    @php
                        $max = max($acc['in'], $acc['out'], 1);
                        $inPct = min(100, round($acc['in'] / $max * 100));
                        $outPct = min(100, round($acc['out'] / $max * 100));
                    @endphp
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden mb-1">
                        <div class="h-full bg-green-400 rounded-full" style="width:{{ $inPct }}%"></div>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-red-400 rounded-full" style="width:{{ $outPct }}%"></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-6 py-3 bg-gray-50 border-t flex justify-between text-sm font-bold">
            <span class="text-gray-600">Net Balance (all accounts)</span>
            <span class="{{ $totalBal >= 0 ? 'text-blue-700' : 'text-red-600' }}">
                Rs. {{ number_format(abs($totalBal), 0) }}
                {{ $totalBal < 0 ? '(deficit)' : '' }}
            </span>
        </div>
    </div>
    @endif

    {{-- Flash --}}
    @if(session('success'))
    <div style="background:#f0fdf4;border:2px solid #22c55e;border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <i class="fas fa-check-circle" style="color:#16a34a;font-size:18px;"></i>
        <span style="font-size:14px;font-weight:600;color:#166534;">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Balance Transfer --}}
    <div class="bg-white rounded-xl shadow-sm border border-blue-100 overflow-hidden mb-6">
        <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
            <h3 class="font-semibold text-blue-800 text-sm">
                <i class="fas fa-exchange-alt mr-2"></i>Transfer Between Accounts (اکاؤنٹ ٹرانسفر)
            </h3>
            <button onclick="document.getElementById('transfer-form').classList.toggle('hidden')"
                class="text-xs text-blue-600 hover:underline">Show / Hide</button>
        </div>
        <div id="transfer-form" class="hidden p-5">
            <form action="{{ route('admin.cash.transfer') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">From Account (سے)</label>
                        <select name="from_account" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($paymentMethods as $pm)
                                @php $bal = collect($summary)->firstWhere('method', $pm->name)['balance'] ?? 0; @endphp
                                <option value="{{ $pm->name }}">
                                    {{ $pm->label }} (Rs. {{ number_format($bal, 0) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">To Account (کو)</label>
                        <select name="to_account" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->name }}">{{ $pm->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Amount (رقم) Rs.</label>
                        <input type="number" name="amount" min="1" step="0.01" required placeholder="0"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Date (تاریخ)</label>
                        <input type="date" name="transferred_at" value="{{ date('Y-m-d') }}" required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div class="mt-3 flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Note (optional)</label>
                        <input type="text" name="note" placeholder="e.g. Cash deposited to bank..."
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700">
                        <i class="fas fa-exchange-alt mr-1"></i> Record Transfer
                    </button>
                </div>
            </form>
        </div>

        {{-- Recent transfers --}}
        @if($recentTransfers->isNotEmpty())
        <div class="border-t border-blue-100 px-5 py-3">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Recent Transfers</p>
            <div class="space-y-1">
                @foreach($recentTransfers as $t)
                <div class="flex justify-between items-center text-xs text-gray-600 py-1 border-b border-gray-50">
                    <span>
                        <span class="font-semibold text-red-600">{{ ucfirst($t->from_account) }}</span>
                        <i class="fas fa-arrow-right mx-1 text-gray-400"></i>
                        <span class="font-semibold text-green-600">{{ ucfirst($t->to_account) }}</span>
                        @if($t->note) <span class="text-gray-400 ml-1">— {{ $t->note }}</span> @endif
                    </span>
                    <span class="flex items-center gap-3">
                        <span class="font-bold text-gray-700">Rs. {{ number_format($t->amount, 0) }}</span>
                        <span class="text-gray-400">{{ $t->transferred_at->format('d M Y') }}</span>
                        <span class="text-gray-400">{{ $t->user?->name ?? 'Staff' }}</span>
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Receivables & Payables context --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-red-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-500 uppercase tracking-wide">Outstanding Receivables</p>
                    <p class="text-sm text-gray-500 mt-0.5">Money customers still owe us</p>
                </div>
                <p class="text-2xl font-black text-red-600">Rs. {{ number_format($totalReceivables, 0) }}</p>
            </div>
            <a href="{{ route('admin.receivables') }}"
                class="mt-3 block text-xs text-blue-500 hover:underline">View details →</a>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-orange-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-orange-500 uppercase tracking-wide">Outstanding Payables</p>
                    <p class="text-sm text-gray-500 mt-0.5">Money we still owe suppliers</p>
                </div>
                <p class="text-2xl font-black text-orange-600">Rs. {{ number_format($totalPayables, 0) }}</p>
            </div>
            <a href="{{ route('purchases.index') }}"
                class="mt-3 block text-xs text-blue-500 hover:underline">View purchases →</a>
        </div>
    </div>

    {{-- Note --}}
    <div class="mt-4 text-xs text-gray-400 bg-gray-50 rounded-lg px-4 py-3">
        <strong>How this is calculated:</strong>
        Received = POS sales paid + customer khata payments + supplier refunds received.
        Paid Out = Purchase payments + supplier payments + customer refunds/payouts.
        Figures are computed from actual transaction records, grouped by payment method.
    </div>

</div>
@endsection

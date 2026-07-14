<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountTransfer;
use App\Models\Customer;
use App\Models\LedgerAccount;
use App\Models\LedgerAccountEntry;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashTransactionController extends Controller
{
    use BranchScoped;

    public function index()
    {
        $customers = $this->scopeBranch(Customer::query())
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'customer_type', 'current_balance', 'credit_limit', 'credit_enabled']);

        // Suppliers with derived balance (what we owe them).
        // Out-direction unlinked payments reduce outstanding; in-direction (refunds) increase it.
        $suppliers = $this->scopeBranch(Supplier::query())
            ->withSum('purchases as total_purchased', 'total_amount')
            ->withSum('purchases as total_paid_on_purchases', 'paid_amount')
            ->withSum(['payments as total_paid_unlinked' => fn ($q) =>
                $q->whereNull('purchase_id')->where('direction', 'out')
            ], 'amount')
            ->withSum(['payments as total_received_back' => fn ($q) =>
                $q->whereNull('purchase_id')->where('direction', 'in')
            ], 'amount')
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'company_name'])
            ->map(function ($s) {
                $purchased   = (float) ($s->total_purchased ?? 0);
                $paidLinked  = (float) ($s->total_paid_on_purchases ?? 0);
                $paidUnlinked= (float) ($s->total_paid_unlinked ?? 0);
                $received    = (float) ($s->total_received_back ?? 0);

                // We owe = purchases - what we've paid + refunds we've taken back
                $balance = $purchased - $paidLinked - $paidUnlinked + $received;

                return [
                    'id'             => $s->id,
                    'name'           => $s->name,
                    'phone'          => $s->phone,
                    'company_name'   => $s->company_name,
                    'total_purchased'=> $purchased,
                    'total_paid'     => $paidLinked + $paidUnlinked,
                    'balance'        => $balance,        // > 0 = we owe supplier
                ];
            });

        // Ledger accounts with derived balance
        $ledgerAccounts = LedgerAccount::where('is_active', true)
            ->where('name', '!=', 'Cash')
            ->withSum('entries as total_debit',  'debit')
            ->withSum('entries as total_credit', 'credit')
            ->orderBy('type')->orderBy('name')
            ->get(['id', 'account_code', 'name', 'type', 'opening_balance'])
            ->map(function ($a) {
                $debit  = (float) ($a->total_debit  ?? 0);
                $credit = (float) ($a->total_credit ?? 0);
                $balance = in_array($a->type, [LedgerAccount::TYPE_INCOME, LedgerAccount::TYPE_LIABILITY])
                    ? (float) $a->opening_balance + $credit - $debit
                    : (float) $a->opening_balance + $debit  - $credit;
                return [
                    'id'           => $a->id,
                    'name'         => $a->name,
                    'code'         => $a->account_code,
                    'type'         => $a->type,
                    'total_debit'  => $debit,
                    'total_credit' => $credit,
                    'balance'      => $balance,
                ];
            });

        $paymentMethods = PaymentMethod::active()->get(['id', 'name', 'label']);

        return view('admin.cash.index', compact(
            'customers', 'suppliers', 'ledgerAccounts', 'paymentMethods'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'direction'         => 'required|in:in,out',
            'target_type'       => 'required|in:customer,supplier,ledger',
            'target_id'         => 'required|integer',
            'amount'            => 'required|numeric|min:0.01',
            'transaction_date'  => 'required|date',
            'payment_method'    => 'required|string|max:50',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $direction = $validated['direction'];                  // 'in' | 'out'
        $amount    = round((float) $validated['amount'], 2);
        $sign      = $direction === 'in' ? 1 : -1;             // for signed amount on payments

        DB::beginTransaction();
        try {
            $cashAccount = $this->getCashAccount();

            switch ($validated['target_type']) {
                case 'customer':
                    $reference = $this->postCustomer($validated, $direction, $amount, $sign);
                    break;
                case 'supplier':
                    $reference = $this->postSupplier($validated, $direction, $amount, $sign);
                    break;
                case 'ledger':
                    $reference = $this->postLedger($validated, $direction, $amount);
                    break;
                default:
                    throw new \RuntimeException('Unknown target type.');
            }

            // Counter-entry on Cash account (always, for double-entry integrity)
            LedgerAccountEntry::create([
                'entry_number'      => LedgerAccountEntry::generateEntryNumber(),
                'ledger_account_id' => $cashAccount->id,
                'entry_date'        => $validated['transaction_date'],
                'description'       => $reference['description'],
                'debit'             => $direction === 'in'  ? $amount : 0,
                'credit'            => $direction === 'out' ? $amount : 0,
                'reference_type'    => 'cash_' . $validated['target_type'],
                'reference_id'      => $reference['id'],
                'reference_number'  => $reference['number'],
                'payment_method'    => $validated['payment_method'],
                'notes'             => $validated['notes'] ?? null,
                'created_by'        => auth()->id(),
            ]);

            DB::commit();

            $verb = $direction === 'in' ? 'received' : 'paid';
            return redirect()->route('admin.cash.index')
                ->with('success', 'Rs. ' . number_format($amount, 2) . " {$verb} successfully. Ref: {$reference['number']}");

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function history(Request $request)
    {
        $from        = $request->input('from');
        $to          = $request->input('to');
        $direction   = $request->input('direction');   // in|out|null
        $targetType  = $request->input('target_type'); // customer|supplier|ledger|null

        $rows = collect();

        // Customer payments (khata + khata_payout)
        if (!$targetType || $targetType === 'customer') {
            $q = Payment::with('customer')->whereIn('payment_type', ['khata', 'khata_payout']);
            if ($from) $q->whereDate('payment_date', '>=', $from);
            if ($to)   $q->whereDate('payment_date', '<=', $to);
            $branchId = $this->branchId();
            if ($branchId && $branchId !== 'all') {
                $q->whereHas('customer', fn ($c) => $c->where('branch_id', $branchId));
            }
            foreach ($q->get() as $p) {
                $dir = $p->payment_type === 'khata_payout' ? 'out' : 'in';
                $rows->push([
                    'id'             => 'C-' . $p->id,
                    'date'           => $p->payment_date,
                    'direction'      => $dir,
                    'target_type'    => 'customer',
                    'target_label'   => $p->customer?->name ?? 'Customer',
                    'amount'         => abs((float) $p->amount),
                    'payment_method' => $p->payment_method,
                    'reference'      => $p->payment_number,
                    'notes'          => $p->notes,
                ]);
            }
        }

        // Supplier payments (use `direction` column)
        if (!$targetType || $targetType === 'supplier') {
            $q = SupplierPayment::with('supplier');
            if ($from) $q->whereDate('payment_date', '>=', $from);
            if ($to)   $q->whereDate('payment_date', '<=', $to);
            $branchId = $this->branchId();
            if ($branchId && $branchId !== 'all') {
                $q->where('branch_id', $branchId);
            }
            foreach ($q->get() as $p) {
                $dir = ($p->direction ?? 'out') === 'in' ? 'in' : 'out';
                $rows->push([
                    'id'             => 'S-' . $p->id,
                    'date'           => $p->payment_date,
                    'direction'      => $dir,
                    'target_type'    => 'supplier',
                    'target_label'   => $p->supplier?->name ?? 'Supplier',
                    'amount'         => abs((float) $p->amount),
                    'payment_method' => $p->payment_method,
                    'reference'      => $p->payment_number,
                    'notes'          => $p->notes,
                ]);
            }
        }

        // Ledger entries created via this feature (reference_type starts with cash_ledger)
        if (!$targetType || $targetType === 'ledger') {
            $q = LedgerAccountEntry::with('ledgerAccount')
                ->where('reference_type', 'cash_ledger');
            if ($from) $q->whereDate('entry_date', '>=', $from);
            if ($to)   $q->whereDate('entry_date', '<=', $to);
            foreach ($q->get() as $e) {
                $dir = $e->credit > 0 ? 'in' : 'out';
                $rows->push([
                    'id'             => 'L-' . $e->id,
                    'date'           => $e->entry_date,
                    'direction'      => $dir,
                    'target_type'    => 'ledger',
                    'target_label'   => $e->ledgerAccount?->name ?? 'Ledger',
                    'amount'         => (float) ($e->debit ?: $e->credit),
                    'payment_method' => $e->payment_method,
                    'reference'      => $e->entry_number,
                    'notes'          => $e->notes,
                ]);
            }
        }

        if ($direction) {
            $rows = $rows->where('direction', $direction)->values();
        }

        $rows = $rows->sortByDesc(fn ($r) => $r['date'])->values();

        $totalIn  = $rows->where('direction', 'in')->sum('amount');
        $totalOut = $rows->where('direction', 'out')->sum('amount');

        return view('admin.cash.history', [
            'rows'       => $rows,
            'totalIn'    => $totalIn,
            'totalOut'   => $totalOut,
            'net'        => $totalIn - $totalOut,
            'filters'    => compact('from', 'to', 'direction', 'targetType'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Internal: per-target persistence
    // ──────────────────────────────────────────────────────────────────────────

    private function postCustomer(array $v, string $direction, float $amount, int $sign): array
    {
        $customer = Customer::findOrFail($v['target_id']);

        // Match per-entity convention: khata (cash in) vs khata_payout (cash out), amounts always positive.
        $payment = Payment::create([
            'payment_number'   => Payment::generatePaymentNumber(),
            'payment_type'     => $direction === 'in' ? 'khata' : 'khata_payout',
            'order_id'         => null,
            'customer_id'      => $customer->id,
            'amount'           => $amount,
            'payment_date'     => $v['transaction_date'],
            'payment_method'   => $v['payment_method'],
            'reference_number' => 'CASH-' . strtoupper(uniqid()),
            'notes'            => $v['notes'] ?? null,
            'status'           => 'completed',
            'created_by'       => auth()->id(),
        ]);

        // Cash In  → customer paid us, balance decreases.
        // Cash Out → we paid customer, balance increases (or advance reduces).
        $delta = $direction === 'in' ? -$amount : +$amount;
        $customer->update([
            'current_balance' => (float) ($customer->current_balance ?? 0) + $delta,
        ]);

        $verb = $direction === 'in' ? 'received from' : 'paid to';
        return [
            'id'          => $payment->id,
            'number'      => $payment->payment_number,
            'description' => "Cash {$verb} customer: {$customer->name}",
        ];
    }

    private function postSupplier(array $v, string $direction, float $amount, int $sign): array
    {
        $supplier = Supplier::findOrFail($v['target_id']);

        // New convention: amounts always positive, `direction` column distinguishes.
        $branchId = $this->branchId();
        $payment = SupplierPayment::create([
            'payment_number' => SupplierPayment::generatePaymentNumber(),
            'supplier_id'    => $supplier->id,
            'purchase_id'    => null,
            'branch_id'      => $branchId && $branchId !== 'all' ? $branchId : null,
            'amount'         => $amount,
            'payment_date'   => $v['transaction_date'],
            'payment_method' => $v['payment_method'],
            'direction'      => $direction,
            'notes'          => $v['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        $verb = $direction === 'in' ? 'received from' : 'paid to';
        return [
            'id'          => $payment->id,
            'number'      => $payment->payment_number,
            'description' => "Cash {$verb} supplier: {$supplier->name}",
        ];
    }

    private function postLedger(array $v, string $direction, float $amount): array
    {
        $account = LedgerAccount::findOrFail($v['target_id']);

        if ($account->name === 'Cash') {
            throw new \RuntimeException('Cannot transact directly against the Cash account.');
        }

        // Cash In  → credit the chosen account (debit on Cash counter-entry)
        // Cash Out → debit  the chosen account (credit on Cash counter-entry)
        $entry = LedgerAccountEntry::create([
            'entry_number'      => LedgerAccountEntry::generateEntryNumber(),
            'ledger_account_id' => $account->id,
            'entry_date'        => $v['transaction_date'],
            'description'       => 'Cash ' . ($direction === 'in' ? 'received' : 'paid') . ' — ' . $account->name,
            'debit'             => $direction === 'out' ? $amount : 0,
            'credit'            => $direction === 'in'  ? $amount : 0,
            'reference_type'    => 'cash_ledger',
            'reference_number'  => 'CASH-' . strtoupper(uniqid()),
            'payment_method'    => $v['payment_method'],
            'notes'             => $v['notes'] ?? null,
            'created_by'        => auth()->id(),
        ]);

        return [
            'id'          => $entry->id,
            'number'      => $entry->entry_number,
            'description' => $entry->description,
        ];
    }

    // ── Available Cash Summary ────────────────────────────────────────────────
    public function availableCash()
    {
        $branchId = $this->branchId();
        $isAll = $this->isAllBranches();

        $accounts = [
            'cash'         => ['label' => 'Cash (نقد)',          'icon' => '💵', 'in' => 0, 'out' => 0],
            'bank'         => ['label' => 'Bank Transfer (بینک)',  'icon' => '🏦', 'in' => 0, 'out' => 0],
            'mobile_money' => ['label' => 'Mobile Money (موبائل)', 'icon' => '📱', 'in' => 0, 'out' => 0],
            'card'         => ['label' => 'Card (کارڈ)',           'icon' => '💳', 'in' => 0, 'out' => 0],
            'cheque'       => ['label' => 'Cheque (چیک)',          'icon' => '📄', 'in' => 0, 'out' => 0],
            'credit'       => ['label' => 'Credit / Khata (ادھار)', 'icon' => '📋', 'in' => 0, 'out' => 0],
        ];

        // ── Cash IN ────────────────────────────────────────────────────────

        // 1. POS order payments received (paid_amount)
        $ordersQ = Order::whereNotIn('status', ['cancelled'])
            ->whereRaw('paid_amount > 0');
        if (!$isAll) $ordersQ->where('branch_id', $branchId);
        foreach ($ordersQ->get(['payment_method', 'paid_amount']) as $o) {
            $method = strtolower($o->payment_method ?? 'cash');
            $key = $this->normaliseMethod($method);
            if (isset($accounts[$key])) $accounts[$key]['in'] += (float) $o->paid_amount;
        }

        // 2. Customer khata payments received (money we got from customers)
        $custPayQ = Payment::where('payment_type', 'khata')->whereRaw('amount > 0');
        if (!$isAll) {
            $custPayQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
        }
        foreach ($custPayQ->get(['payment_method', 'amount']) as $p) {
            $key = $this->normaliseMethod($p->payment_method ?? 'cash');
            if (isset($accounts[$key])) $accounts[$key]['in'] += (float) $p->amount;
        }

        // 3. Supplier payments received back (in-direction)
        $supInQ = SupplierPayment::where('direction', 'in')->whereRaw('amount > 0');
        if (!$isAll) $supInQ->where('branch_id', $branchId);
        foreach ($supInQ->get(['payment_method', 'amount']) as $p) {
            $key = $this->normaliseMethod($p->payment_method ?? 'cash');
            if (isset($accounts[$key])) $accounts[$key]['in'] += (float) $p->amount;
        }

        // ── Cash OUT ───────────────────────────────────────────────────────

        // 4. Purchase payments made (paid_amount) — now have payment_method
        $purchQ = Purchase::whereRaw('paid_amount > 0');
        if (!$isAll) $purchQ->where('branch_id', $branchId);
        foreach ($purchQ->get(['payment_method', 'paid_amount']) as $p) {
            $key = $this->normaliseMethod($p->payment_method ?? 'cash');
            if (isset($accounts[$key])) $accounts[$key]['out'] += (float) $p->paid_amount;
        }

        // 5. Supplier payments out (we paid suppliers)
        $supOutQ = SupplierPayment::where('direction', 'out')->whereRaw('amount > 0');
        if (!$isAll) $supOutQ->where('branch_id', $branchId);
        foreach ($supOutQ->get(['payment_method', 'amount']) as $p) {
            $key = $this->normaliseMethod($p->payment_method ?? 'cash');
            if (isset($accounts[$key])) $accounts[$key]['out'] += (float) $p->amount;
        }

        // 6. Customer khata_payout (we paid the customer — refund / advance payout)
        $payoutQ = Payment::where('payment_type', 'khata_payout')->whereRaw('amount > 0');
        if (!$isAll) {
            $payoutQ->whereHas('customer', fn($q) => $q->where('branch_id', $branchId));
        }
        foreach ($payoutQ->get(['payment_method', 'amount']) as $p) {
            $key = $this->normaliseMethod($p->payment_method ?? 'cash');
            if (isset($accounts[$key])) $accounts[$key]['out'] += (float) $p->amount;
        }

        // 7. Account transfers (e.g. cash deposited into bank)
        $transferQ = AccountTransfer::query();
        if (!$isAll) $transferQ->where('branch_id', $branchId);
        foreach ($transferQ->get(['from_account', 'to_account', 'amount']) as $t) {
            $from = $this->normaliseMethod($t->from_account);
            $to   = $this->normaliseMethod($t->to_account);
            if (isset($accounts[$from])) $accounts[$from]['out'] += (float) $t->amount;
            if (isset($accounts[$to]))   $accounts[$to]['in']   += (float) $t->amount;
        }

        // Compute balance and remove zero accounts
        $summary = collect($accounts)->map(function ($a, $method) {
            return array_merge($a, [
                'method'  => $method,
                'balance' => $a['in'] - $a['out'],
            ]);
        })->filter(fn($a) => $a['in'] > 0 || $a['out'] > 0)->values();

        $totalIn  = $summary->sum('in');
        $totalOut = $summary->sum('out');
        $totalBal = $totalIn - $totalOut;

        // Receivables & payables context
        $totalReceivables = $this->scopeBranch(
            Order::whereNotIn('status', ['cancelled', 'refunded'])->where('balance_amount', '>', 0)
        )->sum('balance_amount');

        $totalPayables = Purchase::where('payment_status', '!=', 'paid')
            ->whereRaw('total_amount > paid_amount')
            ->when(!$isAll, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('SUM(total_amount - paid_amount) as balance')
            ->value('balance') ?? 0;

        $paymentMethods  = PaymentMethod::active()->get();
        $recentTransfers = AccountTransfer::with('user')
            ->when(!$isAll, fn($q) => $q->where('branch_id', $branchId))
            ->latest()->take(10)->get();

        return view('admin.cash.available', compact(
            'summary', 'totalIn', 'totalOut', 'totalBal',
            'totalReceivables', 'totalPayables',
            'paymentMethods', 'recentTransfers'
        ));
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'from_account'    => 'required|string',
            'to_account'      => 'required|string|different:from_account',
            'amount'          => 'required|numeric|min:1',
            'transferred_at'  => 'required|date',
            'note'            => 'nullable|string|max:255',
        ]);

        $branchId = $this->branchId();

        AccountTransfer::create([
            'branch_id'      => $branchId !== 'all' ? $branchId : null,
            'user_id'        => auth()->id(),
            'from_account'   => $request->from_account,
            'to_account'     => $request->to_account,
            'amount'         => $request->amount,
            'note'           => $request->note,
            'transferred_at' => $request->transferred_at,
        ]);

        return redirect()->route('admin.cash.available')
            ->with('success', 'Transfer recorded: Rs. ' . number_format($request->amount, 0) .
                ' from ' . ucfirst($request->from_account) . ' to ' . ucfirst($request->to_account) . '.');
    }

    private function normaliseMethod(string $method): string
    {
        return match(true) {
            in_array($method, ['cash', 'نقد'])                         => 'cash',
            in_array($method, ['bank', 'bank_transfer', 'online'])     => 'bank',
            in_array($method, ['mobile_money', 'jazzcash', 'easypaisa', 'mobile']) => 'mobile_money',
            in_array($method, ['card', 'debit_card', 'credit_card'])   => 'card',
            in_array($method, ['cheque', 'check'])                     => 'cheque',
            in_array($method, ['credit', 'khata', 'cod'])              => 'credit',
            default => 'cash',
        };
    }

    // Find or create the global "Cash" ledger account (asset). One row, all branches.
    private function getCashAccount(): LedgerAccount
    {
        return LedgerAccount::firstOrCreate(
            ['name' => 'Cash'],
            [
                'account_code'    => LedgerAccount::generateAccountCode(),
                'type'            => LedgerAccount::TYPE_ASSET,
                'category'        => 'Cash & Bank',
                'description'     => 'Cash drawer (auto-created by Cash In/Out feature)',
                'is_active'       => true,
                'opening_balance' => 0,
                'created_by'      => auth()->id(),
            ]
        );
    }
}

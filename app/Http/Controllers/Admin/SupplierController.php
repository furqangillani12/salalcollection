<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use BranchScoped;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->scopeBranch(Supplier::query());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->latest()->paginate(10);
        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        $branchId = $this->branchId();
        if ($branchId && $branchId !== 'all') {
            $validated['branch_id'] = $branchId;
        }

        $supplier = Supplier::create($validated);

        $linkedMsg = '';
        if ($request->boolean('also_customer')) {
            $customer = $this->ensureCustomerCounterpart($supplier);
            if ($customer) {
                $linkedMsg = " Linked to customer “{$customer->name}”.";
            }
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.' . $linkedMsg);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return view('admin.suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
        ]);

        $supplier->update($validated);

        $linkedMsg = '';
        if ($request->boolean('also_customer') && !$supplier->linked_customer_id) {
            $supplier->refresh();
            $customer = $this->ensureCustomerCounterpart($supplier);
            if ($customer) {
                $linkedMsg = " Linked to customer “{$customer->name}”.";
            }
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.' . $linkedMsg);
    }

    /**
     * Find or create the customer record that represents the same party,
     * and link both directions. Returns the customer (existing or new), or
     * null if no action was needed.
     */
    private function ensureCustomerCounterpart(Supplier $supplier): ?\App\Models\Customer
    {
        if ($supplier->linked_customer_id) {
            return $supplier->linkedCustomer;
        }

        $branchId = $supplier->branch_id;

        $customer = null;
        if ($supplier->phone) {
            $customer = \App\Models\Customer::where('phone', $supplier->phone)
                ->whereNull('linked_supplier_id')
                ->when($branchId, fn ($q) => $q->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                }))
                ->first();
        }

        if (!$customer) {
            $customer = \App\Models\Customer::create([
                'branch_id'       => $branchId,
                'name'            => $supplier->name,
                'email'           => $supplier->email,
                'phone'           => $supplier->phone,
                'address'         => $supplier->address,
                'customer_type'   => 'customer',
                'loyalty_points'  => 0,
                'credit_enabled'  => false,
                'credit_limit'    => 0,
                'current_balance' => 0,
                'credit_due_days' => 30,
                'barcode'         => \App\Models\Customer::generateBarcode(),
            ]);
        }

        $supplier->update(['linked_customer_id' => $customer->id]);
        $customer->update(['linked_supplier_id' => $supplier->id]);

        return $customer;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }

    /**
     * Show supplier ledger (khata) with all purchases and payments.
     */
    public function ledger(Supplier $supplier)
    {
        $supplier->load(['purchases.items.product', 'payments']);

        $purchases = $supplier->purchases()->orderBy('purchase_date')->orderBy('id')->get();
        $payments = $supplier->payments()->orderBy('payment_date')->orderBy('id')->get();

        // Build unified transaction list
        $transactions = [];

        foreach ($purchases as $p) {
            // Subtract any linked supplier payments to get original paid at purchase time
            // (storePayment adds to purchase.paid_amount, so we undo that to avoid double-counting)
            $linkedPayments = $payments->where('purchase_id', $p->id)->sum('amount');
            $originalPaid = $p->paid_amount - $linkedPayments;

            $transactions[] = [
                'type'      => 'purchase',
                'id'        => $p->id,
                'date'      => $p->purchase_date,
                'reference' => $p->invoice_number,
                'amount'    => $p->total_amount,
                'paid'      => $originalPaid,
                'items_count' => $p->items->count(),
                'notes'     => $p->notes,
                'created_at' => $p->created_at,
            ];
        }

        foreach ($payments as $pay) {
            $isReceipt = ($pay->direction ?? 'out') === 'in';
            $transactions[] = [
                'type'      => $isReceipt ? 'receipt' : 'payment',
                'id'        => $pay->id,
                'date'      => $pay->payment_date,
                'reference' => $pay->payment_number,
                'amount'    => $pay->amount,
                'method'    => $pay->payment_method,
                'notes'     => $pay->notes,
                'purchase_id' => $pay->purchase_id,
                'created_at' => $pay->created_at,
            ];
        }

        // Sort by date, then by created_at
        usort($transactions, function ($a, $b) {
            $dateCompare = strtotime($a['date']) - strtotime($b['date']);
            if ($dateCompare !== 0) return $dateCompare;
            return strtotime($a['created_at']) - strtotime($b['created_at']);
        });

        // Calculate running balance
        $runningBalance = 0;
        foreach ($transactions as &$txn) {
            if ($txn['type'] === 'purchase') {
                // Debit: full amount, Credit: original paid at purchase time
                $runningBalance += ($txn['amount'] - $txn['paid']);
            } elseif ($txn['type'] === 'receipt') {
                // Cash received back from supplier increases what they owe (or reduces advance)
                $runningBalance += $txn['amount'];
            } else {
                // Payment to supplier reduces balance
                $runningBalance -= $txn['amount'];
            }
            $txn['running_balance'] = $runningBalance;
        }
        unset($txn);

        // Newest first
        $transactions = array_reverse($transactions);

        // Summary — no double-counting
        $paidOutPayments    = $payments->where('direction', '!=', 'in');
        $receivedInPayments = $payments->where('direction', 'in');

        $totalPurchased          = $purchases->sum('total_amount');
        $linkedPaymentsTotal     = $paidOutPayments->whereNotNull('purchase_id')->sum('amount');
        $originalPaidOnPurchases = $purchases->sum('paid_amount') - $linkedPaymentsTotal;
        $totalSupplierPayments   = $paidOutPayments->sum('amount');
        $totalReceived           = $receivedInPayments->sum('amount');
        $totalPaid               = $originalPaidOnPurchases + $totalSupplierPayments;
        $balance                 = $totalPurchased - $totalPaid + $totalReceived; // positive = we owe

        $summary = [
            'total_purchased' => $totalPurchased,
            'total_paid'      => $totalPaid,
            'total_received'  => $totalReceived,
            'total_due'       => max(0, $balance),
            'advance'         => $balance < 0 ? abs($balance) : 0,
            'balance'         => $balance,
            'payments_count'  => $paidOutPayments->count(),
            'receipts_count'  => $receivedInPayments->count(),
        ];

        // ── Linked customer (if any) ───────────────────────────────────────
        $supplier->load('linkedCustomer');
        $linkedCustomer = $supplier->linkedCustomer;
        $linkedCustomerBalance = 0.0;
        $linkedNetBalance = $balance; // default: just what we owe
        if ($linkedCustomer) {
            $linkedCustomerBalance = round((float) ($linkedCustomer->current_balance ?? 0), 2);
            // Net (from supplier’s point-of-view): supplier balance - customer balance.
            // Positive = we still owe them net. Negative = they owe us net.
            $linkedNetBalance = round($balance - $linkedCustomerBalance, 2);
        }

        $availableCustomers = $linkedCustomer ? collect() : $this->scopeBranch(\App\Models\Customer::query())
            ->whereNull('linked_supplier_id')
            ->orderBy('name')->get(['id', 'name', 'phone', 'customer_type', 'current_balance']);

        return view('admin.suppliers.ledger', compact(
            'supplier', 'transactions', 'summary',
            'linkedCustomer', 'linkedCustomerBalance', 'linkedNetBalance', 'availableCustomers'
        ));
    }

    /**
     * Record a payment to / receipt from supplier.
     * direction = 'out' (default) — cash paid to supplier.
     * direction = 'in' — cash received back from supplier (refund / advance return).
     */
    public function storePayment(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'purchase_id'    => 'nullable|exists:purchases,id',
            'notes'          => 'nullable|string|max:500',
            'direction'      => 'nullable|in:in,out',
        ]);

        $direction = $validated['direction'] ?? 'out';
        $isReceipt = $direction === 'in';
        $branchId  = $this->branchId();

        $payment = SupplierPayment::create([
            'payment_number' => SupplierPayment::generatePaymentNumber(),
            'supplier_id'    => $supplier->id,
            // Receipts don't link to a specific purchase
            'purchase_id'    => $isReceipt ? null : ($validated['purchase_id'] ?? null),
            'branch_id'      => $branchId && $branchId !== 'all' ? $branchId : null,
            'amount'         => $validated['amount'],
            'payment_date'   => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'direction'      => $direction,
            'notes'          => $validated['notes'] ?? null,
            'created_by'     => auth()->id(),
        ]);

        // Only outbound payments against a specific purchase update paid_amount
        if (!$isReceipt && $payment->purchase_id) {
            $purchase = $payment->purchase;
            $purchase->paid_amount += $payment->amount;
            if ($purchase->paid_amount >= $purchase->total_amount) {
                $purchase->payment_status = 'paid';
            } else {
                $purchase->payment_status = 'partial';
            }
            $purchase->save();
        }

        $msg = $isReceipt
            ? 'Cash receipt of Rs. ' . number_format($validated['amount'], 0) . ' recorded.'
            : 'Payment of Rs. ' . number_format($validated['amount'], 0) . ' recorded successfully.';

        return redirect()->route('suppliers.payment.voucher', [$supplier, $payment])
            ->with('success', $msg);
    }

    /**
     * Delete / reverse a supplier payment or receipt.
     */
    public function deletePayment(Supplier $supplier, SupplierPayment $payment)
    {
        $isReceipt = ($payment->direction ?? 'out') === 'in';

        // Only outbound payments touched a purchase's paid_amount — reverse that
        if (!$isReceipt && $payment->purchase_id) {
            $purchase = $payment->purchase;
            if ($purchase) {
                $purchase->paid_amount = max(0, $purchase->paid_amount - $payment->amount);
                if ($purchase->paid_amount <= 0) {
                    $purchase->payment_status = 'unpaid';
                } elseif ($purchase->paid_amount < $purchase->total_amount) {
                    $purchase->payment_status = 'partial';
                }
                $purchase->save();
            }
        }

        $payment->delete();

        $msg = $isReceipt ? 'Cash receipt reversed successfully.' : 'Payment reversed successfully.';

        return redirect()->route('suppliers.ledger', $supplier)
            ->with('success', $msg);
    }

    /**
     * Show payment / receipt voucher.
     */
    public function paymentVoucher(Supplier $supplier, SupplierPayment $payment)
    {
        $isReceipt = ($payment->direction ?? 'out') === 'in';

        // Base ledger state (net, excluding this payment for "before")
        $totalPurchased       = $supplier->purchases()->sum('total_amount');
        $totalPaidOnPurchases = $supplier->purchases()->sum('paid_amount');

        // Outbound payments before this one
        $totalPaidBefore = SupplierPayment::where('supplier_id', $supplier->id)
            ->where('direction', '!=', 'in')
            ->where('id', '<', $payment->id)
            ->sum('amount');

        // Receipts before this one
        $totalReceivedBefore = SupplierPayment::where('supplier_id', $supplier->id)
            ->where('direction', 'in')
            ->where('id', '<', $payment->id)
            ->sum('amount');

        // If this payment was linked to a purchase, its amount is already reflected in paid_amount
        $paidOnPurchasesAdjusted = $totalPaidOnPurchases;
        if (!$isReceipt && $payment->purchase_id) {
            $paidOnPurchasesAdjusted -= $payment->amount;
        }

        $balanceBefore = $totalPurchased - $paidOnPurchasesAdjusted - $totalPaidBefore + $totalReceivedBefore;
        $balanceAfter  = $balanceBefore + ($isReceipt ? $payment->amount : -$payment->amount);

        return view('admin.suppliers.payment-voucher', compact('supplier', 'payment', 'balanceBefore', 'balanceAfter', 'isReceipt'));
    }
}

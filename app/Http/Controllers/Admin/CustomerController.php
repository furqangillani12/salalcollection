<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CreditLedger;
use App\Models\CreditTransaction;
use App\Models\PaymentMethod;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    use BranchScoped;
    public function index(Request $request)
    {
        $query = $this->scopeBranch(Customer::query());

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->has('type') && $request->type) {
            $query->where('customer_type', $request->type);
        }

        // Credit status filter
        if ($request->has('credit') && $request->credit) {
            switch ($request->credit) {
                case 'enabled':
                    $query->where('credit_enabled', true);
                    break;
                case 'disabled':
                    $query->where('credit_enabled', false);
                    break;
                case 'has_balance':
                    $query->where('credit_enabled', true)
                          ->where('current_balance', '>', 0);
                    break;
                case 'overdue':
                    $customerIds = CreditTransaction::where('transaction_type', 'debit')
                        ->where('payment_status', 'pending')
                        ->where('due_date', '<', now())
                        ->pluck('customer_id')
                        ->unique();
                    $query->whereIn('id', $customerIds);
                    break;
            }
        }

        $customers = $query->latest()->paginate(10);

        // Calculate credit statistics scoped by branch
        $statsQuery = $this->scopeBranch(Customer::query());
        $creditStats = [
            'enabled' => (clone $statsQuery)->where('credit_enabled', true)->count(),
            'total_limit' => (clone $statsQuery)->where('credit_enabled', true)->sum('credit_limit'),
            'outstanding' => (clone $statsQuery)->where('credit_enabled', true)->sum('current_balance'),
            'overdue' => CreditTransaction::overdue()->sum('remaining_amount') ?? 0
        ];

        return view('admin.customers.index', compact('customers', 'creditStats'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:customers,email',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'loyalty_points' => 'nullable|numeric|min:0',
            'customer_type'  => 'required|in:customer,reseller,wholesale',
            'barcode'        => 'nullable|string|unique:customers,barcode',
            // Credit fields validation
            'credit_enabled' => 'nullable|boolean',
            'credit_limit'   => 'nullable|numeric|min:0',
            'credit_due_days'=> 'nullable|integer|min:1|max:365',
            // Optional website login password
            'website_password' => 'nullable|string|min:6|max:255',
        ]);

        // Website login password → hashed by the model cast. Not a column on its own.
        if ($request->filled('website_password')) {
            $validated['password'] = $request->input('website_password');
        }
        unset($validated['website_password']);

        // Handle credit_enabled properly (checkbox sends value only when checked)
        $validated['credit_enabled'] = $request->has('credit_enabled') ? true : false;
        
        // Set default values for credit fields
        if (!$validated['credit_enabled']) {
            $validated['credit_limit'] = 0;
            $validated['credit_due_days'] = 30;
            $validated['current_balance'] = 0;
        } else {
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
            $validated['credit_due_days'] = $validated['credit_due_days'] ?? 30;
            $validated['current_balance'] = 0;
            $validated['credit_start_date'] = now();
        }

        // Convert loyalty_points to integer
        if (isset($validated['loyalty_points'])) {
            $validated['loyalty_points'] = (int) $validated['loyalty_points'];
        }

        // Generate barcode if not provided
        if (empty($validated['barcode'])) {
            $validated['barcode'] = Customer::generateBarcode();
        }

        // Assign to current branch
        $branchId = $this->branchId();
        if ($branchId && $branchId !== 'all') {
            $validated['branch_id'] = $branchId;
        }

        DB::beginTransaction();

        try {
            $customer = Customer::create($validated);
            
            // Create credit ledger if credit is enabled
            if ($customer->credit_enabled) {
                CreditLedger::create([
                    'ledger_number' => CreditLedger::generateLedgerNumber(),
                    'customer_id' => $customer->id,
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'opening_balance' => 0,
                    'closing_balance' => 0,
                    'credit_limit' => $customer->credit_limit,
                    'status' => 'active',
                    'notes' => 'Credit enabled on ' . now()->format('Y-m-d')
                ]);
            }
            
            // Auto-create + link supplier counterpart if requested
            $linkedMsg = '';
            if ($request->boolean('also_supplier')) {
                $supplier = $this->ensureSupplierCounterpart($customer);
                if ($supplier) {
                    $linkedMsg = " Linked to supplier “{$supplier->name}”.";
                }
            }

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer created successfully.' . $linkedMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Find or create the supplier record that represents the same party,
     * and link both directions. Returns the supplier (existing or new), or
     * null if no action was needed.
     */
    private function ensureSupplierCounterpart(Customer $customer): ?\App\Models\Supplier
    {
        if ($customer->linked_supplier_id) {
            return $customer->linkedSupplier; // already linked, no-op
        }

        $branchId = $customer->branch_id;

        // Prefer an existing un-linked supplier with the same phone in the same branch
        $supplier = null;
        if ($customer->phone) {
            $supplier = \App\Models\Supplier::where('phone', $customer->phone)
                ->whereNull('linked_customer_id')
                ->when($branchId, fn ($q) => $q->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                }))
                ->first();
        }

        if (!$supplier) {
            $supplier = \App\Models\Supplier::create([
                'branch_id'    => $branchId,
                'name'         => $customer->name,
                'email'        => $customer->email,
                'phone'        => $customer->phone,
                'address'      => $customer->address,
                'company_name' => null,
            ]);
        }

        $customer->update(['linked_supplier_id' => $supplier->id]);
        $supplier->update(['linked_customer_id' => $customer->id]);

        return $supplier;
    }

    public function show(Customer $customer)
    {
        $customer->loadCount('orders');
        $customer->load(['orders' => function($query) {
            $query->latest()->take(5);
        }]);
        
        // Load credit relationships
        $customer->load(['creditLedger', 'creditTransactions' => function($query) {
            $query->latest()->take(10);
        }]);
        
        $customer->load(['pointTransactions' => fn ($q) => $q->take(15)]);

        return view('admin.customers.show', compact('customer'));
    }

    /** Manually award / adjust reward points (photo / video / social review bonuses). */
    public function awardPoints(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'points' => 'required|integer',
            'type'   => 'nullable|string|max:30',
            'note'   => 'nullable|string|max:191',
        ]);

        $customer->awardPoints(
            (int) $data['points'],
            $data['type'] ?: 'adjust',
            $data['note'] ?? null,
            null,
            auth()->id()
        );

        return back()->with('success', "{$data['points']} points recorded for {$customer->name}.");
    }

    public function edit(Customer $customer)
    {
        // Load credit ledger for the edit form
        $customer->load('creditLedger');
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'loyalty_points' => 'nullable|numeric|min:0',
            'customer_type'  => 'required|in:customer,reseller,wholesale',
            'barcode'        => 'nullable|string|unique:customers,barcode,' . $customer->id,
            // Credit fields validation
            'credit_enabled' => 'nullable|boolean',
            'credit_limit'   => 'nullable|numeric|min:0',
            'credit_due_days'=> 'nullable|integer|min:1|max:365',
            // Optional website login password
            'website_password' => 'nullable|string|min:6|max:255',
        ]);

        // Website login password → hashed by model cast. Blank = keep unchanged.
        if ($request->filled('website_password')) {
            $validated['password'] = $request->input('website_password');
        }
        unset($validated['website_password']);

        // Handle credit_enabled properly
        $validated['credit_enabled'] = $request->has('credit_enabled') ? true : false;
        
        // Set default values for credit fields
        if (!$validated['credit_enabled']) {
            $validated['credit_limit'] = 0;
            $validated['credit_due_days'] = 30;
            
            // Check if customer has outstanding balance before disabling
            if ($customer->current_balance > 0) {
                return redirect()->back()
                    ->with('warning', 'Cannot disable credit. Customer has outstanding balance of Rs. ' . number_format($customer->current_balance, 2))
                    ->withInput();
            }
            
            $validated['current_balance'] = 0;
        } else {
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
            $validated['credit_due_days'] = $validated['credit_due_days'] ?? 30;
            
            if (!$customer->credit_start_date) {
                $validated['credit_start_date'] = now();
            }
        }

        // Convert loyalty_points to integer
        if (isset($validated['loyalty_points'])) {
            $validated['loyalty_points'] = (int) $validated['loyalty_points'];
        }

        DB::beginTransaction();
        
        try {
            $customer->update($validated);
            
            // Handle credit ledger
            if ($customer->credit_enabled) {
                $ledger = $customer->creditLedger;
                
                if (!$ledger) {
                    // Create new ledger if it doesn't exist
                    CreditLedger::create([
                        'ledger_number' => CreditLedger::generateLedgerNumber(),
                        'customer_id' => $customer->id,
                        'total_debit' => 0,
                        'total_credit' => 0,
                        'opening_balance' => 0,
                        'closing_balance' => 0,
                        'credit_limit' => $customer->credit_limit,
                        'status' => 'active',
                        'notes' => 'Credit enabled on ' . now()->format('Y-m-d')
                    ]);
                } else {
                    // Update existing ledger
                    $ledger->credit_limit = $customer->credit_limit;
                    $ledger->status = 'active';
                    $ledger->save();
                }
            } else {
                // If credit is disabled, update ledger status
                $ledger = $customer->creditLedger;
                if ($ledger && $customer->current_balance == 0) {
                    $ledger->status = 'inactive';
                    $ledger->save();
                }
            }
            
            // Auto-link to supplier counterpart if requested and not already linked
            $linkedMsg = '';
            if ($request->boolean('also_supplier') && !$customer->linked_supplier_id) {
                $customer->refresh();
                $supplier = $this->ensureSupplierCounterpart($customer);
                if ($supplier) {
                    $linkedMsg = " Linked to supplier “{$supplier->name}”.";
                }
            }

            DB::commit();

            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer updated successfully.' . $linkedMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update customer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Customer $customer)
    {
        // Check if customer has credit balance before deleting
        if ($customer->current_balance > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete customer. Customer has outstanding credit balance of Rs. ' . number_format($customer->current_balance, 2));
        }
        
        // Check if customer has credit ledger
        if ($customer->creditLedger) {
            // Check if there are any transactions
            if ($customer->creditTransactions()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete customer. Customer has credit transaction history.');
            }
            
            // Delete the ledger
            $customer->creditLedger->delete();
        }

        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function search(Request $request)
    {
        $search = $request->get('q');

        $query = $this->scopeBranch(Customer::query());
        $customers = $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'text' => $customer->name . ' (' . $customer->customer_type . ')' . 
                             ($customer->barcode ? ' [' . $customer->barcode . ']' : '') .
                             ($customer->credit_enabled ? ' - Credit: Rs. ' . number_format($customer->available_credit, 0) . ' avail' : ''),
                    'barcode' => $customer->barcode,
                    'name' => $customer->name,
                    'type' => $customer->customer_type,
                    'credit_enabled' => $customer->credit_enabled,
                    'credit_limit' => $customer->credit_limit,
                    'current_balance' => $customer->current_balance,
                    'available_credit' => $customer->available_credit
                ];
            });

        return response()->json($customers);
    }

    /**
     * Get customer credit status (API endpoint for POS)
     */
    public function creditStatus(Customer $customer)
    {
        return response()->json([
            'credit_enabled' => $customer->credit_enabled,
            'credit_limit' => $customer->credit_limit,
            'current_balance' => $customer->current_balance,
            'available_credit' => $customer->available_credit,
            'credit_due_days' => $customer->credit_due_days,
            'has_sufficient_credit' => $customer->hasSufficientCredit(request('amount') ?? 0)
        ]);
    }

    /**
     * Check if customer has sufficient credit for amount (API endpoint for POS)
     */
    public function checkCredit(Request $request, Customer $customer)
    {
        $amount = $request->amount ?? 0;
        
        if (!$customer->credit_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Credit is not enabled for this customer',
                'available_credit' => 0
            ]);
        }
        
        if (!$customer->hasSufficientCredit($amount)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credit limit',
                'available_credit' => $customer->available_credit,
                'current_balance' => $customer->current_balance,
                'credit_limit' => $customer->credit_limit
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Sufficient credit available',
            'available_credit' => $customer->available_credit,
            'current_balance' => $customer->current_balance,
            'credit_limit' => $customer->credit_limit
        ]);
    }

    public function khata(Request $request, Customer $customer)
    {
        $fromDate = $request->input('from_date', now()->subMonths(3)->toDateString());
        $toDate   = $request->input('to_date',   now()->toDateString());

        // ── 1. Get orders in period ────────────────────────────────────────
        $orders = $customer->orders()
            ->with('items.product')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at')
            ->get();

        // ── 2. Get standalone khata payments + payouts + offsets in period ───
        $khataPayments = \App\Models\Payment::where('customer_id', $customer->id)
            ->whereIn('payment_type', ['khata', 'khata_payout', 'khata_offset'])
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->orderBy('payment_date')
            ->get();

        // ── 3. Merge into one timeline sorted by date ──────────────────────
        $transactions = collect();

        foreach ($orders as $order) {
            $transactions->push([
                'type'            => 'order',
                'date'            => $order->created_at,
                'id'              => $order->id,
                'reference'       => $order->order_number,
                'amount'          => $order->total,              // debit (bill)
                'paid'            => ($order->paid_amount == 0 && $order->balance_amount == 0) ? $order->total : $order->paid_amount,
                'balance_on_bill' => $order->balance_amount ?? 0,
                'method'          => $order->payment_method,
                'items_count'     => $order->items->count(),
                'running_balance' => 0,  // filled below
            ]);
        }

        foreach ($khataPayments as $payment) {
            $type = match ($payment->payment_type) {
                'khata_payout' => 'payout',
                'khata_offset' => 'offset',
                default        => 'payment',
            };
            $transactions->push([
                'type'            => $type,
                'date'            => $payment->payment_date,
                'id'              => $payment->id,
                'reference'       => $payment->payment_number ?? $payment->reference_number,
                'amount'          => $payment->amount,
                'paid'            => $payment->amount,
                'balance_on_bill' => 0,
                'method'          => $payment->payment_method,
                'notes'           => $payment->notes,
                'items_count'     => 0,
                'running_balance' => 0,  // filled below
            ]);
        }

      // Sort by date ascending — convert to plain array so we can modify it
        $transactions = $transactions->sortBy('date')->values()->toArray(); // ← toArray() added

        // ── 4. Calculate running balance ──────────────────────────────────────
        $runningBalance = (float) ($customer->current_balance ?? 0);

        // Rewind to get opening balance before this period
        foreach ($transactions as $txn) {
            if ($txn['type'] === 'order') {
                $runningBalance -= ($txn['amount'] - $txn['paid']);
            } elseif ($txn['type'] === 'payout') {
                $runningBalance -= $txn['amount'];
            } else {
                $runningBalance += $txn['amount'];
            }
        }
        $openingBalance = $runningBalance;

        // Walk forward and assign running balance to each row
        foreach ($transactions as $i => $txn) {
            if ($txn['type'] === 'order') {
                $runningBalance += ($txn['amount'] - $txn['paid']);
            } elseif ($txn['type'] === 'payout') {
                $runningBalance += $txn['amount'];
            } else {
                $runningBalance -= $txn['amount'];
            }
            $transactions[$i]['running_balance'] = $runningBalance; // ✅ works on array
        }

        // Reverse for display: newest first
        $transactions = array_reverse($transactions);
        // ── 5. Period summary ──────────────────────────────────────────────
        $allOrders = $customer->orders()
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->get();

        $allKhataPayments = \App\Models\Payment::where('customer_id', $customer->id)
            ->where('payment_type', 'khata')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->get();

        $allKhataPayouts = \App\Models\Payment::where('customer_id', $customer->id)
            ->where('payment_type', 'khata_payout')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->get();

        $totalBilled = $allOrders->sum('total');
        $totalPaidOnOrders = $allOrders->sum(fn($o) => ($o->paid_amount == 0 && $o->balance_amount == 0) ? $o->total : $o->paid_amount);
        $totalKhataPayments = $allKhataPayments->sum('amount');
        $totalKhataPayouts = $allKhataPayouts->sum('amount');
        $totalPaid = $totalPaidOnOrders + $totalKhataPayments;

        $summary = [
            'total_billed'         => $totalBilled,
            'total_paid'           => $totalPaid,
            'total_balance'        => max(0, $totalBilled - $totalPaid),
            'order_count'          => $allOrders->count(),
            'total_khata_payments' => $totalKhataPayments,
            'payments_count'       => $allKhataPayments->count(),
            'total_khata_payouts'  => $totalKhataPayouts,
            'payouts_count'        => $allKhataPayouts->count(),
        ];

        // ── 6. For pagination display we use the paginator on orders ──────
        $ordersPaginated = $customer->orders()
            ->with('items.product')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at')
            ->paginate(30);

        // ── 7. CSV Export ──────────────────────────────────────────────────
        if ($request->has('export') && $request->export === 'csv') {
            $filename = "khata_{$customer->name}_{$fromDate}_to_{$toDate}.csv";
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];
            $callback = function () use ($transactions, $customer, $openingBalance) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Date', 'Type', 'Reference', 'Debit (Bill / Cash Out)', 'Credit (Paid)', 'Running Balance', 'Method', 'Notes']);
                $firstDate = !empty($transactions) ? ($transactions[array_key_first($transactions)]['date'] ?? '') : '';
                fputcsv($handle, [$firstDate, 'Opening Balance', '', '', '', number_format($openingBalance, 2), '', '']);
                foreach ($transactions as $txn) {
                    $typeLabel = match ($txn['type']) {
                        'payment' => 'Payment Received',
                        'payout'  => 'Cash Paid Out',
                        default   => 'Sale Bill',
                    };
                    $debit = match ($txn['type']) {
                        'order'  => number_format($txn['amount'], 2),
                        'payout' => number_format($txn['amount'], 2),
                        default  => '',
                    };
                    $credit = match ($txn['type']) {
                        'payment' => number_format($txn['amount'], 2),
                        'order'   => $txn['paid'] > 0 ? number_format($txn['paid'], 2) : '',
                        default   => '',
                    };
                    fputcsv($handle, [
                        \Carbon\Carbon::parse($txn['date'])->format('d-M-Y'),
                        $typeLabel,
                        $txn['reference'],
                        $debit,
                        $credit,
                        number_format($txn['running_balance'], 2),
                        $txn['method'] ?? '',
                        $txn['notes'] ?? '',
                    ]);
                }
                fclose($handle);
            };
            return response()->stream($callback, 200, $headers);
        }

        $paymentMethods = PaymentMethod::where('is_active', true)->orderBy('sort_order')->get();

        // ── 8. Linked supplier (if any) ────────────────────────────────────
        $customer->load('linkedSupplier');
        $linkedSupplier = $customer->linkedSupplier;
        $linkedSupplierBalance = 0.0;
        $linkedNetBalance = (float) ($customer->current_balance ?? 0);
        if ($linkedSupplier) {
            $purchased    = (float) $linkedSupplier->purchases()->sum('total_amount');
            $paidLinked   = (float) $linkedSupplier->purchases()->sum('paid_amount');
            $paidUnlinked = (float) $linkedSupplier->payments()->whereNull('purchase_id')->where('direction', 'out')->sum('amount');
            $received     = (float) $linkedSupplier->payments()->whereNull('purchase_id')->where('direction', 'in')->sum('amount');
            $linkedSupplierBalance = round($purchased - $paidLinked - $paidUnlinked + $received, 2);
            $linkedNetBalance      = round((float) ($customer->current_balance ?? 0) - $linkedSupplierBalance, 2);
        }

        // For the "Link to existing supplier" picker (only loaded when not yet linked)
        $availableSuppliers = $linkedSupplier ? collect() : $this->scopeBranch(\App\Models\Supplier::query())
            ->whereNull('linked_customer_id')
            ->orderBy('name')->get(['id', 'name', 'phone', 'company_name']);

        return view('admin.customers.khata', compact(
            'customer', 'orders', 'transactions',
            'fromDate', 'toDate', 'summary', 'openingBalance', 'paymentMethods',
            'linkedSupplier', 'linkedSupplierBalance', 'linkedNetBalance', 'availableSuppliers'
        ));
    }


    /**
     * Record a khata transaction — either cash IN (payment) or cash OUT (payout / refund advance).
     */
    public function storeKhataPayment(Request $request, Customer $customer)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'notes'          => 'nullable|string|max:500',
            'direction'      => 'nullable|in:in,out',
        ]);

        $amount    = (float) $request->amount;
        $direction = $request->input('direction', 'in');
        $isPayout  = $direction === 'out';

        DB::beginTransaction();
        try {
            $payment = \App\Models\Payment::create([
                'payment_number'   => \App\Models\Payment::generatePaymentNumber(),
                'payment_type'     => $isPayout ? 'khata_payout' : 'khata',
                'order_id'         => null,
                'customer_id'      => $customer->id,
                'amount'           => $amount,
                'payment_date'     => $request->payment_date,
                'payment_method'   => $request->payment_method,
                'reference_number' => ($isPayout ? 'PAYOUT-' : 'KHATA-') . strtoupper(uniqid()),
                'notes'            => $request->notes,
                'status'           => 'completed',
                'created_by'       => auth()->id(),
            ]);

            $newBalance = (float) ($customer->current_balance ?? 0) + ($isPayout ? $amount : -$amount);
            $customer->update(['current_balance' => $newBalance]);

            DB::commit();

            $msg = $isPayout
                ? 'Rs. ' . number_format($amount, 0) . ' paid out to customer.'
                : 'Rs. ' . number_format($amount, 0) . ' payment recorded.';

            return redirect()->route('admin.customers.khata.payment.voucher', [$customer, $payment])
                ->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }


    /**
     * Delete / reverse a khata payment or payout
     */
    public function deleteKhataPayment(Customer $customer, \App\Models\Payment $payment)
    {
        if ($payment->customer_id !== $customer->id || !in_array($payment->payment_type, ['khata', 'khata_payout'], true)) {
            return back()->with('error', 'Cannot delete this payment.');
        }

        $isPayout = $payment->payment_type === 'khata_payout';

        DB::beginTransaction();
        try {
            $delta = $isPayout ? -$payment->amount : $payment->amount;
            $customer->update(['current_balance' => $customer->current_balance + $delta]);
            $payment->delete();
            DB::commit();

            $msg = $isPayout
                ? 'Cash-out of Rs. ' . number_format($payment->amount, 0) . ' reversed.'
                : 'Payment of Rs. ' . number_format($payment->amount, 0) . ' reversed.';

            return redirect()->route('admin.customers.khata', $customer)
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    /**
     * Show voucher for a khata payment or payout
     */
    public function paymentVoucher(Customer $customer, \App\Models\Payment $payment)
    {
        if ($payment->customer_id !== $customer->id || !in_array($payment->payment_type, ['khata', 'khata_payout'], true)) {
            abort(404);
        }

        $isPayout = $payment->payment_type === 'khata_payout';

        // Calculate balance before and after this payment
        // All order nets before this payment
        $orderNetBefore = (float) \App\Models\Order::where('customer_id', $customer->id)
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '<', $payment->created_at)
            ->selectRaw('COALESCE(SUM(
                CASE
                    WHEN (paid_amount = 0 OR paid_amount IS NULL)
                         AND (balance_amount = 0 OR balance_amount IS NULL)
                    THEN 0
                    ELSE total - COALESCE(paid_amount, 0)
                END
            ), 0) as net')
            ->value('net');

        // All khata payments (in) before this one — reduce balance
        $khataPaymentsBefore = (float) \App\Models\Payment::where('customer_id', $customer->id)
            ->where('payment_type', 'khata')
            ->where('id', '<', $payment->id)
            ->sum('amount');

        // All khata payouts (out) before this one — increase balance
        $khataPayoutsBefore = (float) \App\Models\Payment::where('customer_id', $customer->id)
            ->where('payment_type', 'khata_payout')
            ->where('id', '<', $payment->id)
            ->sum('amount');

        $balanceBefore = $orderNetBefore - $khataPaymentsBefore + $khataPayoutsBefore;
        $balanceAfter  = $balanceBefore + ($isPayout ? $payment->amount : -$payment->amount);

        return view('admin.customers.payment-voucher', compact(
            'customer', 'payment', 'balanceBefore', 'balanceAfter', 'isPayout'
        ));
    }

}
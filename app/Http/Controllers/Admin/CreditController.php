<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CreditLedger;
use App\Models\CreditTransaction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\BranchScoped;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    use BranchScoped;
    /**
     * Display customer credit dashboard
     */
    public function index(Request $request)
    {
        $query = $this->scopeBranch(Customer::query())
                        ->where('credit_enabled', true)
                        ->with('creditLedger');
        
        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }
        
        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->whereHas('creditLedger', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }
        
        $customers = $query->paginate(15);
        
        // Statistics
        $totalCreditSales = CreditTransaction::where('transaction_type', 'debit')->sum('amount');
        $totalCreditPayments = CreditTransaction::where('transaction_type', 'credit')->sum('amount');
        $totalOutstanding = $this->scopeBranch(Customer::query())->where('credit_enabled', true)->sum('current_balance');
        $overdueCount = CreditTransaction::overdue()->count();
        
        return view('admin.credit.index', compact(
            'customers', 
            'totalCreditSales', 
            'totalCreditPayments', 
            'totalOutstanding',
            'overdueCount'
        ));
    }

    /**
     * Show customer credit statement
     */
    public function customerStatement(Request $request, Customer $customer)
    {
        if (!$customer->credit_enabled) {
            return redirect()->route('admin.customers.edit', $customer)
                ->with('warning', 'Credit is not enabled for this customer.');
        }

        $ledger = $customer->creditLedger;
        $query = CreditTransaction::with('order:id,order_number')
                                    ->where('customer_id', $customer->id);

        if ($request->filled('from_date')) {
            $query->where('transaction_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                              ->orderBy('id', 'desc')
                              ->paginate(20);
        
        $summary = [
            'total_purchases' => CreditTransaction::where('customer_id', $customer->id)
                ->where('transaction_type', 'debit')
                ->sum('amount'),
            'total_payments' => CreditTransaction::where('customer_id', $customer->id)
                ->where('transaction_type', 'credit')
                ->sum('amount'),
            'current_balance' => $customer->current_balance,
            'credit_limit' => $customer->credit_limit,
            'available_credit' => $customer->available_credit,
            'overdue_amount' => CreditTransaction::where('customer_id', $customer->id)
                ->overdue()
                ->sum('remaining_amount'),
        ];
        
        return view('admin.credit.statement', compact('customer', 'ledger', 'transactions', 'summary'));
    }

    /**
     * Show form to collect credit payment
     */
    public function paymentForm(Request $request)
    {
        $customer = null;
        $outstandingTransactions = [];
        
        if ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
            
            if ($customer->credit_enabled) {
                $outstandingTransactions = CreditTransaction::where('customer_id', $customer->id)
                    ->where('transaction_type', 'debit')
                    ->whereIn('payment_status', ['pending', 'partial'])
                    ->where('remaining_amount', '>', 0)
                    ->orderBy('due_date')
                    ->orderBy('transaction_date')
                    ->get();
            }
        }
        
        $customers = $this->scopeBranch(Customer::query())
                            ->where('credit_enabled', true)
                            ->where('current_balance', '>', 0)
                            ->orderBy('name')
                            ->get();
        
        return view('admin.credit.payment', compact('customers', 'customer', 'outstandingTransactions'));
    }

    /**
 * Process credit payment
 */
public function processPayment(Request $request)
{
    // Debug logging
    Log::info('=== PAYMENT PROCESSING STARTED ===');
    Log::info('Request data:', $request->all());
    
    $validator = Validator::make($request->all(), [
        'customer_id' => 'required|exists:customers,id',
        'payment_date' => 'required|date',
        'payment_method' => 'required|string',
        'reference_number' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
        'transactions' => 'required|array|min:1',
        'transactions.*.id' => 'required|exists:credit_transactions,id',
        'transactions.*.paid_amount' => 'required|numeric|min:0.01',
    ]);

    if ($validator->fails()) {
        Log::warning('Payment validation failed:', $validator->errors()->toArray());
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    DB::beginTransaction();

    try {
        Log::info('Step 1: Finding customer...');
        $customer = Customer::with('creditLedger')->findOrFail($request->customer_id);
        Log::info('Customer found:', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'current_balance' => $customer->current_balance,
            'credit_enabled' => $customer->credit_enabled
        ]);
        
        Log::info('Step 2: Getting credit ledger...');
        $ledger = $customer->creditLedger;
        Log::info('Ledger:', [
            'ledger_id' => $ledger->id ?? null,
            'ledger_number' => $ledger->ledger_number ?? null,
            'exists' => $ledger ? 'yes' : 'no'
        ]);
        
        if (!$ledger) {
            Log::error('Credit ledger not found for customer: ' . $customer->id);
            throw new \Exception('Credit ledger not found for this customer');
        }

        $totalPaymentAmount = 0;
        $processedTransactions = [];
        
        Log::info('Step 3: Processing ' . count($request->transactions) . ' transactions...');
        
        // Process each transaction payment
        foreach ($request->transactions as $index => $item) {
            Log::info("Processing transaction {$index}:", $item);
            
            $transaction = CreditTransaction::findOrFail($item['id']);
            $paidAmount = floatval($item['paid_amount']);
            
            Log::info('Transaction details:', [
                'transaction_id' => $transaction->id,
                'transaction_number' => $transaction->transaction_number,
                'customer_id' => $transaction->customer_id,
                'amount' => $transaction->amount,
                'paid_amount_so_far' => $transaction->paid_amount,
                'remaining' => $transaction->remaining_amount,
                'payment_status' => $transaction->payment_status
            ]);
            
            // Validate transaction belongs to this customer
            if ($transaction->customer_id != $customer->id) {
                Log::error('Transaction belongs to different customer', [
                    'transaction_customer' => $transaction->customer_id,
                    'request_customer' => $customer->id
                ]);
                throw new \Exception("Transaction does not belong to this customer");
            }
            
            if ($paidAmount <= 0) {
                throw new \Exception("Payment amount must be greater than 0");
            }
            
            if ($paidAmount > $transaction->remaining_amount) {
                throw new \Exception("Payment amount exceeds remaining amount");
            }
            
            $totalPaymentAmount += $paidAmount;
            
            // Calculate new remaining amount
            $newRemainingAmount = $transaction->remaining_amount - $paidAmount;
            $paymentStatus = $newRemainingAmount <= 0 ? 'paid' : 'partial';
            
            Log::info('Updating transaction:', [
                'old_remaining' => $transaction->remaining_amount,
                'paid' => $paidAmount,
                'new_remaining' => $newRemainingAmount,
                'new_status' => $paymentStatus
            ]);
            
            // Update transaction
            $transaction->paid_amount += $paidAmount;
            $transaction->remaining_amount = $newRemainingAmount;
            $transaction->payment_status = $paymentStatus;
            $transaction->save();
            
            Log::info('Transaction updated successfully');
            
            $processedTransactions[] = [
                'transaction' => $transaction,
                'paid_amount' => $paidAmount
            ];
        }
        
        Log::info('Step 4: Total payment amount: Rs. ' . $totalPaymentAmount);
        
        // Create payment record
        Log::info('Step 5: Creating payment record...');
        try {
            $payment = new Payment();
            $payment->payment_number = Payment::generatePaymentNumber();
            $payment->customer_id = $customer->id;
            $payment->order_id = $processedTransactions[0]['transaction']->order_id ?? null;
            $payment->amount = $totalPaymentAmount;
            $payment->payment_date = $request->payment_date;
            $payment->method = $request->payment_method;
            $payment->reference = $request->reference_number;
            $payment->notes = $request->notes;
            $payment->status = 'completed';
            $payment->created_by = auth()->id();
            $payment->save();
            
            Log::info('Payment created:', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create payment: ' . $e->getMessage());
            throw new \Exception('Failed to create payment record: ' . $e->getMessage());
        }
        
        Log::info('Step 6: Linking payment to transactions...');
        foreach ($processedTransactions as $item) {
            $transaction = $item['transaction'];
            $transaction->payment_id = $payment->id;
            $transaction->save();
            Log::info('Linked transaction ' . $transaction->id . ' to payment ' . $payment->id);
            
            // Update order if exists
            if ($transaction->order_id) {
                $order = Order::find($transaction->order_id);
                if ($order) {
                    $order->credit_paid_amount += $item['paid_amount'];
                    $order->credit_remaining_amount = $transaction->remaining_amount;
                    
                    if ($order->credit_remaining_amount <= 0) {
                        $order->credit_status = 'paid';
                        $order->payment_status = 'paid';
                    } else {
                        $order->credit_status = 'partial';
                        $order->payment_status = 'partial';
                    }
                    $order->save();
                    
                    Log::info('Order updated:', [
                        'order_id' => $order->id,
                        'credit_status' => $order->credit_status
                    ]);
                }
            }
        }
        
        // Calculate new balance
        $balanceBefore = $customer->current_balance;
        $balanceAfter = $balanceBefore - $totalPaymentAmount;
        
        Log::info('Step 7: Balance update:', [
            'before' => $balanceBefore,
            'after' => $balanceAfter,
            'difference' => $totalPaymentAmount
        ]);
        
        // Create credit transaction for payment
        Log::info('Step 8: Creating credit transaction for payment...');
        try {
            $creditTransaction = CreditTransaction::create([
                'transaction_number' => CreditTransaction::generateTransactionNumber(),
                'credit_ledger_id' => $ledger->id,
                'customer_id' => $customer->id,
                'payment_id' => $payment->id,
                'transaction_type' => 'credit',
                'amount' => $totalPaymentAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => $payment->payment_number,
                'description' => 'Payment received - ' . ucfirst($request->payment_method),
                'transaction_date' => $request->payment_date,
                'payment_date' => $request->payment_date,
                'payment_status' => 'paid',
                'paid_amount' => $totalPaymentAmount,
                'remaining_amount' => 0,
                'notes' => $request->notes,
                'created_by' => auth()->id()
            ]);
            
            Log::info('Credit transaction created:', [
                'transaction_id' => $creditTransaction->id,
                'transaction_number' => $creditTransaction->transaction_number
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create credit transaction: ' . $e->getMessage());
            throw new \Exception('Failed to create credit transaction: ' . $e->getMessage());
        }
        
        // Update ledger totals
        Log::info('Step 9: Updating ledger...');
        $ledger->total_credit += $totalPaymentAmount;
        $ledger->closing_balance = $balanceAfter;
        $ledger->last_transaction_date = now();
        $ledger->save();
        
        Log::info('Ledger updated:', [
            'ledger_id' => $ledger->id,
            'total_credit' => $ledger->total_credit,
            'closing_balance' => $ledger->closing_balance
        ]);
        
        // Update customer balance
        Log::info('Step 10: Updating customer balance...');
        $customer->current_balance = $balanceAfter;
        $customer->save();
        
        Log::info('Customer balance updated:', [
            'customer_id' => $customer->id,
            'new_balance' => $customer->current_balance
        ]);
        
        DB::commit();
        
        Log::info('=== PAYMENT PROCESSING COMPLETED SUCCESSFULLY ===');
        Log::info('Final totals:', [
            'total_payment' => $totalPaymentAmount,
            'transactions_processed' => count($processedTransactions),
            'new_balance' => $balanceAfter
        ]);
        
        $message = 'Payment of Rs. ' . number_format($totalPaymentAmount, 2) . ' recorded successfully.';
        $message .= ' New balance: Rs. ' . number_format($balanceAfter, 2);
        
        return redirect()->route('admin.credit.statement', $customer->id)
            ->with('success', $message);
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('PAYMENT ERROR: ' . $e->getMessage());
        Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return redirect()->back()
            ->with('error', 'Failed to process payment: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Update order status based on payment
     */
    private function updateOrderStatus(Customer $customer, $transactionId, $paidAmount)
    {
        $transaction = CreditTransaction::find($transactionId);
        if ($transaction && $transaction->order_id) {
            $order = Order::find($transaction->order_id);
            if ($order) {
                $order->credit_paid_amount += $paidAmount;
                $order->credit_remaining_amount = $transaction->remaining_amount;
                
                if ($order->credit_remaining_amount <= 0) {
                    $order->credit_status = 'paid';
                    $order->payment_status = 'paid';
                } else {
                    $order->credit_status = 'partial';
                    $order->payment_status = 'partial';
                }
                $order->save();
            }
        }
    }

    /**
     * Enable credit for a customer
     */
    public function enableCredit(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'credit_limit' => 'required|numeric|min:0',
            'credit_due_days' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Enable credit for customer
            $customer->credit_enabled = true;
            $customer->credit_limit = $request->credit_limit;
            $customer->credit_due_days = $request->credit_due_days;
            $customer->credit_start_date = now();
            $customer->current_balance = 0;
            $customer->save();
            
            // Create credit ledger
            $ledger = CreditLedger::create([
                'ledger_number' => CreditLedger::generateLedgerNumber(),
                'customer_id' => $customer->id,
                'total_debit' => 0,
                'total_credit' => 0,
                'opening_balance' => 0,
                'closing_balance' => 0,
                'credit_limit' => $request->credit_limit,
                'status' => 'active',
                'notes' => 'Credit enabled on ' . now()->format('Y-m-d')
            ]);
            
            DB::commit();
            
            return redirect()->route('admin.credit.statement', $customer->id)
                ->with('success', 'Credit enabled successfully for ' . $customer->name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to enable credit: ' . $e->getMessage());
        }
    }

    /**
     * Disable credit for a customer
     */
    public function disableCredit(Customer $customer)
    {
        if ($customer->current_balance > 0) {
            return redirect()->back()
                ->with('error', 'Cannot disable credit. Customer has outstanding balance of Rs. ' . number_format($customer->current_balance, 2));
        }
        
        DB::beginTransaction();
        
        try {
            $customer->credit_enabled = false;
            $customer->save();
            
            $ledger = $customer->creditLedger;
            if ($ledger) {
                $ledger->status = 'closed';
                $ledger->save();
            }
            
            DB::commit();
            
            return redirect()->route('admin.credit.index')
                ->with('success', 'Credit disabled successfully for ' . $customer->name);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to disable credit: ' . $e->getMessage());
        }
    }

    public function processCreditSale(Request $request, Order $order)
    {
        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($order->customer_id);
            
            if (!$customer->credit_enabled) {
                throw new \Exception('Credit is not enabled for this customer');
            }

            // Check credit limit again
            if (!$customer->hasSufficientCredit($order->total)) {
                throw new \Exception('Insufficient credit limit. Available: ' . $customer->available_credit);
            }

            $ledger = $customer->creditLedger;
            
            // Create ledger if doesn't exist
            if (!$ledger) {
                $ledger = CreditLedger::create([
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

            $balanceBefore = $customer->current_balance;
            $balanceAfter = $balanceBefore + $order->total;
            $dueDate = now()->addDays($customer->credit_due_days ?? 30);

            // Create credit transaction
            $transaction = CreditTransaction::create([
                'transaction_number' => CreditTransaction::generateTransactionNumber(),
                'credit_ledger_id' => $ledger->id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'transaction_type' => 'debit',
                'amount' => $order->total,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference_number' => $order->order_number,
                'description' => 'Credit purchase - Order #' . $order->order_number,
                'transaction_date' => now(),
                'due_date' => $dueDate,
                'payment_status' => 'pending',
                'paid_amount' => 0,
                'remaining_amount' => $order->total,
                'created_by' => auth()->id()
            ]);

            // Update ledger
            $ledger->total_debit += $order->total;
            $ledger->closing_balance = $balanceAfter;
            $ledger->last_transaction_date = now();
            $ledger->save();

            // Update customer
            $customer->current_balance = $balanceAfter;
            $customer->save();

            // Update order with credit information
            $order->update([
                'payment_method' => 'credit',
                'credit_status' => 'pending',
                'credit_ledger_id' => $ledger->id,
                'credit_due_date' => $dueDate,
                'credit_paid_amount' => 0,
                'credit_remaining_amount' => $order->total
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credit sale processed successfully',
                'transaction' => $transaction,
                'balance' => $balanceAfter,
                'due_date' => $dueDate->format('Y-m-d'),
                'amount' => $order->total,
                'receipt_url' => route('admin.pos.receipt', $order->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process credit sale: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue transactions report
     */
    public function overdueReport(Request $request)
    {
        $query = CreditTransaction::overdue()
            ->with(['customer', 'order'])
            ->orderBy('due_date');
        
        if ($request->has('days')) {
            $days = (int) $request->days;
            $query->where('due_date', '<', now()->subDays($days));
        }
        
        $transactions = $query->paginate(20);
        
        $totalOverdue = CreditTransaction::overdue()->sum('remaining_amount');
        $totalCustomers = CreditTransaction::overdue()->distinct('customer_id')->count('customer_id');
        
        return view('admin.credit.overdue', compact('transactions', 'totalOverdue', 'totalCustomers'));
    }

    /**
     * Export customer statement as PDF
     */
    public function exportStatement(Customer $customer)
    {
        // You can implement PDF export using Laravel DomPDF or similar
        // For now, redirect back with info
        return redirect()->back()->with('info', 'PDF export feature coming soon!');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Manages "this customer is also that supplier" linking and the offset
 * (A/R - A/P swap) journal entry between them.
 */
class LinkedPartyController extends Controller
{
    use BranchScoped;

    // ── LINK ──────────────────────────────────────────────────────────────────

    /** Link a customer ↔ supplier (bidirectional). */
    public function link(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'redirect_to' => 'nullable|in:customer,supplier',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $supplier = Supplier::findOrFail($validated['supplier_id']);

        // If either side is already linked to a different party, refuse.
        if ($customer->linked_supplier_id && $customer->linked_supplier_id !== $supplier->id) {
            return back()->with('error', 'This customer is already linked to a different supplier. Unlink first.');
        }
        if ($supplier->linked_customer_id && $supplier->linked_customer_id !== $customer->id) {
            return back()->with('error', 'This supplier is already linked to a different customer. Unlink first.');
        }

        DB::transaction(function () use ($customer, $supplier) {
            $customer->update(['linked_supplier_id' => $supplier->id]);
            $supplier->update(['linked_customer_id' => $customer->id]);
        });

        $msg = "Linked: customer “{$customer->name}” ↔ supplier “{$supplier->name}”.";
        return ($validated['redirect_to'] ?? 'customer') === 'supplier'
            ? redirect()->route('suppliers.ledger', $supplier)->with('success', $msg)
            : redirect()->route('admin.customers.khata', $customer)->with('success', $msg);
    }

    /** Unlink whichever side the request comes from. Clears both directions. */
    public function unlink(Request $request)
    {
        $request->validate([
            'from_type' => 'required|in:customer,supplier',
            'from_id'   => 'required|integer',
        ]);

        if ($request->from_type === 'customer') {
            $customer = Customer::findOrFail($request->from_id);
            $supplier = $customer->linkedSupplier;
            DB::transaction(function () use ($customer, $supplier) {
                $customer->update(['linked_supplier_id' => null]);
                if ($supplier) $supplier->update(['linked_customer_id' => null]);
            });
            return redirect()->route('admin.customers.khata', $customer)
                ->with('success', 'Customer–supplier link removed.');
        }

        $supplier = Supplier::findOrFail($request->from_id);
        $customer = $supplier->linkedCustomer;
        DB::transaction(function () use ($supplier, $customer) {
            $supplier->update(['linked_customer_id' => null]);
            if ($customer) $customer->update(['linked_supplier_id' => null]);
        });
        return redirect()->route('suppliers.ledger', $supplier)
            ->with('success', 'Supplier–customer link removed.');
    }

    // ── OFFSET ────────────────────────────────────────────────────────────────

    /**
     * Post a paired offset entry on both sides. Reduces customer khata AND
     * supplier outstanding by the same amount. No cash moves; this is a pure
     * A/R ↔ A/P journal swap.
     */
    public function offset(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'supplier_id'      => 'required|exists:suppliers,id',
            'amount'           => 'required|numeric|min:0.01',
            'transaction_date' => 'required|date',
            'notes'            => 'nullable|string|max:500',
            'redirect_to'      => 'nullable|in:customer,supplier',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $supplier = Supplier::findOrFail($validated['supplier_id']);

        // Sanity: must actually be linked.
        if ($customer->linked_supplier_id !== $supplier->id || $supplier->linked_customer_id !== $customer->id) {
            return back()->with('error', 'These two are not linked. Link them first.');
        }

        $amount = round((float) $validated['amount'], 2);

        // Cap the offset at min(receivable, payable). Going past that flips a
        // balance into a debit on the other side, which would be a confusing
        // entry — better to refuse.
        $receivable = max(0.0, (float) ($customer->current_balance ?? 0));
        $payable    = max(0.0, $this->supplierBalance($supplier));
        $maxOffset  = min($receivable, $payable);

        if ($maxOffset <= 0) {
            return back()->with('error', 'Nothing to offset — one of the balances is zero or in advance.');
        }
        if ($amount > $maxOffset + 0.001) {
            return back()->with('error', 'Offset amount exceeds the smaller of the two balances (Rs. ' . number_format($maxOffset, 2) . ').');
        }

        $branchId = $this->branchId();
        $reference = 'OFFSET-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid('', true), -6));

        DB::transaction(function () use ($customer, $supplier, $amount, $validated, $branchId, $reference) {

            // Customer side: reduce A/R via a Payment with payment_type='khata_offset'
            Payment::create([
                'payment_number'   => Payment::generatePaymentNumber(),
                'payment_type'     => 'khata_offset',
                'order_id'         => null,
                'customer_id'      => $customer->id,
                'amount'           => $amount,
                'payment_date'     => $validated['transaction_date'],
                'payment_method'   => 'offset',
                'reference_number' => $reference,
                'notes'            => 'Offset against supplier #' . $supplier->id . ' (' . $supplier->name . ')'
                                    . ($validated['notes'] ? ' — ' . $validated['notes'] : ''),
                'status'           => 'completed',
                'created_by'       => auth()->id(),
            ]);
            $customer->update([
                'current_balance' => round((float) ($customer->current_balance ?? 0) - $amount, 2),
            ]);

            // Supplier side: reduce A/P via SupplierPayment with payment_method='offset'
            SupplierPayment::create([
                'payment_number' => SupplierPayment::generatePaymentNumber(),
                'supplier_id'    => $supplier->id,
                'purchase_id'    => null,
                'branch_id'      => $branchId && $branchId !== 'all' ? $branchId : null,
                'amount'         => $amount,
                'payment_date'   => $validated['transaction_date'],
                'payment_method' => 'offset',
                'direction'      => 'out',
                'notes'          => 'Offset against customer #' . $customer->id . ' (' . $customer->name . ')'
                                  . ($validated['notes'] ? ' — ' . $validated['notes'] : ''),
                'created_by'     => auth()->id(),
            ]);
        });

        $msg = 'Offset of Rs. ' . number_format($amount, 0) . " applied. Ref: {$reference}";
        return ($validated['redirect_to'] ?? 'customer') === 'supplier'
            ? redirect()->route('suppliers.ledger', $supplier)->with('success', $msg)
            : redirect()->route('admin.customers.khata', $customer)->with('success', $msg);
    }

    // ── COMBINED STATEMENT ────────────────────────────────────────────────────

    /** Merged timeline of customer + supplier ledgers for a linked pair. */
    public function combinedStatement(Request $request, Customer $customer)
    {
        $supplier = $customer->linkedSupplier;
        if (!$supplier) {
            return redirect()->route('admin.customers.khata', $customer)
                ->with('error', 'This customer is not linked to a supplier.');
        }

        $from = $request->input('from_date', now()->subMonths(6)->toDateString());
        $to   = $request->input('to_date',   now()->toDateString());

        $rows = collect();

        // Customer side: orders, khata payments, payouts, offsets
        $orders = $customer->orders()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at')->get();
        foreach ($orders as $o) {
            $rows->push(['date' => $o->created_at, 'side' => 'customer', 'kind' => 'sale',
                'reference' => $o->order_number, 'amount' => (float) $o->total,
                'effect' => +1, 'desc' => 'Sale to ' . $customer->name]);
        }

        $custPayments = Payment::where('customer_id', $customer->id)
            ->whereIn('payment_type', ['khata', 'khata_payout', 'khata_offset'])
            ->whereBetween('payment_date', [$from, $to])
            ->orderBy('payment_date')->get();
        foreach ($custPayments as $p) {
            $kind = match ($p->payment_type) {
                'khata_payout' => 'cash_out_to_customer',
                'khata_offset' => 'offset_credit',
                default        => 'khata_payment',
            };
            $effect = $p->payment_type === 'khata_payout' ? +1 : -1;
            $rows->push(['date' => $p->payment_date, 'side' => 'customer', 'kind' => $kind,
                'reference' => $p->reference_number ?? $p->payment_number, 'amount' => (float) $p->amount,
                'effect' => $effect, 'desc' => $p->notes ?: ucfirst(str_replace('_', ' ', $kind))]);
        }

        // Supplier side: purchases, supplier payments
        $purchases = $supplier->purchases()
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->orderBy('created_at')->get();
        foreach ($purchases as $pu) {
            $rows->push(['date' => $pu->created_at, 'side' => 'supplier', 'kind' => 'purchase',
                'reference' => $pu->invoice_number ?? ('PUR-' . $pu->id), 'amount' => (float) $pu->total_amount,
                'effect' => -1, 'desc' => 'Purchase from ' . $supplier->name]);
        }
        $supPayments = SupplierPayment::where('supplier_id', $supplier->id)
            ->whereBetween('payment_date', [$from, $to])
            ->orderBy('payment_date')->get();
        foreach ($supPayments as $p) {
            $isOffset = $p->payment_method === 'offset';
            $kind     = $isOffset ? 'offset_debit' : (($p->direction ?? 'out') === 'in' ? 'supplier_refund' : 'supplier_payment');
            $effect   = ($p->direction ?? 'out') === 'in' ? -1 : +1; // out reduces A/P (good for us → +1 to net)
            $rows->push(['date' => $p->payment_date, 'side' => 'supplier', 'kind' => $kind,
                'reference' => $p->payment_number, 'amount' => (float) $p->amount,
                'effect' => $effect, 'desc' => $p->notes ?: ucfirst(str_replace('_', ' ', $kind))]);
        }

        $rows = $rows->sortBy('date')->values();

        // Compute walking net (positive = they owe us net)
        $net = 0.0;
        $rows = $rows->map(function ($r) use (&$net) {
            $net += $r['effect'] * $r['amount'];
            $r['running_net'] = $net;
            return $r;
        });

        $reversed = $rows->reverse()->values();

        $custBalance = (float) ($customer->current_balance ?? 0);
        $supBalance  = $this->supplierBalance($supplier);
        $netBalance  = $custBalance - $supBalance;

        return view('admin.customers.combined-statement', [
            'customer'    => $customer,
            'supplier'    => $supplier,
            'rows'        => $reversed,
            'from'        => $from,
            'to'          => $to,
            'custBalance' => $custBalance,
            'supBalance'  => $supBalance,
            'netBalance'  => $netBalance,
        ]);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    /**
     * Compute what we owe a supplier (purchases - paid_on_purchases - unlinked_out + unlinked_in).
     * Mirrors the logic used elsewhere in the app.
     */
    private function supplierBalance(Supplier $supplier): float
    {
        $purchased = (float) $supplier->purchases()->sum('total_amount');
        $paidLinked = (float) $supplier->purchases()->sum('paid_amount');
        $paidUnlinked = (float) $supplier->payments()
            ->whereNull('purchase_id')->where('direction', 'out')->sum('amount');
        $received = (float) $supplier->payments()
            ->whereNull('purchase_id')->where('direction', 'in')->sum('amount');
        // Round to 2 dp so tiny float residues from sums never propagate.
        return round($purchased - $paidLinked - $paidUnlinked + $received, 2);
    }
}

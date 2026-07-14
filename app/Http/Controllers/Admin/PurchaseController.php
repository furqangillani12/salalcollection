<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    use BranchScoped;

    public function index()
    {
        $purchases = $this->scopeBranch(Purchase::with(['supplier', 'items.product']))->latest()->paginate(20);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers      = $this->scopeBranch(Supplier::query())->get();
        $products       = $this->scopeBranch(Product::query())->with('category')->get();
        $paymentMethods = PaymentMethod::active()->get();
        $accountBals    = $this->accountBalances();

        $supplierBalances = [];
        foreach ($suppliers as $s) {
            $totalPurchased   = Purchase::where('supplier_id', $s->id)->sum('total_amount');
            $totalPaid        = Purchase::where('supplier_id', $s->id)->sum('paid_amount');
            $supplierPayments = SupplierPayment::where('supplier_id', $s->id)->sum('amount');
            $supplierBalances[$s->id] = $totalPurchased - $totalPaid - $supplierPayments;
        }

        return view('admin.purchases.create', compact('suppliers', 'products', 'supplierBalances', 'paymentMethods', 'accountBals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'            => 'required|exists:suppliers,id',
            'purchase_date'          => 'required|date',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'payment_status'         => 'required|in:paid,partial,unpaid',
            'payment_method'         => 'nullable|string|max:50',
            'paid_amount'            => 'required|numeric|min:0',
            'discount'               => 'nullable|numeric|min:0',
            'expenses'               => 'nullable|array',
            'expenses.*.label'       => 'nullable|string|max:255',
            'expenses.*.amount'      => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
        ]);

        $branchId = $this->branchId();

        $itemsTotal = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $expensesRaw = collect($request->expenses ?? [])->filter(fn($e) => !empty($e['amount']) && $e['amount'] > 0)->values();
        $totalExpenses = $expensesRaw->sum('amount');
        $discount = (float) ($request->discount ?? 0);
        $totalAmount = $itemsTotal + $totalExpenses - $discount;

        $totalQty = collect($request->items)->sum('quantity');
        $expensePerUnit = $totalQty > 0 ? ($totalExpenses - $discount) / $totalQty : 0;

        $purchase = Purchase::create([
            'supplier_id'    => $request->supplier_id,
            'branch_id'      => $branchId !== 'all' ? $branchId : null,
            'invoice_number' => 'INV-' . Str::upper(Str::random(8)),
            'total_amount'   => $totalAmount,
            'paid_amount'    => $request->paid_amount,
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method ?? 'cash',
            'purchase_date'  => $request->purchase_date,
            'notes'          => $request->notes,
            'expenses'       => $expensesRaw->toArray(),
            'discount'       => $discount,
        ]);

        foreach ($request->items as $item) {
            $adjustedCostPrice = $item['unit_price'] + ($item['quantity'] > 0 ? $expensePerUnit : 0);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id'  => $item['product_id'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);

            // Update branch stock
            $product = Product::find($item['product_id']);
            // Update product cost_price with adjusted price (including expenses, minus discount)
            $product->update(['cost_price' => round($adjustedCostPrice, 2)]);
            if ($branchId && $branchId !== 'all') {
                $product->incrementBranchStock($branchId, $item['quantity']);
            } else {
                $product->increment('stock_quantity', $item['quantity']);
            }
        }

        return redirect()->route('purchases.show', $purchase->id)
            ->with('success', 'Purchase order created successfully');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);
        return view('admin.purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $purchase->load(['items.product', 'supplier']);
        $suppliers = $this->scopeBranch(Supplier::query())->get();
        $products  = $this->scopeBranch(Product::query())->with('category')->get();
        $existingItems = $purchase->items->map(function ($item) {
            return [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name ?? 'Unknown',
                'quantity'     => $item->quantity,
                'unit_price'   => $item->unit_price,
            ];
        });

        $paymentMethods = PaymentMethod::active()->get();
        $accountBals    = $this->accountBalances();

        $supplierBalances = [];
        foreach ($suppliers as $s) {
            $totalPurchased   = Purchase::where('supplier_id', $s->id)->sum('total_amount');
            $totalPaid        = Purchase::where('supplier_id', $s->id)->sum('paid_amount');
            $supplierPayments = SupplierPayment::where('supplier_id', $s->id)->sum('amount');
            $supplierBalances[$s->id] = $totalPurchased - $totalPaid - $supplierPayments;
        }

        return view('admin.purchases.edit', compact('purchase', 'suppliers', 'products', 'existingItems', 'supplierBalances', 'paymentMethods', 'accountBals'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'supplier_id'            => 'required|exists:suppliers,id',
            'purchase_date'          => 'required|date',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'payment_status'         => 'required|in:paid,partial,unpaid',
            'payment_method'         => 'nullable|string|max:50',
            'paid_amount'            => 'required|numeric|min:0',
            'discount'               => 'nullable|numeric|min:0',
            'expenses'               => 'nullable|array',
            'expenses.*.label'       => 'nullable|string|max:255',
            'expenses.*.amount'      => 'nullable|numeric|min:0',
            'notes'                  => 'nullable|string',
        ]);

        $branchId = $purchase->branch_id;

        // Reverse old stock
        foreach ($purchase->items as $item) {
            $product = $item->product;
            if ($branchId) {
                $product->decrementBranchStock($branchId, $item->quantity);
            } else {
                $product->decrement('stock_quantity', $item->quantity);
            }
        }

        // Delete old items
        $purchase->items()->delete();

        $itemsTotal = collect($request->items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        $expensesRaw = collect($request->expenses ?? [])->filter(fn($e) => !empty($e['amount']) && $e['amount'] > 0)->values();
        $totalExpenses = $expensesRaw->sum('amount');
        $discount = (float) ($request->discount ?? 0);
        $totalAmount = $itemsTotal + $totalExpenses - $discount;

        $totalQty = collect($request->items)->sum('quantity');
        $expensePerUnit = $totalQty > 0 ? ($totalExpenses - $discount) / $totalQty : 0;

        $purchase->update([
            'supplier_id'    => $request->supplier_id,
            'total_amount'   => $totalAmount,
            'paid_amount'    => $request->paid_amount,
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method ?? $purchase->payment_method ?? 'cash',
            'purchase_date'  => $request->purchase_date,
            'notes'          => $request->notes,
            'expenses'       => $expensesRaw->toArray(),
            'discount'       => $discount,
        ]);

        foreach ($request->items as $item) {
            $adjustedCostPrice = $item['unit_price'] + ($item['quantity'] > 0 ? $expensePerUnit : 0);

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id'  => $item['product_id'],
                'quantity'    => $item['quantity'],
                'unit_price'  => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
            ]);

            // Update branch stock
            $product = Product::find($item['product_id']);
            // Update cost price with expenses/discount distributed
            $product->update(['cost_price' => round($adjustedCostPrice, 2)]);
            if ($branchId) {
                $product->incrementBranchStock($branchId, $item['quantity']);
            } else {
                $product->increment('stock_quantity', $item['quantity']);
            }
        }

        return redirect()->route('purchases.show', $purchase->id)
            ->with('success', 'Purchase order updated successfully');
    }

    public function invoice(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product']);

        // Calculate supplier's previous balance (sum of unpaid amounts from prior purchases)
        $previousPurchases = Purchase::where('supplier_id', $purchase->supplier_id)
            ->where('id', '<', $purchase->id)
            ->get();
        $previousBalance = $previousPurchases->sum(function ($p) {
            return $p->total_amount - $p->paid_amount;
        });

        return view('admin.purchases.invoice', compact('purchase', 'previousBalance'));
    }

    public function destroy(Purchase $purchase)
    {
        $branchId = $purchase->branch_id;

        foreach ($purchase->items as $item) {
            $product = $item->product;
            if ($branchId) {
                $product->decrementBranchStock($branchId, $item->quantity);
            } else {
                $product->decrement('stock_quantity', $item->quantity);
            }
        }

        $purchase->delete();
        return redirect()->route('purchases.index')
            ->with('success', 'Purchase order deleted successfully');
    }

    // Returns current balance per payment method (cash in minus cash out)
    private function accountBalances(): array
    {
        $methods = PaymentMethod::active()->get();
        $balances = [];

        foreach ($methods as $pm) {
            $key = strtolower($pm->name);

            // Cash IN: order payments + supplier refunds received
            $in  = Order::where('payment_method', $key)->whereNotIn('status', ['cancelled'])->sum('paid_amount');
            $in += SupplierPayment::where('payment_method', $key)->where('direction', 'in')->sum('amount');
            $in += Payment::where('payment_method', $key)->where('payment_type', 'khata')->sum('amount');

            // Cash OUT: purchases paid + supplier payments out + customer payouts
            $out  = Purchase::where('payment_method', $key)->sum('paid_amount');
            $out += SupplierPayment::where('payment_method', $key)->where('direction', 'out')->sum('amount');
            $out += Payment::where('payment_method', $key)->where('payment_type', 'khata_payout')->sum('amount');

            $balances[$key] = [
                'label'   => $pm->label,
                'balance' => round($in - $out, 2),
            ];
        }

        return $balances;
    }
}

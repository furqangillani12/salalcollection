<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Refund;
use App\Models\CreditLedger;
use App\Models\CreditTransaction;
use App\Models\PaymentMethod;
use App\Models\DispatchMethod;
use App\Models\DeliveryChargeSlab;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class PosController extends Controller
{
    use BranchScoped;

    public function index()
    {
        $customers  = $this->scopeBranch(Customer::query())->get();
        $categories = $this->scopeBranch(Category::query())->get();

        $paymentMethods  = PaymentMethod::active()->get();
        $dispatchMethods = DispatchMethod::active()->get();
        $deliverySlabs = DeliveryChargeSlab::active()->orderBy('min_weight')->get();

        // Pre-format slabs grouped by dispatch_method_id for JS
        $deliverySlabsJson = $deliverySlabs->groupBy('dispatch_method_id')->map(function ($slabs) {
            return $slabs->map(function ($s) {
                return ['min' => (float) $s->min_weight, 'max' => (float) $s->max_weight, 'charge' => (float) $s->charge];
            })->values();
        });

        $paymentMethodsJson = $paymentMethods->map(function ($pm) {
            return ['name' => $pm->name, 'label' => $pm->label];
        })->values();

        $dispatchMethodsJson = $dispatchMethods->map(function ($dm) {
            return ['id' => $dm->id, 'name' => $dm->name, 'has_tracking' => $dm->has_tracking];
        })->values();

        $nextOrderNumber = Order::generateOrderNumber($this->branchId());

        return view('admin.pos.index', [
            'customers'          => $customers,
            'categories'         => $categories,
            'tax_rate'           => config('pos.tax_rate'),
            'paymentMethods'     => $paymentMethods,
            'dispatchMethods'    => $dispatchMethods,
            'paymentMethodsJson' => $paymentMethodsJson,
            'dispatchMethodsJson' => $dispatchMethodsJson,
            'deliverySlabsJson'  => $deliverySlabsJson,
            'nextOrderNumber'    => $nextOrderNumber,
        ]);
    }

    public function searchProducts(Request $request)
    {
        $query = $this->scopeBranch(Product::query())->with(['unit']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $branchId = $this->branchId();

        $products = $query->orderBy('created_at', 'desc')
                          ->paginate($request->input('per_page', 30));

        return response()->json([
            'data' => $products->map(function ($p) use ($branchId) {
                return [
                    'id'              => $p->id,
                    'name'            => $p->name,
                    'barcode'         => $p->barcode,
                    'sale_price'      => $p->sale_price,
                    'resale_price'    => $p->resale_price,
                    'wholesale_price' => $p->wholesale_price,
                    'weight'          => $p->weight ?? 0,
                    'unit'            => $p->unit->abbreviation ?? '',
                    'category_id'     => $p->category_id,
                    'stock_quantity'  => $p->getStockForBranch($branchId),
                    'reorder_level'   => $p->reorder_level ?? 5,
                    'rank'            => $p->rank,
                    'image'           => $p->image ? asset('storage/' . $p->image) : null,
                ];
            }),
            'current_page' => $products->currentPage(),
            'last_page'    => $products->lastPage(),
            'total'        => $products->total(),
        ]);
    }

    public function storeOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id'        => 'nullable|exists:customers,id',
                'items'              => 'required|array',
                'items.*.product_id'     => 'required|exists:products,id',
                'items.*.quantity'       => 'required|numeric|min:0.01',
                'items.*.unit_price'     => 'nullable|numeric|min:0',
                'items.*.original_price' => 'nullable|numeric|min:0',
                'items.*.line_discount'  => 'nullable|numeric|min:0',
                'payment_method'         => 'required|string',
                'notes'                  => 'nullable|string|max:1000',
                'paid_amount'        => 'nullable|numeric|min:0',
                'dispatch_method'    => 'nullable|string',
                'tracking_id'        => 'nullable|string',
                'delivery_charges'   => 'nullable|numeric|min:0',
                'tax_rate'           => 'nullable|numeric|min:0',
                'tax_type'           => 'nullable|string|in:percent,fixed',
                'discount'           => 'nullable|numeric|min:0',
                'discount_label'     => 'nullable|string|max:255',
                'order_date'         => 'nullable|date',
            ]);

            $branchId = $this->branchId();

            DB::beginTransaction();

            $customer     = null;
            $customerType = 'walkin';

            if (!empty($validated['customer_id'])) {
                $customer     = Customer::findOrFail($validated['customer_id']);
                $customerType = $customer->customer_type ?? 'customer';
            }

            $totalWeight = 0;
            $subtotal    = 0;
            $orderItems  = [];

            foreach ($validated['items'] as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $unitPrice = !empty($item['unit_price'])
                    ? (float) $item['unit_price']
                    : $this->getPriceForCustomerType($product, $customerType);
                $itemTotal = $unitPrice * $item['quantity'];
                $subtotal += $itemTotal;

                if (!empty($product->weight)) {
                    $totalWeight += $product->weight * $item['quantity'];
                }

                // Check branch stock
                $branchStock = $product->getStockForBranch($branchId);
                if ($branchStock < $item['quantity']) {
                    throw new \Exception("Not enough stock for {$product->name} (Available: {$branchStock})");
                }

                $orderItems[] = [
                    'product'        => $product,
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $unitPrice,
                    'original_price' => !empty($item['original_price']) ? (float)$item['original_price'] : null,
                    'line_discount'  => !empty($item['line_discount'])  ? (float)$item['line_discount']  : 0,
                    'total'          => $itemTotal,
                ];
            }

            // ── Calculate totals (tax applies on subtotal - discount + delivery)
            $taxRate         = $validated['tax_rate'] ?? config('pos.tax_rate', 0);
            $taxType         = $validated['tax_type'] ?? 'percent';
            $discount        = $validated['discount'] ?? 0;
            $deliveryCharges = $validated['delivery_charges'] ?? 0;
            $afterDiscount   = $subtotal - $discount;
            $taxableAmount   = $afterDiscount + $deliveryCharges;
            $tax             = $taxType === 'percent' ? $taxableAmount * ($taxRate / 100) : $taxRate;
            $total           = $afterDiscount + $tax + $deliveryCharges;

            // ── Partial payment logic
            $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== null
                ? (float) $validated['paid_amount']
                : $total;

            $previousBalance = 0;
            if ($customer) {
                $previousBalance = (float) ($customer->current_balance ?? 0);
            }

            $balanceOnOrder    = max(0, $total - $paidAmount);
            $newRunningBalance = $previousBalance + $total - $paidAmount;
            $paymentStatus     = $balanceOnOrder <= 0 ? 'paid' : 'partial';

            // ── Create order with branch_id
            $order = Order::create([
                'order_number'     => Order::generateOrderNumber($branchId),
                'customer_id'      => $customer ? $customer->id : null,
                'customer_type'    => $customerType,
                'user_id'          => auth()->id(),
                'branch_id'        => $branchId !== 'all' ? $branchId : null,
                'order_type'       => 'pos',
                'payment_method'   => $validated['payment_method'],
                'status'           => 'completed',
                'notes'            => $validated['notes'] ?? null,
                'tax_rate'         => $taxRate,
                'tax_type'         => $taxType,
                'tax'              => $tax,
                'dispatch_method'  => $validated['dispatch_method'] ?? null,
                'tracking_id'      => $validated['tracking_id'] ?? null,
                'discount'         => $discount,
                'discount_label'   => $validated['discount_label'] ?? null,
                'delivery_charges' => $deliveryCharges,
                'weight'           => $totalWeight,
                'subtotal'         => $subtotal,
                'total'            => $total,
                'paid_amount'      => $paidAmount,
                'previous_balance' => $previousBalance,
                'balance_amount'   => $balanceOnOrder,
            ]);

            // Backdate the order if order_date was provided
            if (!empty($validated['order_date'])) {
                $time = now()->format('H:i:s');
                $order->created_at = $validated['order_date'] . ' ' . $time;
                $order->save();
            }

            // ── Create order items & update BRANCH stock
            foreach ($orderItems as $itemData) {
                $product = $itemData['product'];

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'quantity'       => $itemData['quantity'],
                    'unit_price'     => $itemData['unit_price'],
                    'original_price' => $itemData['original_price'],
                    'line_discount'  => $itemData['line_discount'],
                    'total_price'    => $itemData['total'],
                ]);

                if ($product->track_inventory && $branchId && $branchId !== 'all') {
                    $product->decrementBranchStock($branchId, $itemData['quantity']);
                }
            }

            // ── Create payment record
            if ($customer && $paidAmount > 0) {
                Payment::create([
                    'payment_number'   => Payment::generatePaymentNumber(),
                    'order_id'         => $order->id,
                    'customer_id'      => $customer->id,
                    'amount'           => $paidAmount,
                    'payment_date'     => now(),
                    'payment_method'   => $validated['payment_method'],
                    'reference_number' => $validated['tracking_id'] ?? null,
                    'status'           => 'completed',
                    'created_by'       => auth()->id(),
                ]);
            }

            // ── Update customer running balance
            if ($customer) {
                $customer->current_balance = $newRunningBalance;
                $customer->save();
            }

            DB::commit();

            $order->load('items');

            return response()->json([
                'success'           => true,
                'message'           => 'Order created successfully',
                'order_id'          => $order->id,
                'order_number'      => $order->order_number,
                'next_order_number' => Order::generateOrderNumber($branchId),
                'total'             => $order->total,
                'paid_amount'       => $paidAmount,
                'balance_amount'    => $balanceOnOrder,
                'previous_balance'  => $previousBalance,
                'new_balance'       => $newRunningBalance,
                'receipt_url'       => route('admin.pos.receipt', $order),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function getPriceForCustomerType($product, $customerType)
    {
        switch ($customerType) {
            case 'reseller':
                return $product->resale_price ?? $product->sale_price;
            case 'wholesale':
                return $product->wholesale_price ?? $product->sale_price;
            default:
                return $product->sale_price;
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

            $ledger = $customer->creditLedger;

            if (!$ledger) {
                $ledger = CreditLedger::create([
                    'ledger_number'   => CreditLedger::generateLedgerNumber(),
                    'customer_id'     => $customer->id,
                    'total_debit'     => 0,
                    'total_credit'    => 0,
                    'opening_balance' => 0,
                    'closing_balance' => 0,
                    'credit_limit'    => $customer->credit_limit,
                    'status'          => 'active',
                    'notes'           => 'Credit enabled on ' . now()->format('Y-m-d'),
                ]);
            }

            $balanceBefore = $customer->current_balance;
            $balanceAfter  = $balanceBefore + $order->total;
            $dueDate       = now()->addDays($customer->credit_due_days ?? 30);

            CreditTransaction::create([
                'transaction_number' => CreditTransaction::generateTransactionNumber(),
                'credit_ledger_id'   => $ledger->id,
                'customer_id'        => $customer->id,
                'order_id'           => $order->id,
                'transaction_type'   => 'debit',
                'amount'             => $order->total,
                'balance_before'     => $balanceBefore,
                'balance_after'      => $balanceAfter,
                'reference_number'   => $order->order_number,
                'description'        => 'Credit purchase - Order #' . $order->order_number,
                'transaction_date'   => now(),
                'due_date'           => $dueDate,
                'payment_status'     => 'pending',
                'items'              => $order->items->map(function ($item) {
                    return [
                        'product_id'   => $item->product_id,
                        'product_name' => $item->product->name ?? null,
                        'quantity'     => $item->quantity,
                        'price'        => $item->unit_price,
                        'total'        => $item->total_price,
                    ];
                }),
                'paid_amount'      => 0,
                'remaining_amount' => $order->total,
                'created_by'       => auth()->id(),
            ]);

            $ledger->total_debit           += $order->total;
            $ledger->closing_balance        = $balanceAfter;
            $ledger->last_transaction_date  = now();
            $ledger->save();

            $customer->current_balance = $balanceAfter;
            $customer->save();

            $order->update([
                'payment_method'           => 'credit',
                'credit_status'            => 'pending',
                'credit_ledger_id'         => $ledger->id,
                'credit_due_date'          => $dueDate,
                'credit_paid_amount'       => 0,
                'credit_remaining_amount'  => $order->total,
            ]);

            DB::commit();

            return response()->json([
                'success'     => true,
                'message'     => 'Credit sale processed successfully',
                'transaction' => $order,
                'balance'     => $balanceAfter,
                'due_date'    => $dueDate->format('Y-m-d'),
                'amount'      => $order->total,
                'receipt_url' => route('admin.pos.receipt', $order->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process credit sale: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function editOrder(Order $order)
    {
        $order->load('items.product.unit', 'customer');
        $products   = $this->scopeBranch(Product::query())->with(['category', 'unit'])->orderBy('created_at', 'desc')->get();
        $customers  = $this->scopeBranch(Customer::query())->get();
        $categories = $this->scopeBranch(Category::query())->get();

        $paymentMethods  = PaymentMethod::active()->get();
        $dispatchMethods = DispatchMethod::active()->get();

        return view('admin.pos.edit', [
            'order'           => $order,
            'products'        => $products,
            'customers'       => $customers,
            'categories'      => $categories,
            'tax_rate'        => config('pos.tax_rate'),
            'paymentMethods'  => $paymentMethods,
            'dispatchMethods' => $dispatchMethods,
        ]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        try {
            $validated = $request->validate([
                'customer_id'        => 'nullable|exists:customers,id',
                'items'              => 'required|array',
                'items.*.product_id'     => 'required|exists:products,id',
                'items.*.quantity'       => 'required|numeric|min:0.01',
                'items.*.unit_price'     => 'nullable|numeric|min:0',
                'items.*.original_price' => 'nullable|numeric|min:0',
                'items.*.line_discount'  => 'nullable|numeric|min:0',
                'payment_method'         => 'required|string',
                'paid_amount'            => 'nullable|numeric|min:0',
                'dispatch_method'    => 'nullable|string',
                'tracking_id'        => 'nullable|string',
                'delivery_charges'   => 'nullable|numeric|min:0',
                'tax_rate'           => 'nullable|numeric|min:0',
                'discount'           => 'nullable|numeric|min:0',
                'discount_label'     => 'nullable|string|max:255',
                'order_date'         => 'nullable|date',
                'notes'              => 'nullable|string|max:1000',
            ]);

            $branchId = $order->branch_id ?? $this->branchId();

            DB::beginTransaction();

            // Restore old branch stock
            foreach ($order->items as $oldItem) {
                if ($oldItem->product && $oldItem->product->track_inventory && $branchId && $branchId !== 'all') {
                    $oldItem->product->incrementBranchStock($branchId, $oldItem->quantity);
                }
            }

            $order->items()->delete();

            $customer     = null;
            $customerType = 'walkin';

            if (!empty($validated['customer_id'])) {
                $customer     = Customer::findOrFail($validated['customer_id']);
                $customerType = $customer->customer_type ?? 'customer';
            }

            // Reverse old balance effect on customer
            $oldCustomer = $order->customer;
            $oldNetEffect = ($order->total ?? 0) - ($order->paid_amount ?? 0);
            if ($oldCustomer) {
                $oldCustomer->current_balance = $oldCustomer->current_balance - $oldNetEffect;
                $oldCustomer->save();
            }

            if ($customer && $oldCustomer && $customer->id === $oldCustomer->id) {
                $customer->refresh();
            }

            $totalWeight = 0;
            $subtotal    = 0;
            $orderItems  = [];

            foreach ($validated['items'] as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $unitPrice = !empty($item['unit_price'])
                    ? (float) $item['unit_price']
                    : $this->getPriceForCustomerType($product, $customerType);
                $itemTotal = $unitPrice * $item['quantity'];
                $subtotal += $itemTotal;

                if (!empty($product->weight)) {
                    $totalWeight += $product->weight * $item['quantity'];
                }

                $branchStock = $product->getStockForBranch($branchId);
                if ($branchStock < $item['quantity']) {
                    throw new \Exception("Not enough stock for {$product->name} (Available: {$branchStock})");
                }

                $orderItems[] = [
                    'product'        => $product,
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $unitPrice,
                    'original_price' => !empty($item['original_price']) ? (float)$item['original_price'] : null,
                    'line_discount'  => !empty($item['line_discount'])  ? (float)$item['line_discount']  : 0,
                    'total'          => $itemTotal,
                ];
            }

            $taxRate         = $validated['tax_rate'] ?? config('pos.tax_rate', 0);
            $discount        = $validated['discount'] ?? 0;
            $deliveryCharges = $validated['delivery_charges'] ?? 0;
            $afterDiscount   = $subtotal - $discount;
            $taxableAmount   = $afterDiscount + $deliveryCharges;
            $tax             = $taxableAmount * ($taxRate / 100);
            $total           = $afterDiscount + $tax + $deliveryCharges;

            $paidAmount = isset($validated['paid_amount']) && $validated['paid_amount'] !== null
                ? (float) $validated['paid_amount']
                : $total;

            $balanceOnOrder = max(0, $total - $paidAmount);
            $newNetEffect   = $total - $paidAmount;

            $updateData = [
                'customer_id'      => $customer ? $customer->id : null,
                'customer_type'    => $customerType,
                'payment_method'   => $validated['payment_method'],
                'notes'            => $validated['notes'] ?? null,
                'tax_rate'         => $taxRate,
                'tax'              => $tax,
                'dispatch_method'  => $validated['dispatch_method'] ?? null,
                'tracking_id'      => $validated['tracking_id'] ?? null,
                'discount'         => $discount,
                'discount_label'   => $validated['discount_label'] ?? null,
                'delivery_charges' => $deliveryCharges,
                'weight'           => $totalWeight,
                'subtotal'         => $subtotal,
                'total'            => $total,
                'paid_amount'      => $paidAmount,
                'balance_amount'   => $balanceOnOrder,
                // Note: previous_balance is NOT updated — it's a historical snapshot
                // and is now computed dynamically in receipts via computePreviousBalance()
            ];
            $order->update($updateData);

            if (!empty($validated['order_date'])) {
                // Preserve the time portion of original created_at.
                // created_at is not in $fillable, so assign directly and save
                // (mass assignment via update() would silently drop it).
                $time = \Carbon\Carbon::parse($order->created_at)->format('H:i:s');
                $order->created_at = $validated['order_date'] . ' ' . $time;
                $order->save();
            }

            foreach ($orderItems as $itemData) {
                $product = $itemData['product'];

                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $product->id,
                    'quantity'       => $itemData['quantity'],
                    'unit_price'     => $itemData['unit_price'],
                    'original_price' => $itemData['original_price'],
                    'line_discount'  => $itemData['line_discount'],
                    'total_price'    => $itemData['total'],
                ]);

                if ($product->track_inventory && $branchId && $branchId !== 'all') {
                    $product->decrementBranchStock($branchId, $itemData['quantity']);
                }
            }

            // Update customer balance: add new net effect (already reversed old effect above)
            if ($customer) {
                $customer->current_balance = $customer->current_balance + $newNetEffect;
                $customer->save();
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Order updated successfully',
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'total'        => $order->total,
                'receipt_url'  => route('admin.pos.receipt', $order),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadReceiptPdf(Order $order)
    {
        $order->load(['branch', 'items.product.unit']);
        $pdf = Pdf::loadView('admin.pos.receipt-pdf', compact('order'));
        return $pdf->download("Receipt-{$order->order_number}.pdf");
    }

    public function downloadReceipt(Order $order)
    {
        $order->load(['branch', 'items.product.unit']);
        $pdf = Pdf::loadView('admin.pos.receipt', compact('order'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('receipt-' . $order->order_number . '.pdf');
    }

    public function showReceipt(Order $order)
    {
        $order->load(['branch', 'items.product.unit', 'refunds.user']);
        return view('admin.pos.receipt', compact('order'));
    }

    public function thermalReceipt(Order $order)
    {
        $order->load(['items.product.unit', 'customer', 'user', 'branch']);
        return view('admin.pos.receipt-thermal', compact('order'));
    }

    public function processRefund(Request $request, Order $order)
    {
        if (!$order->isRefundable()) {
            return redirect()->route('admin.pos.receipt', $order)
                ->with('error', 'This order cannot be refunded. It must be completed and within 30 days.');
        }

        $validator = \Validator::make($request->all(), [
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.name'       => 'nullable|string',
            'reason'             => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.pos.receipt', $order)
                ->withErrors($validator)
                ->withInput();
        }

        $branchId = $order->branch_id;

        // Only process items that were checked (have selected=1 AND a quantity)
        $refundItems = collect($request->items)
            ->filter(fn($item) => !empty($item['selected']) && !empty($item['quantity']) && (float)$item['quantity'] > 0)
            ->map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'name'       => $item['name'] ?? '',
                    'quantity'   => (float) $item['quantity'],
                    'unit_price' => (float) $item['unit_price'],
                    'total'      => round((float) $item['quantity'] * (float) $item['unit_price'], 2),
                ];
            })
            ->values();

        if ($refundItems->isEmpty()) {
            return redirect()->route('admin.pos.receipt', $order)
                ->with('error', 'Please select at least one item to return.');
        }

        $rawRefundTotal = $refundItems->sum('total');

        // When an order-level discount exists (e.g. package discount), refund proportionally
        // so the customer gets back what they actually paid, not the retail item total.
        if (($order->discount ?? 0) > 0 && ($order->subtotal ?? 0) > 0) {
            $refundAmount = round($rawRefundTotal / $order->subtotal * $order->total, 2);
        } else {
            $refundAmount = $rawRefundTotal;
        }

        $remainingRefundable = $order->remainingRefundable();
        if ($refundAmount <= 0 || $refundAmount > $remainingRefundable + 1) {
            return redirect()->route('admin.pos.receipt', $order)
                ->with('error', 'Invalid refund amount (Rs. ' . number_format($refundAmount, 0) . '). Maximum returnable is Rs. ' . number_format($remainingRefundable, 0) . '.');
        }

        DB::transaction(function () use ($request, $order, $branchId, $refundItems, $refundAmount) {
            Refund::create([
                'refund_number' => Refund::generateRefundNumber(),
                'order_id'      => $order->id,
                'user_id'       => auth()->id(),
                'amount'        => $refundAmount,
                'reason'        => $request->reason,
                'items'         => $refundItems->toArray(),
                'status'        => 'completed',
            ]);

            // Mark order as refunded if full amount returned, otherwise stays completed
            $totalRefunded = $order->refunds()->where('status', 'completed')->sum('amount') + $refundAmount;
            $order->update([
                'status' => $totalRefunded >= $order->total
                    ? Order::STATUS_REFUNDED
                    : Order::STATUS_COMPLETED,
            ]);

            // Reduce customer balance
            if ($order->customer_id) {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->current_balance = max(0, $customer->current_balance - $refundAmount);
                    $customer->save();
                }
            }

            // Return selected items to inventory
            if ($request->boolean('return_to_inventory')) {
                foreach ($refundItems as $ri) {
                    $product = Product::find($ri['product_id']);
                    if ($product && $product->track_inventory && $branchId) {
                        $product->incrementBranchStock($branchId, $ri['quantity']);
                        $product->inventoryLogs()->create([
                            'action'          => 'refund_return',
                            'quantity_change' => $ri['quantity'],
                            'branch_id'       => $branchId,
                            'notes'           => 'Returned via refund of Order #' . $order->order_number,
                            'user_id'         => auth()->id(),
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.pos.receipt', $order)
            ->with('success', 'Return processed. Refund amount: Rs. ' . number_format($refundAmount, 0) . '. ' . ($request->boolean('return_to_inventory') ? 'Items returned to inventory.' : ''));
    }

    public function updateRefund(Request $request, Refund $refund)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $refund->update(['reason' => $request->reason]);

        return redirect()->route('admin.pos.returns')
            ->with('success', 'Return ' . $refund->refund_number . ' updated.');
    }

    public function voidRefund(Request $request, Refund $refund)
    {
        if ($refund->status !== 'completed') {
            return back()->with('error', 'Only completed refunds can be voided.');
        }

        DB::transaction(function () use ($refund) {
            $order = $refund->order;

            // Restore customer balance
            if ($order?->customer_id) {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->current_balance += $refund->amount;
                    $customer->save();
                }
            }

            // Reverse inventory if items exist
            if ($refund->items && $order) {
                foreach ($refund->items as $ri) {
                    $product = Product::find($ri['product_id'] ?? null);
                    if ($product && $product->track_inventory && $order->branch_id) {
                        $product->decrementBranchStock($order->branch_id, $ri['quantity'] ?? 0);
                    }
                }
            }

            // Restore order status to completed
            if ($order && $order->status === Order::STATUS_REFUNDED) {
                $order->update(['status' => Order::STATUS_COMPLETED]);
            }

            $refund->update(['status' => 'voided']);
        });

        return redirect()->route('admin.pos.returns')
            ->with('success', 'Return ' . $refund->refund_number . ' has been voided and reversed.');
    }

    public function returnsList(Request $request)
    {
        $query = Refund::with(['order.customer', 'user'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->whereHas('order', function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            })->orWhere('refund_number', 'like', "%{$search}%");
        }

        if ($start = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $start);
        }
        if ($end = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $end);
        }

        $refunds = $query->paginate(25)->withQueryString();
        $totalAmount = Refund::where('status', 'completed')->sum('amount');

        return view('admin.pos.returns', compact('refunds', 'totalAmount'));
    }

    public function cancelOrder(Order $order)
    {
        if ($order->status === Order::STATUS_CANCELLED) {
            return back()->with('error', 'Order is already cancelled.');
        }

        $branchId = $order->branch_id;

        DB::transaction(function () use ($order, $branchId) {
            // Restore branch stock
            foreach ($order->items as $item) {
                if ($item->product && $branchId) {
                    $item->product->incrementBranchStock($branchId, $item->quantity);
                }
            }

            if ($order->customer_id && $order->balance_amount > 0) {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->current_balance = max(0, $customer->current_balance - $order->balance_amount);
                    $customer->save();
                }
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);
        });

        return back()->with('success', 'Order #' . $order->order_number . ' has been cancelled and stock restored.');
    }

    public function deleteOrder(Order $order)
    {
        $branchId = $order->branch_id;

        DB::transaction(function () use ($order, $branchId) {
            if ($order->status !== Order::STATUS_CANCELLED && $order->status !== Order::STATUS_REFUNDED) {
                foreach ($order->items as $item) {
                    if ($item->product && $branchId) {
                        $item->product->incrementBranchStock($branchId, $item->quantity);
                    }
                }
            }

            if ($order->customer_id && $order->balance_amount > 0 && $order->status !== Order::STATUS_CANCELLED) {
                $customer = Customer::find($order->customer_id);
                if ($customer) {
                    $customer->current_balance = max(0, $customer->current_balance - $order->balance_amount);
                    $customer->save();
                }
            }

            $order->items()->delete();
            $order->payments()->delete();
            $order->delete();
        });

        return redirect()->route('admin.reports.sales')->with('success', 'Order deleted successfully.');
    }
}

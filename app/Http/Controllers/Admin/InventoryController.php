<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\BranchProductStock;
use App\Models\InventoryLog;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    use BranchScoped;

    public function index()
    {
        $branchId = $this->branchId();
        $products = $this->scopeBranch(Product::query())->with('category')->filter(request(['search']))->paginate(20);

        // Attach branch stock info for the view
        foreach ($products as $product) {
            $product->branch_stock = $product->getStockForBranch($branchId);
        }

        return view('admin.inventory.index', compact('products'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'action'     => 'required|in:add,remove,set',
            'quantity'   => 'required|integer|min:1',
            'notes'      => 'required|string|max:255',
        ]);

        $product  = Product::find($validated['product_id']);
        $branchId = $this->branchId();

        if ($branchId && $branchId !== 'all') {
            // Operate on branch stock
            $entry = BranchProductStock::firstOrCreate(
                ['branch_id' => $branchId, 'product_id' => $product->id],
                ['stock_quantity' => 0, 'reorder_level' => $product->reorder_level ?? 10]
            );

            switch ($validated['action']) {
                case 'add':
                    $entry->increment('stock_quantity', $validated['quantity']);
                    $change = $validated['quantity'];
                    break;
                case 'remove':
                    $entry->decrement('stock_quantity', $validated['quantity']);
                    $change = -$validated['quantity'];
                    break;
                case 'set':
                    $change = $validated['quantity'] - $entry->stock_quantity;
                    $entry->update(['stock_quantity' => $validated['quantity']]);
                    break;
            }
        } else {
            // Fallback: operate on product's global stock
            switch ($validated['action']) {
                case 'add':
                    $product->increment('stock_quantity', $validated['quantity']);
                    $change = $validated['quantity'];
                    break;
                case 'remove':
                    $product->decrement('stock_quantity', $validated['quantity']);
                    $change = -$validated['quantity'];
                    break;
                case 'set':
                    $change = $validated['quantity'] - $product->stock_quantity;
                    $product->update(['stock_quantity' => $validated['quantity']]);
                    break;
            }
        }

        $product->inventoryLogs()->create([
            'action'          => $validated['action'],
            'quantity_change' => $change,
            'branch_id'       => $branchId !== 'all' ? $branchId : null,
            'notes'           => $validated['notes'],
            'user_id'         => auth()->id(),
        ]);

        return back()->with('success', 'Inventory adjusted successfully');
    }

    public function logs()
    {
        $query = InventoryLog::with(['product', 'user'])->latest();

        if (!$this->isAllBranches()) {
            $query->where('branch_id', $this->branchId());
        }

        $logs = $query->filter(request()->only(['product_id', 'action']))->paginate(20);
        $products = $this->scopeBranch(Product::query())->get();

        return view('admin.inventory.logs', compact('logs', 'products'));
    }

    public function lowStock()
    {
        if ($this->isAllBranches()) {
            $products = Product::whereColumn('stock_quantity', '<=', 'reorder_level')->paginate(20);
        } else {
            $branchId = $this->branchId();
            $productIds = BranchProductStock::where('branch_id', $branchId)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->pluck('product_id');
            $products = $this->scopeBranch(Product::query())->whereIn('id', $productIds)->paginate(20);

            foreach ($products as $product) {
                $product->branch_stock = $product->getStockForBranch($branchId);
            }
        }

        return view('admin.inventory.low-stock', compact('products'));
    }
}

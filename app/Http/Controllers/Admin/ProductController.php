<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\BranchProductStock;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    use BranchScoped;
    public function index()
    {
        $products = $this->scopeBranch(Product::query())
            ->with(['category', 'unit'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Attach branch stock for display
        $branchId = $this->branchId();
        foreach ($products as $product) {
            $product->branch_stock = $product->getStockForBranch($branchId);
        }

        return view('admin.products.index', compact('products'));
    }


    public function create()
    {
        $categories = $this->scopeBranch(Category::query())->get();
        $units = Unit::where('is_active', true)->get();
        return view('admin.products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'barcode'          => 'nullable|string|unique:products',
            'category_id'      => 'required|exists:categories,id',
            'unit_id'          => 'nullable|exists:units,id',
            'description'      => 'nullable|string',
            'rank'             => 'nullable|string|max:50', 
            'sale_price'       => 'required|numeric|min:0',
            'resale_price'     => 'required|numeric|min:0',
            'wholesale_price'  => 'required|numeric|min:0',
            'cost_price'       => 'required|numeric|min:0',
            'weight_kg'        => 'nullable|numeric|min:0|decimal:0,4',
            'weight_g'         => 'nullable|integer|min:0',
            'stock_quantity'   => 'required|numeric|min:0',
            'reorder_level'    => 'required|numeric|min:0',
            'image'            => 'nullable|image|max:5120',
            'is_active'        => 'boolean',
            'track_inventory'  => 'boolean',
            'show_on_website'  => 'boolean',
        ]);

        // Checkbox: present only when ticked, so resolve explicitly.
        $validated['show_on_website'] = $request->boolean('show_on_website');

        if (!empty($validated['weight_kg'])) {
            $weight = $validated['weight_kg'];
        } elseif (!empty($validated['weight_g'])) {
            $weight = $validated['weight_g'] / 1000;
        } else {
            $weight = null;
        }
        unset($validated['weight_kg'], $validated['weight_g']);
        $validated['weight'] = $weight;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Assign to current branch
        $branchId = $this->branchId();
        if ($branchId && $branchId !== 'all') {
            $validated['branch_id'] = $branchId;
        }

        $product = Product::create($validated);

        // Create branch stock entry
        if ($branchId && $branchId !== 'all') {
            BranchProductStock::create([
                'branch_id'      => $branchId,
                'product_id'     => $product->id,
                'stock_quantity' => $validated['stock_quantity'],
                'reorder_level'  => $validated['reorder_level'],
            ]);
        }

        // Log inventory change
        $product->inventoryLogs()->create([
            'action'          => 'initial',
            'quantity_change' => $validated['stock_quantity'],
            'branch_id'       => $branchId !== 'all' ? $branchId : null,
            'notes'           => 'Initial stock entry',
            'user_id'         => auth()->id(),
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $categories = $this->scopeBranch(Category::query())->get();
        $units = Unit::where('is_active', true)->get();
        $product->branch_stock = $product->getStockForBranch($this->branchId());
        return view('admin.products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'barcode'          => 'nullable|string|unique:products,barcode,'.$product->id,
            'category_id'      => 'required|exists:categories,id',
            'unit_id'          => 'nullable|exists:units,id', 
            'description'      => 'nullable|string',
            'rank'             => 'nullable|string|max:50', 
            'sale_price'       => 'required|numeric|min:0',
            'resale_price'     => 'required|numeric|min:0',
            'wholesale_price'  => 'required|numeric|min:0',
            'cost_price'       => 'required|numeric|min:0',
            'weight_kg'        => 'nullable|numeric|min:0|decimal:0,4',
            'weight_g'         => 'nullable|integer|min:0',
            'stock_quantity'   => 'required|numeric|min:0',
            'reorder_level'    => 'required|numeric|min:0',
            'image'            => 'nullable|image|max:5120',
            'is_active'        => 'boolean',
            'track_inventory'  => 'boolean',
            'show_on_website'  => 'boolean',
        ]);

        // Checkbox: present only when ticked, so resolve explicitly.
        $validated['show_on_website'] = $request->boolean('show_on_website');

        if (!empty($validated['weight_kg'])) {
        $weight = $validated['weight_kg'];
        } elseif (!empty($validated['weight_g'])) {
            $weight = $validated['weight_g'] / 1000;
        } else {
            $weight = null;
        }

        unset($validated['weight_kg'], $validated['weight_g']);
        $validated['weight'] = $weight;
        
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        // Sync branch stock if editing from a specific branch
        $branchId = $this->branchId();
        if ($branchId && $branchId !== 'all') {
            BranchProductStock::updateOrCreate(
                ['branch_id' => $branchId, 'product_id' => $product->id],
                ['stock_quantity' => $validated['stock_quantity'], 'reorder_level' => $validated['reorder_level']]
            );
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully');
    }

    /** Quick toggle of website visibility from the products list. */
    public function toggleWebsite(Product $product)
    {
        $product->update(['show_on_website' => ! $product->show_on_website]);

        return back()->with('success', $product->show_on_website
            ? "\"{$product->name}\" is now visible on the website."
            : "\"{$product->name}\" is now hidden from the website.");
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully');
    }

    // Import form
    public function showImportForm()
    {
        return view('admin.products.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Log::info('Starting product import');
            $import = new ProductsImport();
            Excel::import($import, $request->file('file'));

            return back()->with('success', 'Imported '.$import->getRowCount().' products');
        } catch (\Exception $e) {
            Log::error('Import failed: '.$e->getMessage());
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'products_'.now()->format('Ymd_His').'.xlsx');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\Product;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use BranchScoped;

    public function index()
    {
        $packages = $this->scopeBranch(Package::with('items.product'))->latest()->get();
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $products = $this->scopeBranch(Product::query())->orderBy('name')->get();
        return view('admin.packages.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'code'                   => 'nullable|string|max:100',
            'sale_price'             => 'required|numeric|min:0',
            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
        ]);

        $branchId = $this->branchId();

        $package = Package::create([
            'branch_id'       => $branchId !== 'all' ? $branchId : null,
            'name'            => $request->name,
            'code'            => $request->code,
            'sale_price'      => $request->sale_price,
            'resale_price'    => $request->filled('resale_price') ? $request->resale_price : null,
            'wholesale_price' => $request->filled('wholesale_price') ? $request->wholesale_price : null,
            'is_active'       => true,
        ]);

        foreach ($request->items as $item) {
            PackageItem::create([
                'package_id' => $package->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
            ]);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package "' . $package->name . '" created successfully.');
    }

    public function edit(Package $package)
    {
        $package->load('items.product');
        $products = $this->scopeBranch(Product::query())->orderBy('name')->get();
        return view('admin.packages.edit', compact('package', 'products'));
    }

    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'code'               => 'nullable|string|max:100',
            'sale_price'         => 'required|numeric|min:0',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
        ]);

        $package->update([
            'name'            => $request->name,
            'code'            => $request->code,
            'sale_price'      => $request->sale_price,
            'resale_price'    => $request->filled('resale_price') ? $request->resale_price : null,
            'wholesale_price' => $request->filled('wholesale_price') ? $request->wholesale_price : null,
        ]);

        $package->items()->delete();
        foreach ($request->items as $item) {
            PackageItem::create([
                'package_id' => $package->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
            ]);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package "' . $package->name . '" updated.');
    }

    public function toggle(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        return back()->with('success', "Package " . ($package->is_active ? 'activated' : 'deactivated') . ".");
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return back()->with('success', 'Package deleted.');
    }

    // API: used by POS to load packages for search
    public function apiList(Request $request)
    {
        $packages = $this->scopeBranch(Package::active()->with('items.product'))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('code', 'like', '%'.$request->search.'%'))
            ->get();

        return response()->json($packages->map(function ($pkg) {
            return [
                'id'              => $pkg->id,
                'name'            => $pkg->name,
                'code'            => $pkg->code,
                'sale_price'      => $pkg->sale_price,
                'resale_price'    => $pkg->resale_price ?? $pkg->sale_price,
                'wholesale_price' => $pkg->wholesale_price ?? $pkg->sale_price,
                'cost_price'      => $pkg->cost_price,
                'retail_total'    => $pkg->retail_total,
                'discount_amount' => $pkg->discount_amount,
                'items'      => $pkg->items->map(fn($i) => [
                    'product_id'   => $i->product_id,
                    'name'         => $i->product?->name ?? 'Unknown',
                    'quantity'     => $i->quantity,
                    'sale_price'   => $i->product?->sale_price ?? 0,
                    'resale_price' => $i->product?->resale_price ?? $i->product?->sale_price ?? 0,
                    'wholesale_price' => $i->product?->wholesale_price ?? $i->product?->sale_price ?? 0,
                    'cost_price'   => $i->product?->cost_price ?? 0,
                    'weight'       => $i->product?->weight ?? 0,
                    'unit'         => $i->product?->unit?->abbreviation ?? '',
                ]),
            ];
        }));
    }
}

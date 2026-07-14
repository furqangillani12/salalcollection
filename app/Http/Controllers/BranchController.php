<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Product;

class BranchController extends Controller
{
    /**
     * Check if user is locked to a specific branch
     */
    private function isLockedUser()
    {
        return auth()->user()->branch_id !== null;
    }

    /**
     * Abort if locked user tries to access another branch
     */
    private function authorizeAccess(Branch $branch)
    {
        $user = auth()->user();
        if ($user->branch_id && $user->branch_id !== $branch->id) {
            abort(403, 'You can only manage your own branch.');
        }
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->branch_id) {
            // Locked user: only show their branch
            $branches = Branch::withCount(['orders', 'employees', 'users'])
                ->where('id', $user->branch_id)
                ->get();
        } else {
            // Admin: show all
            $branches = Branch::withCount(['orders', 'employees', 'users'])
                ->latest()
                ->get();
        }

        return view('admin.branch.index', compact('branches'));
    }

    public function create()
    {
        // Only non-locked users can create branches
        if ($this->isLockedUser()) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'You cannot create new branches.');
        }

        return view('admin.branch.create');
    }

    public function store(Request $request)
    {
        if ($this->isLockedUser()) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'You cannot create new branches.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'code' => 'nullable|string|max:20|unique:branches,code',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'order_start_number' => 'nullable|integer|min:1',
        ]);

        $data = $request->only('name', 'code', 'address', 'phone', 'order_start_number');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('branch-logos', 'public');
        }

        $branch = Branch::create($data);

        // Create stock entries for all existing products with 0 stock
        $products = Product::all();
        foreach ($products as $product) {
            BranchProductStock::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'stock_quantity' => 0,
                'reorder_level' => $product->reorder_level ?? 10,
            ]);
        }

        return redirect()->route('admin.branches.index')->with('success', "Branch \"{$branch->name}\" created. Stock entries initialized for {$products->count()} products.");
    }

    public function edit(Branch $branch)
    {
        $this->authorizeAccess($branch);
        return view('admin.branch.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeAccess($branch);

        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'code' => 'nullable|string|max:20|unique:branches,code,' . $branch->id,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'order_start_number' => 'nullable|integer|min:1',
        ]);

        $data = $request->only('name', 'code', 'address', 'phone', 'order_start_number');

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($branch->logo && \Storage::disk('public')->exists($branch->logo)) {
                \Storage::disk('public')->delete($branch->logo);
            }
            $data['logo'] = $request->file('logo')->store('branch-logos', 'public');
        }

        $branch->update($data);

        return redirect()->route('admin.branches.index')->with('success', "Branch \"{$branch->name}\" updated.");
    }

    public function destroy(Branch $branch)
    {
        // Locked users cannot delete any branch
        if ($this->isLockedUser()) {
            return back()->with('error', 'You cannot delete branches.');
        }

        if ($branch->orders()->count() > 0) {
            return back()->with('error', "Cannot delete branch \"{$branch->name}\" — it has {$branch->orders()->count()} orders.");
        }

        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', "Branch \"{$branch->name}\" deleted.");
    }

    public function toggleActive(Branch $branch)
    {
        // Locked users cannot toggle branches
        if ($this->isLockedUser()) {
            return back()->with('error', 'You cannot activate/deactivate branches.');
        }

        $branch->update(['is_active' => !$branch->is_active]);
        $status = $branch->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Branch \"{$branch->name}\" {$status}.");
    }

    public function toggleWebsite(Branch $branch)
    {
        if ($this->isLockedUser()) {
            return back()->with('error', 'You cannot change website visibility.');
        }

        $branch->update(['show_on_website' => !$branch->show_on_website]);
        $status = $branch->show_on_website ? 'shown on' : 'hidden from';
        return back()->with('success', "Branch \"{$branch->name}\" is now {$status} the website.");
    }

    // Branch selection page (no branch middleware)
    public function select()
    {
        $user = auth()->user();

        // If user has assigned branch, auto-redirect (they can't switch)
        if ($user->branch_id) {
            session(['branch_id' => $user->branch_id]);
            return redirect()->route('admin.dashboard');
        }

        $branches = Branch::where('is_active', true)->withCount(['orders', 'employees'])->get();
        return view('admin.branch.select', compact('branches'));
    }

    // Store branch selection in session
    public function storeBranchSelection(Request $request)
    {
        $user = auth()->user();
        $branchId = $request->input('branch_id');

        // Users with assigned branch can't switch
        if ($user->branch_id) {
            session(['branch_id' => $user->branch_id]);
            return redirect()->route('admin.dashboard');
        }

        if ($branchId === 'all') {
            if (!$user->can('view all branches')) {
                return back()->with('error', 'You do not have permission to view all branches.');
            }
            session(['branch_id' => 'all']);
        } else {
            $branch = Branch::findOrFail($branchId);
            session(['branch_id' => $branch->id]);
        }

        return redirect()->route('admin.dashboard');
    }
}

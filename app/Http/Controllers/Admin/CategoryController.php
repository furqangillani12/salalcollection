<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use BranchScoped;

    public function index()
    {
        $categories = $this->scopeBranch(Category::query())->withCount('products')
            ->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function store(Request $request)
    {
        $branchId = $this->branchId();
        $scopeBranchId = ($branchId && $branchId !== 'all') ? $branchId : null;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->where(fn ($q) => $q->where('branch_id', $scopeBranchId)),
            ],
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('category-photos', 'public');
        }
        if ($scopeBranchId) {
            $validated['branch_id'] = $scopeBranchId;
        }
        // New categories are active and shown on the storefront by default.
        $validated['is_active'] = 1;
        $validated['show_on_website'] = 1;
        $validated['is_featured'] = 1;

        Category::create($validated);
        return back()->with('success', 'Category created successfully');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')
                    ->where(fn ($q) => $q->where('branch_id', $category->branch_id))
                    ->ignore($category->id),
            ],
            'description' => 'nullable|string',
            'photo'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($category->photo && \Storage::disk('public')->exists($category->photo)) {
                \Storage::disk('public')->delete($category->photo);
            }
            $validated['photo'] = $request->file('photo')->store('category-photos', 'public');
        }

        $category->update($validated);
        return back()->with('success', 'Category updated successfully');
    }

    /** Quick toggle of storefront visibility from the categories list. */
    public function toggleWebsite(Category $category)
    {
        $category->update(['show_on_website' => !$category->show_on_website]);
        return back()->with('success', 'Category is now ' . ($category->show_on_website ? 'visible on' : 'hidden from') . ' the website.');
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->products()->exists()) {
            // First attempt without confirmation → show warning
            if (!$request->has('confirm_delete')) {
                return back()->with('error', 'This category has products. Please confirm to delete the category and all related products.');
            }

            // If confirmed → delete related products
            $category->products()->delete();
        }

        // Delete category itself
        $category->delete();

        return back()->with('success', 'Category and related products deleted successfully');
    }


}

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

        // A display-order number (>= 1) must be unique per branch — the same
        // number can't be given to two categories. 0 means "unset" and may repeat.
        $sortRules = ['nullable', 'integer', 'min:0'];
        if ((int) $request->input('sort_order', 0) >= 1) {
            $sortRules[] = Rule::unique('categories', 'sort_order')->where(fn ($q) => $q->where('branch_id', $scopeBranchId));
        }

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->where(fn ($q) => $q->where('branch_id', $scopeBranchId)),
            ],
            'description' => 'nullable|string',
            'sort_order'  => $sortRules,
            'photo'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ], [
            'sort_order.unique' => 'This display order number is already used by another category. Please pick a different number.',
        ]);

        $validated['sort_order'] = (int) $request->input('sort_order', 0);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('category-photos', 'public');
        }

        if ($scopeBranchId) {
            $validated['branch_id'] = $scopeBranchId;
        }

        Category::create($validated);
        return back()->with('success', 'Category created successfully');
    }

    public function update(Request $request, Category $category)
    {
        // Unique display order (>= 1) per branch, ignoring this category itself.
        $sortRules = ['nullable', 'integer', 'min:0'];
        if ((int) $request->input('sort_order', 0) >= 1) {
            $sortRules[] = Rule::unique('categories', 'sort_order')
                ->where(fn ($q) => $q->where('branch_id', $category->branch_id))
                ->ignore($category->id);
        }

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')
                    ->where(fn ($q) => $q->where('branch_id', $category->branch_id))
                    ->ignore($category->id),
            ],
            'description' => 'nullable|string',
            'sort_order'  => $sortRules,
            'photo'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
        ], [
            'sort_order.unique' => 'This display order number is already used by another category. Please pick a different number.',
        ]);

        $validated['sort_order'] = (int) $request->input('sort_order', $category->sort_order ?? 0);

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

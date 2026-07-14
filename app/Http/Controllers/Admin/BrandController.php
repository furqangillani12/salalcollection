<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::withCount('products')->orderBy('sort_order')->orderBy('name')->paginate(20);
        return view('admin.brands.index', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }
        Brand::create($data);
        return redirect()->route('admin.brands.index')->with('success', 'Brand added.');
    }

    public function edit(Brand $brand)
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $this->validated($request);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }
        $brand->update($data);
        return redirect()->route('admin.brands.index')->with('success', 'Brand updated.');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return back()->with('success', 'Brand deleted.');
    }

    public function toggle(Brand $brand)
    {
        $brand->update(['is_active' => !$brand->is_active]);
        return back();
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'name'        => 'required|string|max:191',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|max:2048',
            'is_active'   => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'sort_order'  => 'nullable|integer',
        ]) + [
            'is_active'   => $r->boolean('is_active'),
            'is_featured' => $r->boolean('is_featured'),
        ];
    }
}

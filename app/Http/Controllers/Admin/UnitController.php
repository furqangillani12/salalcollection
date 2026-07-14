<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        // Change this from all() to paginate()
        $units = Unit::withCount('products')->paginate(20); // Added pagination
        
        return view('admin.units.index', compact('units'));
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'abbreviation' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Unit created successfully');
    }

    public function edit(Unit $unit)
    {
        // Load products count for the edit view
        $unit->loadCount('products');
        
        return view('admin.units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'abbreviation' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully');
    }

    public function destroy(Unit $unit)
    {
        // Check if unit is being used by products
        if ($unit->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete unit. It is being used by ' . $unit->products()->count() . ' product(s).');
        }

        $unit->delete();
        return redirect()->back()->with('success', 'Unit deleted successfully');
    }
}
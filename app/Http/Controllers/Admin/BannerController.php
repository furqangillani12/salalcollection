<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('position')->orderBy('sort_order')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('banners', 'public');
        }
        Banner::create($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner added.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request);
        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('banners', 'public');
        }
        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return back()->with('success', 'Banner deleted.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => !$banner->is_active]);
        return back();
    }

    private function validated(Request $r): array
    {
        $rules = [
            'title'      => 'nullable|string|max:191',
            'subtitle'   => 'nullable|string|max:191',
            'cta_text'   => 'nullable|string|max:50',
            'cta_url'    => 'nullable|string|max:500',
            'image'      => 'nullable|string|max:500',
            'image_file' => 'nullable|image|max:4096',
            'position'   => 'required|in:hero,mid,side,footer',
            'sort_order' => 'nullable|integer',
            'is_active'  => 'sometimes|boolean',
        ];
        return $r->validate($rules) + [ 'is_active' => $r->boolean('is_active') ];
    }
}

<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'title'   => 'nullable|string|max:191',
            'body'    => 'nullable|string|max:2000',
            'media'   => 'nullable|array|max:5',
            'media.*' => 'file|mimes:png,jpg,jpeg,webp,mp4,webm,mov|max:20480',
        ], [
            'media.*.max'   => 'Each photo/video must be 20 MB or smaller.',
            'media.*.mimes' => 'Only images (PNG/JPG/WebP) or videos (MP4/WebM/MOV) are allowed.',
        ]);

        // Store any uploaded photos / videos.
        $media = [];
        foreach ((array) $request->file('media', []) as $file) {
            $path = $file->store('review-media', 'public');
            $isVideo = str_starts_with((string) $file->getMimeType(), 'video');
            $media[] = ['path' => $path, 'type' => $isVideo ? 'video' : 'image'];
        }

        DB::transaction(function () use ($data, $product, $media) {
            // Reviews now wait for admin approval before they show or earn points,
            // so a mistaken / spam review can be rejected (client request).
            ProductReview::create([
                'product_id'     => $product->id,
                'customer_id'    => Auth::guard('customer')->id(),
                'rating'         => $data['rating'],
                'title'          => $data['title'] ?? null,
                'body'           => $data['body'] ?? null,
                'media'          => $media ?: null,
                'status'         => 'pending',
                'points_awarded' => false,
            ]);
        });

        return back()->with('shop_success', 'Thanks for your review! It will appear once our team approves it.');
    }
}

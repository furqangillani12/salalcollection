<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin moderation for storefront product reviews. Reviews arrive as "pending";
 * an admin approves (which shows them and awards the review points, once) or
 * rejects / deletes spam or mistaken reviews. Storefront-only — POS unaffected.
 */
class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = in_array($request->input('status'), ['pending', 'approved', 'rejected'], true)
            ? $request->input('status') : 'pending';

        $reviews = ProductReview::with('product:id,name,slug', 'customer:id,name')
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => ProductReview::where('status', 'pending')->count(),
            'approved' => ProductReview::where('status', 'approved')->count(),
            'rejected' => ProductReview::where('status', 'rejected')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'status', 'counts'));
    }

    public function approve(ProductReview $review)
    {
        DB::transaction(function () use ($review) {
            $review->update(['status' => 'approved']);

            // Award the review points exactly once, only on first approval.
            $reward = (int) setting('points_per_review', 0);
            if (! $review->points_awarded && $reward > 0 && $review->customer) {
                $review->customer->awardPoints($reward, 'earn_review', 'Review on ' . ($review->product?->name ?? 'product'));
                $review->update(['points_awarded' => true]);
            }

            $this->recountProduct($review->product_id);
        });

        return back()->with('success', 'Review approved.');
    }

    public function reject(ProductReview $review)
    {
        $review->update(['status' => 'rejected']);
        $this->recountProduct($review->product_id);

        return back()->with('success', 'Review rejected — it will no longer show on the storefront.');
    }

    public function destroy(ProductReview $review)
    {
        foreach ($review->mediaItems() as $m) {
            if (\Storage::disk('public')->exists($m['path'])) {
                \Storage::disk('public')->delete($m['path']);
            }
        }
        $productId = $review->product_id;
        $review->delete();
        $this->recountProduct($productId);

        return back()->with('success', 'Review deleted.');
    }

    /** Recompute a product's rating aggregates from its APPROVED reviews only. */
    protected function recountProduct(int $productId): void
    {
        $agg = ProductReview::where('product_id', $productId)
            ->where('status', 'approved')
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
            ->first();

        Product::where('id', $productId)->update([
            'avg_rating'   => round((float) $agg->avg_rating, 2),
            'review_count' => (int) $agg->review_count,
        ]);
    }
}

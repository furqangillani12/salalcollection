<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\ProductReview;
use Carbon\Carbon;

/**
 * Salal Collection admin dashboard — a lightweight overview of the online store
 * (orders, revenue, catalogue, reviews). No POS / cash / khata figures.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $online = Order::where('order_source', 'online');

        $stats = [
            'orders_total'    => (clone $online)->count(),
            'orders_pending'  => (clone $online)->where('status', 'pending')->count(),
            'orders_today'    => (clone $online)->whereDate('created_at', Carbon::today())->count(),
            'revenue'         => (clone $online)->where('status', '!=', 'cancelled')->sum('total'),
            'revenue_month'   => (clone $online)->where('status', '!=', 'cancelled')
                                    ->whereMonth('created_at', Carbon::now()->month)
                                    ->whereYear('created_at', Carbon::now()->year)->sum('total'),
            'products'        => Product::count(),
            'products_live'   => Product::where('show_on_website', true)->count(),
            'customers'       => Customer::count(),
            'reviews_pending' => ProductReview::where('status', 'pending')->count(),
        ];

        $recentOrders = Order::where('order_source', 'online')
            ->with('customer')
            ->latest()
            ->limit(8)
            ->get();

        $statusBreakdown = (clone $online)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        return view('admin.dashboard', compact('stats', 'recentOrders', 'statusBreakdown'));
    }
}

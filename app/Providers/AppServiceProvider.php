<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Models
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Refund;
use App\Models\Payroll;

// Observers
use App\Observers\OrderObserver;
use App\Observers\PurchaseObserver;
use App\Observers\ExpenseObserver;
use App\Observers\RefundObserver;
use App\Observers\PayrollObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        Purchase::observe(PurchaseObserver::class);
        Expense::observe(ExpenseObserver::class);
        Refund::observe(RefundObserver::class);
        Payroll::observe(PayrollObserver::class);
    }
}
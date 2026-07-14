<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicReceiptController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Salal Collection — website + admin management (no POS)
|--------------------------------------------------------------------------
| Only the storefront-management surface is exposed here: Inventory,
| Online Orders, Reports, and Settings (which also holds Banners, Brands
| and Reviews). All point-of-sale, payroll, cash, credit/khata, ledger,
| suppliers and purchase modules have been removed.
*/

// Storefront is the site's front door.
Route::get('/', function () {
    return redirect('/' . ltrim(env('SHOP_PREFIX', 'shop'), '/'));
});

// ── Branch selection + profile + auth (no branch middleware) ──
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/branch/select', [BranchController::class, 'select'])->name('branch.select');
    Route::post('/branch/select', [BranchController::class, 'storeBranchSelection'])->name('branch.store-selection');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/logout', function () {
        Auth::logout();
        session()->forget('branch_id');
        return redirect('/');
    })->name('logout');
});

// ══════════════════════════════════════════════════════════════════
// Admin (all require auth + branch)
// ══════════════════════════════════════════════════════════════════

// ── Dashboard ──
Route::prefix('admin')->middleware(['auth', 'branch'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});

// ── Inventory: Products, Units, Categories, stock ──
Route::middleware(['auth', 'branch', 'permission:manage products'])->group(function () {
    Route::resource('products', ProductController::class)->except(['show']);
    Route::patch('products/{product}/toggle-website', [ProductController::class, 'toggleWebsite'])->name('products.toggle-website');
    Route::get('products/import', [ProductController::class, 'showImportForm'])->name('products.import.show');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
    Route::resource('units', UnitController::class);
});

Route::middleware(['auth', 'branch', 'permission:manage categories'])->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::patch('categories/{category:id}/toggle-website', [CategoryController::class, 'toggleWebsite'])->name('categories.toggle-website');
});

Route::middleware(['auth', 'branch', 'permission:manage inventory'])->group(function () {
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::get('inventory/logs', [InventoryController::class, 'logs'])->name('inventory.logs');
    Route::get('inventory/low-stock', [InventoryController::class, 'lowStock'])->name('inventory.low-stock');
});

// ── Reports (online-order based) ──
Route::middleware(['auth', 'branch', 'permission:view reports'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/top-products', [ReportController::class, 'topProducts'])->name('top-products');
        Route::get('/profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('/category-sales', [ReportController::class, 'categorySales'])->name('category-sales');
        Route::get('/customer-sales', [ReportController::class, 'customerSales'])->name('customer-sales');
        Route::get('/product-statement', [ReportController::class, 'productStatement'])->name('product-statement');
    });
    Route::get('/orders/{order}', [ReportController::class, 'show'])->name('orders.show');
    Route::get('/reports/customer-orders/{customer}', [ReportController::class, 'getCustomerOrders']);
});

// ── Customers ──
Route::prefix('admin')->name('admin.')->middleware(['auth', 'branch'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/award-points', [CustomerController::class, 'awardPoints'])->name('customers.award-points');
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
});

// ── Settings (Site, Payment & Dispatch methods, Delivery slabs) ──
Route::middleware(['auth', 'branch'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/site', [SettingsController::class, 'updateSiteSettings'])->name('settings.site.update');
    Route::post('/settings/status-templates', [SettingsController::class, 'updateStatusTemplates'])->name('settings.status-templates.update');

    Route::post('/settings/payment-methods', [SettingsController::class, 'storePaymentMethod'])->name('settings.payment-methods.store');
    Route::put('/settings/payment-methods/{paymentMethod}', [SettingsController::class, 'updatePaymentMethod'])->name('settings.payment-methods.update');
    Route::patch('/settings/payment-methods/{paymentMethod}/toggle', [SettingsController::class, 'togglePaymentMethod'])->name('settings.payment-methods.toggle');
    Route::delete('/settings/payment-methods/{paymentMethod}', [SettingsController::class, 'destroyPaymentMethod'])->name('settings.payment-methods.destroy');

    Route::post('/settings/dispatch-methods', [SettingsController::class, 'storeDispatchMethod'])->name('settings.dispatch-methods.store');
    Route::put('/settings/dispatch-methods/{dispatchMethod}', [SettingsController::class, 'updateDispatchMethod'])->name('settings.dispatch-methods.update');
    Route::patch('/settings/dispatch-methods/{dispatchMethod}/toggle', [SettingsController::class, 'toggleDispatchMethod'])->name('settings.dispatch-methods.toggle');
    Route::delete('/settings/dispatch-methods/{dispatchMethod}', [SettingsController::class, 'destroyDispatchMethod'])->name('settings.dispatch-methods.destroy');

    Route::post('/settings/delivery-slabs', [SettingsController::class, 'storeDeliverySlab'])->name('settings.delivery-slabs.store');
    Route::put('/settings/delivery-slabs/{slab}', [SettingsController::class, 'updateDeliverySlab'])->name('settings.delivery-slabs.update');
    Route::patch('/settings/delivery-slabs/{slab}/toggle', [SettingsController::class, 'toggleDeliverySlab'])->name('settings.delivery-slabs.toggle');
    Route::delete('/settings/delivery-slabs/{slab}', [SettingsController::class, 'destroyDeliverySlab'])->name('settings.delivery-slabs.destroy');
});

// ── Manage Website: Brands, Banners, Online Orders, Reviews (surfaced under Settings) ──
Route::middleware(['auth', 'branch'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('brands',  \App\Http\Controllers\Admin\BrandController::class)->except(['show']);
    Route::patch('/brands/{brand}/toggle',   [\App\Http\Controllers\Admin\BrandController::class,  'toggle'])->name('brands.toggle');
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->except(['show']);
    Route::patch('/banners/{banner}/toggle', [\App\Http\Controllers\Admin\BannerController::class, 'toggle'])->name('banners.toggle');

    Route::get('/online-orders',                     [\App\Http\Controllers\Admin\OnlineOrderController::class, 'index'])->name('online-orders.index');
    Route::get('/online-orders/{order}',             [\App\Http\Controllers\Admin\OnlineOrderController::class, 'show'])->name('online-orders.show');
    Route::patch('/online-orders/{order}/status',    [\App\Http\Controllers\Admin\OnlineOrderController::class, 'updateStatus'])->name('online-orders.status');
    Route::patch('/online-orders/{order}/mark-paid', [\App\Http\Controllers\Admin\OnlineOrderController::class, 'markPaid'])->name('online-orders.mark-paid');
    Route::patch('/online-orders/{order}/adjust',    [\App\Http\Controllers\Admin\OnlineOrderController::class, 'adjust'])->name('online-orders.adjust');
    Route::post('/online-orders/{order}/notify',      [\App\Http\Controllers\Admin\OnlineOrderController::class, 'notify'])->name('online-orders.notify');
    Route::get('/online-orders/{order}/slip',         [\App\Http\Controllers\Admin\OnlineOrderController::class, 'slip'])->name('online-orders.slip');
    Route::get('/online-orders/{order}/checklist',    [\App\Http\Controllers\Admin\OnlineOrderController::class, 'checklist'])->name('online-orders.checklist');
    Route::post('/online-orders/{order}/dispatch-media', [\App\Http\Controllers\Admin\OnlineOrderController::class, 'uploadDispatchMedia'])->name('online-orders.dispatch-media');

    Route::get('/reviews',                    [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::patch('/reviews/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
    Route::patch('/reviews/{review}/reject',  [\App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
    Route::delete('/reviews/{review}',        [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// ── Public order receipt (no auth) ──
Route::get('/receipt/{token}', [PublicReceiptController::class, 'show'])->name('public.receipt.show');
Route::get('/receipt/{token}/download', [PublicReceiptController::class, 'download'])->name('public.receipt.download');
Route::get('/receipt/{token}/print', [PublicReceiptController::class, 'print'])->name('public.receipt.print');
Route::get('/receipt/{token}/json', [PublicReceiptController::class, 'json'])->name('public.receipt.json');

require __DIR__ . '/auth.php';

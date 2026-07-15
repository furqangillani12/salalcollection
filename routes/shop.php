<?php

/*
|--------------------------------------------------------------------------
| Shop (public storefront) routes — salalcollection.com
|--------------------------------------------------------------------------
|
| In production:
|     SHOP_DOMAIN=salalcollection.com    (in .env)
|     → Routes match Host: salalcollection.com only. POS stays on its subdomain.
|
| In local dev (one host, no DNS):
|     SHOP_PREFIX=shop                (in .env, default if SHOP_DOMAIN unset)
|     → Routes mount at /shop/...     so they don't collide with the POS
|       welcome page at "/" and Breeze auth at /login etc.
|
| All routes here are NEW and ADDITIVE — no POS route is altered.
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\CatalogController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\AuthController as ShopAuthController;
use App\Http\Controllers\Shop\ReviewController;

$shopDomain = env('SHOP_DOMAIN');
$shopPrefix = env('SHOP_PREFIX', $shopDomain ? '' : 'shop');

$registerShopRoutes = function () {
    Route::name('shop.')->group(function () {

        // ── Catalog (public) ────────────────────────────────────────────────
        Route::get('/',                       [HomeController::class, 'index'])->name('home');
        Route::get('/shop',                   [CatalogController::class, 'index'])->name('catalog');
        Route::get('/category/{category:slug}', [CatalogController::class, 'category'])->name('category');
        Route::get('/brand/{brand:slug}',     [CatalogController::class, 'brand'])->name('brand');
        Route::get('/search',                 [CatalogController::class, 'search'])->name('search');
        Route::get('/product/{product:slug}', [ShopProductController::class, 'show'])->name('product');

        // ── Static pages ────────────────────────────────────────────────────
        Route::view('/about',   'shop.pages.about')->name('about');
        Route::view('/contact', 'shop.pages.contact')->name('contact');
        Route::view('/privacy', 'shop.pages.privacy')->name('privacy');
        Route::view('/terms',   'shop.pages.terms')->name('terms');
        Route::view('/returns', 'shop.pages.returns')->name('returns');

        // ── Cart (works for guests + customers) ────────────────────────────
        Route::get('/cart',                  [CartController::class, 'index'])->name('cart');
        Route::post('/cart/add',             [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/add-package',     [CartController::class, 'addPackage'])->name('cart.add-package');
        Route::post('/cart/update/{item}',   [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
        Route::get('/cart/json',             [CartController::class, 'json'])->name('cart.json');
        Route::post('/cart/coupon',          [CartController::class, 'applyCoupon'])->name('cart.coupon');
        Route::delete('/cart/coupon',        [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

        // ── Auth (customer guard) ──────────────────────────────────────────
        Route::middleware('guest:customer')->group(function () {
            Route::get('/login',     [ShopAuthController::class, 'showLogin'])->name('login');
            Route::post('/login',    [ShopAuthController::class, 'login'])->name('login.post');
            Route::get('/register',  [ShopAuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [ShopAuthController::class, 'register'])->name('register.post');
        });
        Route::post('/logout', [ShopAuthController::class, 'logout'])->middleware('auth:customer')->name('logout');

        // ── Customer-only (account, wishlist, write reviews) ──────────────
        Route::middleware('auth:customer')->group(function () {
            Route::get('/account',                 [AccountController::class, 'index'])->name('account');
            Route::get('/account/profile',         [AccountController::class, 'profile'])->name('account.profile');
            Route::put('/account/profile',         [AccountController::class, 'updateProfile'])->name('account.profile.update');
            Route::get('/account/password',        [AccountController::class, 'password'])->name('account.password');
            Route::put('/account/password',        [AccountController::class, 'updatePassword'])->name('account.password.update');
            Route::get('/account/orders',          [AccountController::class, 'orders'])->name('account.orders');
            Route::get('/account/orders/{order}',  [AccountController::class, 'orderShow'])->name('account.order');
            Route::post('/account/orders/{order}/proof', [AccountController::class, 'uploadProof'])->name('account.order.proof');
            Route::post('/account/orders/{order}/delivered', [AccountController::class, 'markDelivered'])->name('account.order.delivered');
            Route::get('/account/statement',       [AccountController::class, 'statement'])->name('account.statement');
            Route::get('/account/points',          [AccountController::class, 'points'])->name('account.points');

            Route::get('/wishlist',                        [WishlistController::class, 'index'])->name('wishlist');
            Route::post('/wishlist/toggle/{product:id}',   [WishlistController::class, 'toggle'])->name('wishlist.toggle');
            Route::delete('/wishlist/{wishlist}',          [WishlistController::class, 'remove'])->name('wishlist.remove');

            Route::post('/product/{product:slug}/review', [ReviewController::class, 'store'])->name('review.store');
        });

        // ── Checkout (guest + customer) ────────────────────────────────────
        // Open to guests too. The CheckoutController fills customer_id when
        // authenticated, otherwise stores email/phone on the order.
        Route::get('/checkout',                   [CheckoutController::class, 'index'])->name('checkout');
        Route::get('/checkout/lookup',            [CheckoutController::class, 'lookup'])->name('checkout.lookup');
        Route::post('/checkout/place',            [CheckoutController::class, 'place'])->name('checkout.place');
        Route::get('/checkout/thank-you/{order}', [CheckoutController::class, 'thankYou'])->name('checkout.thanks');

        // ── Order tracking (anyone, no login) ──────────────────────────────
        Route::get('/track-order',  [\App\Http\Controllers\Shop\TrackOrderController::class, 'show'])->name('track');
        Route::post('/track-order', [\App\Http\Controllers\Shop\TrackOrderController::class, 'find'])->name('track.find');
        Route::get('/track-order/{token}', [\App\Http\Controllers\Shop\TrackOrderController::class, 'view'])->name('track.view');
    });
};

if ($shopDomain) {
    Route::domain($shopDomain)->group($registerShopRoutes);
} elseif ($shopPrefix) {
    Route::prefix($shopPrefix)->group($registerShopRoutes);
} else {
    $registerShopRoutes();
}

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            // shop.php first: when SHOP_DOMAIN is set its domain-scoped routes
            // must be matched before web.php's host-agnostic '/' (POS welcome),
            // otherwise the main domain root falls through to the POS landing.
            // In local dev (no SHOP_DOMAIN) the shop mounts under /shop, so
            // there is no collision and order is irrelevant.
            __DIR__.'/../routes/shop.php',
            __DIR__.'/../routes/web.php',
        ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \App\Http\Middleware\TrackLogins::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'branch' => \App\Http\Middleware\BranchScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

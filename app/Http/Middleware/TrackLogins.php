<?php

// app/Http/Middleware/TrackLogins.php
namespace App\Http\Middleware;

use Closure;
use App\Models\LoginHistory;
use Illuminate\Http\Request;

class TrackLogins
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            LoginHistory::create([
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
            ]);
        }

        return $next($request);
    }
}

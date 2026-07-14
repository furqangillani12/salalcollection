<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function destroy(Request $request)
    {
        // Update logout time
        LoginHistory::where('user_id', auth()->id())
            ->whereNull('logout_at')
            ->latest()
            ->update(['logout_at' => now()]);

        Auth::guard('web')->logout();
        // ...
    }
}

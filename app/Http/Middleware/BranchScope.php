<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class BranchScope
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $branchId = session('branch_id');
        $allBranches = Branch::where('is_active', true)->orderBy('name')->get();

        // If user has an assigned branch, lock them to it
        if ($user->branch_id) {
            $branchId = $user->branch_id;
            session(['branch_id' => $branchId]);
        }

        // If no branch selected
        if (!$branchId) {
            // Auto-select if only one branch exists
            if ($allBranches->count() === 1) {
                session(['branch_id' => $allBranches->first()->id]);
                $branchId = $allBranches->first()->id;
            } else {
                return redirect()->route('admin.branch.select');
            }
        }

        // Resolve current branch
        if ($branchId === 'all') {
            $currentBranch = 'all';
        } else {
            $currentBranch = Branch::find($branchId);
            if (!$currentBranch) {
                session()->forget('branch_id');
                return redirect()->route('admin.branch.select');
            }
        }

        // Share with all views
        View::share('currentBranch', $currentBranch);
        View::share('allBranches', $allBranches);
        // Share whether user can switch branches (for hiding/showing switcher)
        View::share('canSwitchBranch', !$user->branch_id);

        return $next($request);
    }
}

<?php

namespace App\Traits;

trait BranchScoped
{
    /**
     * Get current branch ID from session.
     * Returns int or 'all'.
     */
    protected function branchId()
    {
        return session('branch_id');
    }

    /**
     * Check if viewing all branches.
     */
    protected function isAllBranches(): bool
    {
        $branchId = session('branch_id');
        return $branchId === 'all' || $branchId === null;
    }

    /**
     * Apply branch filter to a query builder.
     * If 'all' branches selected, no filter applied.
     */
    protected function scopeBranch($query, string $column = 'branch_id')
    {
        $branchId = $this->branchId();

        if ($branchId && $branchId !== 'all') {
            return $query->where($column, $branchId);
        }

        return $query;
    }
}

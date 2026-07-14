<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\LedgerService;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        LedgerService::recordExpense($expense);
    }
}
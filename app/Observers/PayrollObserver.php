<?php

namespace App\Observers;

use App\Models\Payroll;
use App\Services\LedgerService;

class PayrollObserver
{
    /**
     * Only record ledger entry when payroll is marked as paid.
     */
    public function updated(Payroll $payroll): void
    {
        if ($payroll->isDirty('status') && $payroll->status === 'paid') {
            LedgerService::recordPayroll($payroll);
        }
    }
}
<?php

namespace App\Observers;

use App\Models\Refund;
use App\Services\LedgerService;

class RefundObserver
{
    public function created(Refund $refund): void
    {
        LedgerService::recordRefund($refund);
    }
}
<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Services\LedgerService;

class PurchaseObserver
{
    public function created(Purchase $purchase): void
    {
        LedgerService::recordPurchase($purchase);
    }
}
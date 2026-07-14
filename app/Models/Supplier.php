<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'email', 'phone', 'address', 'company_name', 'linked_customer_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * The customer record that represents the SAME real-world party as this supplier.
     */
    public function linkedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'linked_customer_id');
    }

    // Relationship with Purchases
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function getTotalDueAttribute(): float
    {
        $totalPurchased = $this->purchases()->sum('total_amount');
        $totalPaidOnPurchases = $this->purchases()->sum('paid_amount');
        // Linked payments are already in purchases.paid_amount, so only add unlinked ones
        $unlinkedPayments = $this->payments()->whereNull('purchase_id')->sum('amount');
        return $totalPurchased - $totalPaidOnPurchases - $unlinkedPayments;
    }
}

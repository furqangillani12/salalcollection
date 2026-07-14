<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LedgerEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'entry_date',
        'account_type',
        'transaction_type',
        'reference_type',
        'reference_id',
        'reference_number',
        'description',
        'debit',
        'credit',
        'payment_method',
        'party_type',
        'party_id',
        'party_name',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
    ];

    // ─── Account Type Constants ─────────────────────────────────────────────
    const ACCOUNT_SALES              = 'sales';
    const ACCOUNT_PURCHASES          = 'purchases';
    const ACCOUNT_EXPENSES           = 'expenses';
    const ACCOUNT_CASH_IN            = 'cash_in';
    const ACCOUNT_CASH_OUT           = 'cash_out';
    const ACCOUNT_ACCOUNTS_RECEIVABLE = 'accounts_receivable';
    const ACCOUNT_REFUNDS            = 'refunds';
    const ACCOUNT_PAYROLL            = 'payroll';

    // ─── Transaction Type Constants ─────────────────────────────────────────
    const TYPE_SALE            = 'sale';
    const TYPE_PURCHASE        = 'purchase';
    const TYPE_EXPENSE         = 'expense';
    const TYPE_REFUND          = 'refund';
    const TYPE_CREDIT_SALE     = 'credit_sale';
    const TYPE_CREDIT_PAYMENT  = 'credit_payment';
    const TYPE_PAYROLL         = 'payroll';

    // ─── Human-readable labels ───────────────────────────────────────────────
    const ACCOUNT_LABELS = [
        'sales'                => 'Sales Revenue',
        'purchases'            => 'Purchases / Stock',
        'expenses'             => 'Operating Expenses',
        'cash_in'              => 'Cash Inflow',
        'cash_out'             => 'Cash Outflow',
        'accounts_receivable'  => 'Accounts Receivable',
        'refunds'              => 'Refunds / Returns',
        'payroll'              => 'Payroll / Salaries',
    ];

    const TRANSACTION_LABELS = [
        'sale'            => 'Cash Sale',
        'purchase'        => 'Purchase',
        'expense'         => 'Expense',
        'refund'          => 'Refund',
        'credit_sale'     => 'Credit Sale',
        'credit_payment'  => 'Credit Payment',
        'payroll'         => 'Payroll',
    ];

    /**
     * Generate a unique entry number: LED-YYYYMM-XXXX
     */
    public static function generateEntryNumber(): string
    {
        $prefix = 'LED-' . now()->format('Ym') . '-';

        $last = self::where('entry_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('entry_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helper Methods ──────────────────────────────────────────────────────

    public function getAccountLabelAttribute(): string
    {
        return self::ACCOUNT_LABELS[$this->account_type] ?? ucfirst($this->account_type);
    }

    public function getTransactionLabelAttribute(): string
    {
        return self::TRANSACTION_LABELS[$this->transaction_type] ?? ucfirst($this->transaction_type);
    }

    public function getAmountAttribute(): float
    {
        // Net effect: credit = income (positive), debit = cost (negative)
        return $this->credit - $this->debit;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('entry_date', [$from, $to]);
    }

    public function scopeForAccount($query, string $account)
    {
        return $query->where('account_type', $account);
    }

    public function scopeForTransactionType($query, string $type)
    {
        return $query->where('transaction_type', $type);
    }
}
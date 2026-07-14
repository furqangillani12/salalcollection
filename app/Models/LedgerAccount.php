<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerAccount extends Model
{
    protected $fillable = [
        'account_code',
        'name',
        'type',
        'category',
        'description',
        'is_active',
        'opening_balance',
        'created_by',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    // ─── Account Types ───────────────────────────────────────────────────────
    const TYPE_EXPENSE   = 'expense';
    const TYPE_INCOME    = 'income';
    const TYPE_ASSET     = 'asset';
    const TYPE_LIABILITY = 'liability';

    const TYPE_LABELS = [
        'expense'   => 'Expense (خرچہ)',
        'income'    => 'Income (آمدن)',
        'asset'     => 'Asset (اثاثہ)',
        'liability' => 'Liability (ذمہ داری)',
    ];

    const TYPE_COLORS = [
        'expense'   => 'red',
        'income'    => 'green',
        'asset'     => 'blue',
        'liability' => 'orange',
    ];

    // ─── Auto-generate account code ──────────────────────────────────────────
    public static function generateAccountCode(): string
    {
        $last = self::orderByDesc('id')->value('account_code');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'ACC-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    // ─── Relationships ───────────────────────────────────────────────────────
    public function entries(): HasMany
    {
        return $this->hasMany(LedgerAccountEntry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Computed Balances ───────────────────────────────────────────────────
    public function getTotalDebitAttribute(): float
    {
        return (float) $this->entries()->sum('debit');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->entries()->sum('credit');
    }

    /**
     * Net balance = Opening + Credit - Debit  (for income/liability)
     *             = Opening + Debit  - Credit  (for expense/asset)
     */
    public function getBalanceAttribute(): float
    {
        $debit  = $this->total_debit;
        $credit = $this->total_credit;

        if (in_array($this->type, [self::TYPE_INCOME, self::TYPE_LIABILITY])) {
            return $this->opening_balance + $credit - $debit;
        }

        return $this->opening_balance + $debit - $credit;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? ucfirst($this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? 'gray';
    }
}
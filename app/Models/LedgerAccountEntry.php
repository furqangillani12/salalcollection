<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LedgerAccountEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'ledger_account_id',
        'entry_date',
        'description',
        'debit',
        'credit',
        'reference_type',
        'reference_id',
        'reference_number',
        'payment_method',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'debit'      => 'decimal:2',
        'credit'     => 'decimal:2',
    ];

    public static function generateEntryNumber(): string
    {
        $prefix = 'JV-' . now()->format('Ym') . '-';
        $last   = self::where('entry_number', 'like', $prefix . '%')
                      ->orderByDesc('id')
                      ->value('entry_number');
        $next   = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getAmountAttribute(): float
    {
        return $this->credit > 0 ? $this->credit : $this->debit;
    }

    public function getTypeAttribute(): string
    {
        return $this->credit > 0 ? 'credit' : 'debit';
    }
}
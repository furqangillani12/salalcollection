<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'customer_id', 'order_id',
        'rating', 'title', 'body', 'media', 'status', 'points_awarded',
    ];

    protected $casts = [
        'rating'         => 'integer',
        'media'          => 'array',
        'points_awarded' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }

    public function scopePending($q)
    {
        return $q->where('status', 'pending');
    }

    /** Normalised media list: [['path' => ..., 'type' => 'image'|'video'], ...]. */
    public function mediaItems(): array
    {
        return collect($this->media ?? [])
            ->filter(fn ($m) => is_array($m) && ! empty($m['path']))
            ->values()
            ->all();
    }
}

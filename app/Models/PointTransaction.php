<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = ['customer_id', 'points', 'type', 'note', 'order_id', 'user_id'];

    protected $casts = ['points' => 'integer'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** Human label for the transaction type. */
    public function getLabelAttribute(): string
    {
        return match ($this->type) {
            'earn_order'    => 'Order reward',
            'earn_review'   => 'Review reward',
            'review_photo'  => 'Photo review bonus',
            'review_video'  => 'Video review bonus',
            'review_social' => 'Social media review',
            'redeem'        => 'Redeemed',
            default         => 'Adjustment',
        };
    }
}

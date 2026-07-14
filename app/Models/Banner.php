<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title', 'subtitle', 'cta_text', 'cta_url',
        'image', 'mobile_image',
        'position', 'is_active', 'sort_order',
        'starts_at', 'ends_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
                 ->where(function ($q) {
                     $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                 })
                 ->where(function ($q) {
                     $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                 });
    }

    public function scopePosition($q, string $pos)
    {
        return $q->where('position', $pos);
    }
}

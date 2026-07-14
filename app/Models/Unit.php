<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'name',
        'abbreviation',
        'description', 
        'is_active'
    ];

    // Relationship with Products
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
     public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
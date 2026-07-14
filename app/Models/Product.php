<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'branch_id',
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'slug',
        'barcode',
        'description',
        'summary',
        'price',
        'sale_price',
        'resale_price',
        'wholesale_price',
        'cost_price',
        'weight',
        'stock_quantity',
        'reorder_level',
        'image',
        'gallery',
        'is_active',
        'is_featured',
        'show_on_website',
        'condition_label',
        'meta_title',
        'meta_description',
        'avg_rating',
        'review_count',
        'views',
        'track_inventory',
        'rank',
    ];

    protected $casts = [
        'gallery'         => 'array',
        'is_featured'     => 'boolean',
        'show_on_website' => 'boolean',
        'avg_rating'      => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function (Product $p) {
            if (empty($p->slug) && $p->name) {
                $base = \Illuminate\Support\Str::slug($p->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $p->id ?? 0)->exists()) {
                    $slug = $base . '-' . ++$i;
                }
                $p->slug = $slug;
            }
        });
    }

    // Relationship with Category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'approved')->latest();
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * All image URLs for the product (cover + gallery), as web-accessible paths.
     * Returns at least one entry — falls back to a placeholder string.
     */
    public function getImageUrlsAttribute(): array
    {
        $urls = [];
        if ($this->image) $urls[] = $this->image;
        if (is_array($this->gallery)) {
            foreach ($this->gallery as $g) {
                if ($g && !in_array($g, $urls)) $urls[] = $g;
            }
        }
        return $urls;
    }

    public function scopeOnWebsite($q)
    {
        // Cascade visibility (#2): a product is hidden from the storefront if its
        // owning branch OR its category has been switched off for the website —
        // even when the product itself is on. whereDoesntHave keeps products with
        // no category/branch visible (only an explicitly OFF relation hides them).
        return $q->where('products.is_active', true)
            ->where('products.show_on_website', true)
            ->whereDoesntHave('category', fn ($c) => $c->where('show_on_website', false))
            ->whereDoesntHave('branch', fn ($b) => $b->where('show_on_website', false));
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true)->where('is_active', true);
    }

    /** Most-viewed first (then best reviewed) — storefront popularity. */
    public function scopePopular($q)
    {
        return $q->orderByDesc('views')->orderByDesc('review_count')->orderByDesc('id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // Relationship with Order Items
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relationship with Purchase Items
    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Relationship with Inventory Logs
    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_product_stock')
            ->withPivot('stock_quantity', 'reorder_level')
            ->withTimestamps();
    }

    public function stockEntries()
    {
        return $this->hasMany(BranchProductStock::class);
    }

    public function getStockForBranch($branchId)
    {
        if (!$branchId || $branchId === 'all') {
            return $this->getTotalStock();
        }
        $entry = $this->stockEntries()->where('branch_id', $branchId)->first();
        return $entry ? $entry->stock_quantity : 0;
    }

    public function getTotalStock()
    {
        return $this->stockEntries()->sum('stock_quantity');
    }

    public function decrementBranchStock($branchId, $quantity)
    {
        $entry = BranchProductStock::firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $this->id],
            ['stock_quantity' => 0, 'reorder_level' => $this->reorder_level ?? 10]
        );
        $entry->decrement('stock_quantity', $quantity);
        return $entry;
    }

    public function incrementBranchStock($branchId, $quantity)
    {
        $entry = BranchProductStock::firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $this->id],
            ['stock_quantity' => 0, 'reorder_level' => $this->reorder_level ?? 10]
        );
        $entry->increment('stock_quantity', $quantity);
        return $entry;
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('rank', 'like', '%' . $filters['search'] . '%'); // Added rank to search
            });
        }

        if (!empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        // Optional: Add filter by rank range
        if (!empty($filters['rank'])) {
            $query->where('rank', $filters['rank']);
        }
        
        if (!empty($filters['min_rank'])) {
            $query->where('rank', '>=', $filters['min_rank']);
        }
        
        if (!empty($filters['max_rank'])) {
            $query->where('rank', '<=', $filters['max_rank']);
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Scope products to a specific branch via branch_product_stock table.
     * This is the correct way to filter products per branch (not products.branch_id).
     */
    public function scopeForBranch($query, $branchId)
    {
        if ($branchId && $branchId !== 'all') {
            return $query->whereHas('stockEntries', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
        return $query;
    }

    /**
     * Scope to order products by rank
     */
    public function scopeOrderByRank($query, $direction = 'asc')
    {
        return $query->orderBy('rank', $direction);
    }

    /**
     * Get price based on customer type
     */
    public function getPriceForCustomerType($customerType)
    {
        switch ($customerType) {
            case 'reseller':
                return isset($this->resale_price) ? $this->resale_price : $this->sale_price;
            case 'wholesaler':
                return isset($this->wholesale_price) ? $this->wholesale_price : $this->sale_price;
            default: // normal customer
                return isset($this->sale_price) ? $this->sale_price : $this->price;
        }
    }

    /**
     * Get the box/placement information based on rank
     */
    public function getBoxPlacementAttribute()
    {
        if (empty($this->rank)) {
            return 'Not assigned';
        }
        
        return "Box/Position: {$this->rank}";
    }
  
    /**
     * Get formatted weight display
     */
    public function getFormattedWeightAttribute()
    {
        if (is_null($this->weight)) {
            return 'N/A';
        }
        
        $weightInGrams = $this->weight * 1000;
        
        // If weight is 1 kg or more, show in kg
        if ($this->weight >= 1) {
            // Remove trailing zeros
            $kg = rtrim(rtrim(number_format($this->weight, 3, '.', ''), '0'), '.');
            return $kg . ' kg';
        }
        
        // If less than 1 kg, show in grams
        return number_format($weightInGrams, 0) . ' g';
    }

    /**
     * Get weight in grams
     */
    public function getWeightInGramsAttribute()
    {
        if (is_null($this->weight)) {
            return null;
        }
        
        return $this->weight * 1000;
    }

     public function getUnitDisplayAttribute()
    {
        if ($this->unit) {
            return $this->unit->abbreviation ?: $this->unit->name;
        }
        return 'N/A';
    }

    public function getNameWithUnitAttribute()
    {
        $unitDisplay = $this->unit_display;
        return $this->name . ($unitDisplay !== 'N/A' ? " ({$unitDisplay})" : '');
    }
}

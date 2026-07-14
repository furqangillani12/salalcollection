<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'address', 'phone', 'logo', 'order_start_number', 'is_active', 'show_on_website'];

    protected $casts = ['show_on_website' => 'boolean'];

    public function stockEntries()
    {
        return $this->hasMany(BranchProductStock::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'branch_product_stock')
            ->withPivot('stock_quantity', 'reorder_level')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

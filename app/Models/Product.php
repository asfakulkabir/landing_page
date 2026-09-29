<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'regular_price',
        'sale_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('uploads/'.$this->image);
        }

        return asset('img/placeholder.svg');
    }

    public function hasDiscount(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->regular_price;
    }

    public function getCurrentPriceAttribute(): float
    {
        return (float) ($this->hasDiscount() ? $this->sale_price : $this->regular_price);
    }

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->hasDiscount() || (float) $this->regular_price <= 0) {
            return 0;
        }

        return (int) round((1 - ((float) $this->sale_price / (float) $this->regular_price)) * 100);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}

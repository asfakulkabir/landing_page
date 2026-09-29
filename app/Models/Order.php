<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending' => 'অপেক্ষমাণ',
        'confirmed' => 'নিশ্চিত হয়েছে',
        'shipped' => 'পাঠানো হয়েছে',
        'delivered' => 'পৌঁছে গেছে',
        'cancelled' => 'বাতিল হয়েছে',
    ];

    /** English status labels, used by the order management list. */
    public static array $statusesEn = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
    ];

    protected $fillable = [
        'order_number',
        'customer_name',
        'phone',
        'email',
        'address',
        'delivery_zone_id',
        'delivery_zone',
        'delivery_charge',
        'total',
        'payment_method',
        'note',
        'status',
    ];

    protected $casts = [
        'delivery_charge' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_zone_id' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function zone()
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getStatusLabelEnAttribute(): string
    {
        return self::$statusesEn[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    /**
     * Short readable reference: ord-26-0001, where 26 is the year and the four
     * digits count up within that year.
     *
     * The sequence is seeded from how many orders already carry this year's
     * prefix and then incremented until it is free, so a deleted number in the
     * middle cannot hand out a duplicate. The unique index on order_number is
     * still the final guard against two checkouts racing on the same number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'ord-'.now()->format('y');
        $sequence = static::where('order_number', 'like', $prefix.'-%')->count();

        do {
            $sequence++;
            $number = $prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}

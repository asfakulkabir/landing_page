<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    private const PRODUCTS = [
        ['Wireless Bluetooth Headset', 2499, 1899, '#4f46e5', '🎧'],
        ['Smart LED Desk Lamp', 3499, 2750, '#f59e0b', '💡'],
        ['Portable Power Bank 20000mAh', 4299, 3299, '#0ea5e9', '🔋'],
        ['Cotton Crew Neck T-Shirt', 1299, 899, '#10b981', '👕'],
        ['Stainless Steel Water Bottle', 1599, null, '#ef4444', '🍶'],
        ['Wireless Mouse Silent Click', 1899, 1450, '#8b5cf6', '🖱️'],
        ['Leather Wallet RFID Block', 1099, 799, '#64748b', '👛'],
        ['Bluetooth Speaker Pocket', 2899, 2199, '#ec4899', '🔊'],
        ['Running Sports Shoes', 4599, 3599, '#14b8a6', '👟'],
        ['A4 Notebook Pack of 3', 699, 549, '#6366f1', '📓'],
        ['Ceramic Coffee Mug', 899, null, '#a16207', '☕'],
        ['Yoga Mat Anti Slip', 1999, 1499, '#84cc16', '🧘'],
    ];

    private const TESTIMONIALS = [
        ['#22c55e', 'A', 'Rifat H.', 'Verified Buyer'],
        ['#f97316', 'S', 'Sumaiya T.', 'Verified Buyer'],
        ['#3b82f6', 'T', 'Tanvir A.', 'Verified Buyer'],
        ['#a855f7', 'N', 'Nadia R.', 'Verified Buyer'],
        ['#e11d48', 'M', 'Mehedi H.', 'Verified Buyer'],
        ['#0891b2', 'F', 'Farhan K.', 'Verified Buyer'],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('products');
        $disk->makeDirectory('testimonials');

        foreach (self::PRODUCTS as $i => [$name, $regular, $sale, $colour, $emoji]) {
            if (Product::where('name', $name)->exists()) {
                continue;
            }

            $file = 'products/demo-product-'.($i + 1).'.svg';
            $disk->put($file, $this->productSvg($name, $colour, $emoji));

            Product::create([
                'name' => $name,
                'image' => $file,
                'regular_price' => $regular,
                'sale_price' => $sale,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }

        foreach (self::TESTIMONIALS as $i => [$colour, $initial, $name, $role]) {
            if (Testimonial::where('image', 'testimonials/demo-testimonial-'.($i + 1).'.svg')->exists()) {
                continue;
            }

            $file = 'testimonials/demo-testimonial-'.($i + 1).'.svg';
            $disk->put($file, $this->testimonialSvg($colour, $initial, $name, $role));

            Testimonial::create([
                'image' => $file,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }

        $this->demoOrders();
    }

    private function demoOrders(): void
    {
        if (Order::exists()) {
            return;
        }

        $zones = DeliveryZone::ordered()->get();

        if ($zones->isEmpty() || Product::count() === 0) {
            return;
        }

        $samples = [
            ['Ahmed Hasan', '01711223344', 'ahmed@example.com', 'Flat 3B, House 21, Road 7, Dhanmondi, Dhaka-1209', 'Please call after 6pm', 0, 'pending', 2],
            ['Sumaiya Tabassum', '01822334455', 'sumaiya@example.com', 'House 44, Lane 2, Mirpur-10, Dhaka', null, 0, 'confirmed', 1],
            ['Tanvir Ahmed', '01933445566', 'tanvir@example.com', 'Plot 9, Sector 4, Uttara, Dhaka-1207', 'Leave with the guard', 1, 'shipped', 3],
            ['Nadia Rahman', '01644556677', 'nadia@example.com', 'House 7, Road 12, Agrabad, Chittagong', null, 1, 'delivered', 5],
        ];

        foreach ($samples as $i => [$name, $phone, $email, $address, $note, $zoneIndex, $status, $ageDays]) {
            $zone = $zones[$zoneIndex % $zones->count()];

            $picked = Product::ordered()->skip($i % 4)->take(2)->get();
            $lines = [];
            $subtotal = 0;

            foreach ($picked as $product) {
                $qty = 1 + (($i + $product->id) % 3);
                $price = $product->current_price;
                $lineTotal = $price * $qty;
                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $lineTotal,
                ];
            }

            $order = Order::create([
                'order_number' => 'ORD-'.now()->subDays($ageDays)->format('Ymd').'-DEMO'.($i + 1),
                'customer_name' => $name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'delivery_zone_id' => $zone->id,
                'delivery_zone' => $zone->name,
                'delivery_charge' => $zone->charge,
                'total' => $subtotal + (float) $zone->charge,
                'payment_method' => 'cod',
                'note' => $note,
                'status' => $status,
            ]);

            $order->items()->createMany($lines);
            $order->forceFill(['created_at' => now()->subDays($ageDays), 'updated_at' => now()->subDays($ageDays)])->save();
        }
    }

    private function productSvg(string $name, string $colour, string $emoji): string
    {
        $short = Str::limit($name, 26);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#ffffff" stop-opacity="0.28"/>
      <stop offset="1" stop-color="#000000" stop-opacity="0.16"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="{$colour}"/>
  <rect width="600" height="600" fill="url(#g)"/>
  <circle cx="480" cy="120" r="90" fill="#ffffff" opacity="0.12"/>
  <circle cx="110" cy="500" r="120" fill="#000000" opacity="0.10"/>
  <text x="300" y="345" font-size="190" text-anchor="middle">{$emoji}</text>
  <text x="300" y="450" font-family="Segoe UI, Arial, sans-serif" font-size="30" font-weight="700" fill="#ffffff" text-anchor="middle">{$short}</text>
</svg>
SVG;
    }

    private function testimonialSvg(string $colour, string $initial, string $name, string $role): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600" viewBox="0 0 600 600">
  <defs>
    <linearGradient id="t" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$colour}"/>
      <stop offset="1" stop-color="#14162b"/>
    </linearGradient>
  </defs>
  <rect width="600" height="600" fill="url(#t)"/>
  <circle cx="300" cy="235" r="112" fill="#ffffff" opacity="0.92"/>
  <text x="300" y="288" font-family="Segoe UI, Arial, sans-serif" font-size="120" font-weight="700" fill="{$colour}" text-anchor="middle">{$initial}</text>
  <text x="300" y="430" font-family="Segoe UI, Arial, sans-serif" font-size="46" font-weight="700" fill="#ffffff" text-anchor="middle">{$name}</text>
  <text x="300" y="482" font-family="Segoe UI, Arial, sans-serif" font-size="27" fill="#ffffff" opacity="0.85" text-anchor="middle">{$role}</text>
  <g fill="#ffd166" opacity="0.95">
    <text x="222" y="540" font-size="34" text-anchor="middle">&#9733;</text>
    <text x="300" y="540" font-size="34" text-anchor="middle">&#9733;</text>
    <text x="378" y="540" font-size="34" text-anchor="middle">&#9733;</text>
  </g>
</svg>
SVG;
    }
}

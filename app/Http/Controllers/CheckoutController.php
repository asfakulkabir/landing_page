<?php

namespace App\Http\Controllers;

use App\Mail\OrderPlaced;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Services\PixelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * Reduce any accepted Bangladeshi mobile format to the canonical 01XXXXXXXXX.
     *
     * Accepts 01…, 88001…, +88001… and 0088001…, with spaces, dashes, dots or
     * brackets anywhere. Returns null when the number is not a valid BD mobile.
     * Keep in sync with normaliseBdPhone() in public/js/site.js.
     */
    public static function normaliseBdPhone(?string $raw): ?string
    {
        $digits = preg_replace('/[\s\-().]/', '', (string) $raw);

        /* Strip an international prefix. Note the international form drops the
           domestic trunk 0 (+8801712345678), so it has to go back on. */
        if (preg_match('/^(?:\+?00880|\+?880)/', $digits) === 1) {
            $digits = preg_replace('/^(?:\+?00880|\+?880)/', '', $digits);

            if ($digits !== '' && $digits[0] !== '0') {
                $digits = '0'.$digits;
            }
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) === 1 ? $digits : null;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'delivery_zone_id' => ['required', 'integer', 'exists:delivery_zones,id'],
            'note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.selected' => ['nullable'],
        ], [], [
            'customer_name' => 'name',
            'delivery_zone_id' => 'delivery zone',
            'items' => 'products',
        ]);

        $phone = self::normaliseBdPhone($data['phone']);

        if ($phone === null) {
            throw ValidationException::withMessages([
                'phone' => __('validation.bd_phone'),
            ]);
        }

        $zone = DeliveryZone::active()->find($data['delivery_zone_id']);

        if (! $zone) {
            throw ValidationException::withMessages([
                'delivery_zone_id' => 'Please choose a valid delivery zone.',
            ]);
        }

        /* Keep only the ticked rows. If nothing was ticked fall back to the
           first product so a customer can order with a single click. */
        $rows = collect($data['items'])
            ->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => max(1, min(99, (int) $item['quantity'])),
                'selected' => ! empty($item['selected']),
            ])
            ->filter(fn ($item) => $item['selected'])
            ->values();

        if ($rows->isEmpty()) {
            $rows = collect($data['items'])->take(1)->map(fn ($item) => [
                'product_id' => (int) $item['product_id'],
                'quantity' => max(1, min(99, (int) $item['quantity'])),
            ])->values();
        }

        $products = Product::active()
            ->whereIn('id', $rows->pluck('product_id')->all())
            ->get()
            ->keyBy('id');

        if ($products->count() !== $rows->pluck('product_id')->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'One of the selected products is no longer available. Please choose again.',
            ]);
        }

        $order = DB::transaction(function () use ($rows, $products, $zone, $data, $phone) {
            $lines = [];
            $subtotal = 0;

            foreach ($rows as $row) {
                $product = $products->get($row['product_id']);
                $price = $product->current_price;
                $lineTotal = $price * $row['quantity'];

                $subtotal += $lineTotal;

                $lines[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'price' => $price,
                    'quantity' => $row['quantity'],
                    'subtotal' => $lineTotal,
                ];
            }

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_name' => $data['customer_name'],
                'phone' => $phone,
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'delivery_zone_id' => $zone->id,
                'delivery_zone' => $zone->name,
                'delivery_charge' => $zone->charge,
                'total' => $subtotal + (float) $zone->charge,
                'payment_method' => 'cod',
                'note' => $data['note'] ?? null,
                'status' => 'pending',
            ]);

            $order->items()->createMany($lines);

            return $order;
        });

        /* Notify after the transaction has committed, so a mail failure can never
           roll back a placed order. Delivery problems are logged, not surfaced. */
        $notifyEmail = Setting::value('notify_email');

        if (! empty($notifyEmail)) {
            try {
                Mail::to($notifyEmail)->send(new OrderPlaced($order->load('items')));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('thank-you', $order->order_number);
    }

    public function thankYou(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->firstOrFail();

        /* Purchase is reported from here rather than at checkout time, so it counts
           as a completed order. The browser half renders in the view and shares
           this event_id, so the two collapse into one conversion per destination. */
        PixelService::sendPurchase($order);

        return view('thank-you', [
            'order' => $order,
            'settings' => Setting::all_settings(),
            'pixelPurchase' => [
                'eventId' => PixelService::purchaseEventId($order),
                'data' => PixelService::purchasePayload($order),
            ],
        ]);
    }
}

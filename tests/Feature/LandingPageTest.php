<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Services\PixelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    private function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Product',
            'image' => 'products/test.png',
            'regular_price' => 1000,
            'sale_price' => 800,
            'is_active' => true,
            'sort_order' => 1,
        ], $attrs));
    }

    private function zone(array $attrs = []): DeliveryZone
    {
        return DeliveryZone::create(array_merge([
            'name' => 'Inside City',
            'charge' => 60,
            'estimated_days' => 1,
            'is_active' => true,
            'sort_order' => 1,
        ], $attrs));
    }

    /* ------------------------------------------------------- public pages */

    public function test_home_page_loads_with_products_and_zones(): void
    {
        $this->product(['name' => 'Visible Product']);
        $this->product(['name' => 'Hidden Product', 'is_active' => false, 'sort_order' => 2]);
        $this->zone();

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Visible Product')
            ->assertDontSee('Hidden Product')
            ->assertSee('Inside City')
            ->assertSee('Place Order', false);
    }

    public function test_home_page_shows_sale_and_regular_price(): void
    {
        $this->product(['name' => 'Discounted', 'regular_price' => 1000, 'sale_price' => 750]);

        $this->get('/')
            ->assertOk()
            ->assertSee('৳750')
            ->assertSee('৳1,000');
    }

    public function test_home_page_preselects_product_from_query_string(): void
    {
        $first = $this->product(['name' => 'First', 'sort_order' => 1]);
        $third = $this->product(['name' => 'Third', 'sort_order' => 3]);

        $response = $this->get('/?product='.$third->id);

        $response->assertOk();
        $this->assertStringContainsString('data-product-row="'.$third->id.'"', $response->getContent());
    }

    public function test_invalid_product_query_falls_back_to_first_product(): void
    {
        $this->product(['name' => 'First', 'sort_order' => 1]);

        $response = $this->get('/?product=99999');

        $response->assertOk()->assertSee('First');
    }

    public function test_testimonials_section_only_shows_active_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('testimonials/a.png', 'a');
        Storage::disk('public')->put('testimonials/b.png', 'b');

        Testimonial::create(['image' => 'testimonials/a.png', 'is_active' => true, 'sort_order' => 1]);
        Testimonial::create(['image' => 'testimonials/b.png', 'is_active' => false, 'sort_order' => 2]);

        $this->get('/')
            ->assertOk()
            ->assertSee('testimonials/a.png')
            ->assertDontSee('testimonials/b.png');
    }

    public function test_missing_image_falls_back_to_placeholder(): void
    {
        Storage::fake('public');

        Testimonial::create(['image' => 'testimonials/gone.png', 'is_active' => true, 'sort_order' => 1]);

        $this->get('/')
            ->assertOk()
            ->assertSee('img/placeholder.svg')
            ->assertDontSee('testimonials/gone.png');
    }

    public function test_no_tailwind_or_external_libraries_are_referenced(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('tailwind', $html);
        $this->assertStringNotContainsString('cdn.', $html);
        $this->assertStringContainsString('/css/site.css', $html);
        $this->assertStringContainsString('/js/site.js', $html);
    }

    /* ------------------------------------------------------ checkout flow */

    public function test_checkout_creates_order_and_redirects_to_thank_you(): void
    {
        $product = $this->product(['regular_price' => 1000, 'sale_price' => 800]);
        $zone = $this->zone(['charge' => 60]);

        $response = $this->post('/checkout', [
            'customer_name' => 'Rahim Uddin',
            'phone' => '01712345678',
            'email' => 'rahim@example.com',
            'address' => 'House 5, Road 2, Dhaka',
            'delivery_zone_id' => $zone->id,
            'note' => 'Call before delivery',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'selected' => '1'],
            ],
        ]);

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect(route('thank-you', $order->order_number));

        // 800 x 2 + 60 delivery = 1660
        $this->assertEquals(1660.00, (float) $order->total);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(1600.00, (float) $order->subtotal);
        $this->assertCount(1, $order->items);
        $this->assertEquals(2, $order->items->first()->quantity);
    }

    public function test_thank_you_page_shows_order_details(): void
    {
        $product = $this->product(['name' => 'Nice Chair', 'regular_price' => 1000, 'sale_price' => 900]);
        $zone = $this->zone();

        $this->post('/checkout', [
            'customer_name' => 'Karim',
            'phone' => '01899999999',
            'address' => 'Some address',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ]);

        $order = Order::first();

        $this->get(route('thank-you', $order->order_number))
            ->assertOk()
            ->assertSee('Thank you for your order')
            ->assertSee($order->order_number)
            ->assertSee('Karim')
            ->assertSee('Nice Chair')
            ->assertSee('900.00');
    }

    public function test_only_selected_products_are_ordered(): void
    {
        $a = $this->product(['name' => 'Alpha', 'regular_price' => 100, 'sale_price' => null, 'sort_order' => 1]);
        $b = $this->product(['name' => 'Beta', 'regular_price' => 200, 'sale_price' => null, 'sort_order' => 2]);
        $zone = $this->zone(['charge' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [
                ['product_id' => $a->id, 'quantity' => 1],
                ['product_id' => $b->id, 'quantity' => 1, 'selected' => '1'],
            ],
        ]);

        $order = Order::first();

        $this->assertCount(1, $order->items);
        $this->assertEquals('Beta', $order->items->first()->product_name);
        $this->assertEquals(200.00, (float) $order->total);
    }

    public function test_nothing_selected_falls_back_to_the_first_product(): void
    {
        $a = $this->product(['name' => 'Alpha', 'regular_price' => 100, 'sale_price' => null, 'sort_order' => 1]);
        $b = $this->product(['name' => 'Beta', 'regular_price' => 200, 'sale_price' => null, 'sort_order' => 2]);
        $zone = $this->zone(['charge' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [
                ['product_id' => $a->id, 'quantity' => 3],
                ['product_id' => $b->id, 'quantity' => 1],
            ],
        ]);

        $order = Order::first();

        $this->assertCount(1, $order->items);
        $this->assertEquals('Alpha', $order->items->first()->product_name);
        $this->assertEquals(3, $order->items->first()->quantity);
    }

    public function test_inactive_product_cannot_be_ordered(): void
    {
        $product = $this->product(['is_active' => false, 'regular_price' => 100, 'sale_price' => null]);
        $zone = $this->zone();

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ])->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
    }

    public function test_inactive_delivery_zone_cannot_be_used(): void
    {
        $product = $this->product(['regular_price' => 100, 'sale_price' => null]);
        $zone = $this->zone(['is_active' => false]);

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ])->assertSessionHasErrors('delivery_zone_id');
    }

    public function test_checkout_requires_name_phone_address_zone_and_items(): void
    {
        $this->post('/checkout', [])->assertSessionHasErrors([
            'customer_name', 'phone', 'address', 'delivery_zone_id', 'items',
        ]);
    }

    public function test_checkout_validates_email_format(): void
    {
        $product = $this->product();
        $zone = $this->zone();

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'email' => 'not-an-email',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ])->assertSessionHasErrors('email');
    }

    public function test_quantity_is_capped_and_floored(): void
    {
        $product = $this->product(['regular_price' => 100, 'sale_price' => null]);
        $zone = $this->zone(['charge' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 500, 'selected' => '1']],
        ])->assertSessionHasErrors('items.0.quantity');

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 0, 'selected' => '1']],
        ])->assertSessionHasErrors('items.0.quantity');
    }

    public function test_free_delivery_zone_produces_correct_total(): void
    {
        $product = $this->product(['regular_price' => 500, 'sale_price' => null]);
        $zone = $this->zone(['charge' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'Test',
            'phone' => '01700000000',
            'address' => 'Addr',
            'delivery_zone_id' => $zone->id,
            'items' => [['product_id' => $product->id, 'quantity' => 4, 'selected' => '1']],
        ]);

        $order = Order::first();
        $this->assertEquals(2000.00, (float) $order->total);
        $this->assertEquals(0.00, (float) $order->delivery_charge);
    }

    public function test_order_numbers_are_unique(): void
    {
        $this->product(['regular_price' => 100, 'sale_price' => null]);
        $zone = $this->zone(['charge' => 0]);

        for ($i = 0; $i < 3; $i++) {
            $this->post('/checkout', [
                'customer_name' => 'Test '.$i,
                'phone' => '0170000000'.$i,
                'address' => 'Addr',
                'delivery_zone_id' => $zone->id,
                'items' => [['product_id' => Product::first()->id, 'quantity' => 1, 'selected' => '1']],
            ]);
        }

        $numbers = Order::pluck('order_number')->all();

        $this->assertCount(3, $numbers);
        $this->assertCount(3, array_unique($numbers));
    }

    public function test_thank_you_page_404s_for_unknown_order(): void
    {
        $this->get('/thank-you/ORD-DOES-NOT-EXIST')->assertNotFound();
    }

    /* ------------------------------------------------- phone verification */

    #[DataProvider('acceptedBdPhoneProvider')]
    public function test_bd_phone_formats_are_normalised_to_the_canonical_form(string $typed, string $stored): void
    {
        $product = $this->product(['sale_price' => null, 'regular_price' => 100]);
        $zone = $this->zone(['charge' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'Karim',
            'phone' => $typed,
            'address' => 'X',
            'delivery_zone_id' => $zone->id,
            'items' => [0 => ['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ])->assertSessionHasNoErrors();

        $this->assertSame($stored, Order::first()->phone);
    }

    public static function acceptedBdPhoneProvider(): array
    {
        return [
            'plain 01' => ['01712345678', '01712345678'],
            '880 prefix' => ['8801712345678', '01712345678'],
            'plus 880 prefix' => ['+8801712345678', '01712345678'],
            '00880 prefix' => ['008801712345678', '01712345678'],
            'spaces and dashes' => ['+880 1712-345 678', '01712345678'],
            'operator 013' => ['01312345678', '01312345678'],
            'operator 019' => ['+8801912345678', '01912345678'],
        ];
    }

    #[DataProvider('rejectedBdPhoneProvider')]
    public function test_invalid_bd_phone_numbers_are_rejected(string $typed): void
    {
        $product = $this->product(['sale_price' => null, 'regular_price' => 100]);
        $zone = $this->zone(['charge' => 0]);

        $this->post('/checkout', [
            'customer_name' => 'Karim',
            'phone' => $typed,
            'address' => 'X',
            'delivery_zone_id' => $zone->id,
            'items' => [0 => ['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ])->assertSessionHasErrors('phone');

        $this->assertSame(0, Order::count());
    }

    public static function rejectedBdPhoneProvider(): array
    {
        return [
            'too short' => ['0171234567'],
            'too long' => ['017123456789'],
            'landline prefix 02' => ['0212345678'],
            'invalid operator 012' => ['01212345678'],
            'operator 010' => ['01012345678'],
            'letters' => ['0171234567a'],
            'not a number' => ['abcdefghijk'],
        ];
    }

    public function test_the_checkout_form_exposes_live_phone_verification(): void
    {
        $this->product();

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('data-phone-input', $html);
        $this->assertStringContainsString('data-phone-ok', $html);
        $this->assertStringContainsString('id="phone-hint"', $html);
        $this->assertStringContainsString('aria-describedby="phone-hint"', $html);
        $this->assertStringContainsString('inputmode="tel"', $html);
    }

    /* ----------------------------------------------------- order block */

    public function test_the_order_block_has_steppers_and_a_line_total(): void
    {
        $this->product(['name' => 'পিচ টিস্যু সিল্ক শাড়ি', 'sale_price' => null, 'regular_price' => 1430]);
        $this->zone();

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('আপনার অর্ডার', $html);
        $this->assertStringContainsString('class="picker-row"', $html);
        $this->assertStringContainsString('picker-row__thumb', $html);
        $this->assertStringContainsString('class="qty-stepper"', $html);
        $this->assertStringContainsString('data-qty-up', $html);
        $this->assertStringContainsString('data-qty-down', $html);
        $this->assertStringContainsString('data-row-line', $html);
        $this->assertStringContainsString('data-row-qty', $html);
        $this->assertStringContainsString('data-row-checkbox', $html);

        /* unit price x default qty 1 */
        $this->assertStringContainsString('৳1,430</span>', $html);

        /* the order review is a real table, like the reference shop_table */
        $this->assertStringContainsString('<table class="review">', $html);
        $this->assertStringContainsString('<thead>', $html);
        $this->assertStringContainsString('<tbody data-summary-list>', $html);
        $this->assertStringContainsString('<tfoot>', $html);
        $this->assertStringContainsString('review__col-product', $html);
        $this->assertStringContainsString('review__col-total', $html);
        $this->assertStringContainsString('data-summary-subtotal', $html);
        $this->assertStringContainsString('data-summary-delivery', $html);
        $this->assertStringContainsString('data-summary-total', $html);

        /* the reference uses a single-line address input, not a textarea */
        $this->assertStringContainsString('<input type="text" id="address"', $html);
        $this->assertStringNotContainsString('<textarea id="address"', $html);
    }

    public function test_the_line_total_reflects_the_saved_quantity(): void
    {
        $this->product(['name' => 'Pich Tissu', 'sale_price' => null, 'regular_price' => 1430]);
        $this->zone();

        /* simulate a validation bounce-back with qty 3 */
        $this->withSession([
            '_old_input' => [
                'items' => [0 => ['product_id' => 1, 'quantity' => 3, 'selected' => '1']],
            ],
        ])->get('/')->assertOk()->assertSee('৳4,290', false);
    }

    /* ------------------------------------------------- order notifications */
    private function placeOrder(array $overrides = [])
    {
        $product = $this->product(['name' => 'Notify Product', 'sale_price' => null, 'regular_price' => 500]);
        $zone = $this->zone(['name' => 'Notify Zone', 'charge' => 60]);

        return $this->post('/checkout', array_merge([
            'customer_name' => 'Karim',
            'phone' => '01700000000',
            'address' => 'Somewhere, Dhaka',
            'delivery_zone_id' => $zone->id,
            'items' => [
                0 => ['product_id' => $product->id, 'quantity' => 2, 'selected' => '1'],
            ],
        ], $overrides));
    }

    public function test_new_order_emails_the_configured_notification_address(): void
    {
        Mail::fake();
        Setting::put('notify_email', 'orders@store.test');

        $response = $this->placeOrder();
        $order = Order::first();

        $response->assertRedirect(route('thank-you', $order->order_number));

        Mail::assertSent(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
            return $mail->hasTo('orders@store.test')
                && $mail->order->is($order);
        });
    }

    public function test_notification_email_carries_the_order_details(): void
    {
        Mail::fake();
        Setting::put('notify_email', 'orders@store.test');
        Setting::put('currency_symbol', '৳');

        $this->placeOrder();
        $order = Order::with('items')->first();

        Mail::assertSent(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
            $rendered = $mail->render();

            return str_contains($rendered, $order->order_number)
                && str_contains($rendered, 'Karim')
                && str_contains($rendered, '01700000000')
                && str_contains($rendered, 'Somewhere, Dhaka')
                && str_contains($rendered, 'Notify Product')
                && str_contains($rendered, 'Notify Zone')
                && str_contains($rendered, '৳1,060');
        });
    }

    public function test_no_notification_email_is_sent_when_the_field_is_blank(): void
    {
        Mail::fake();

        $this->assertSame('', Setting::value('notify_email'));

        $this->placeOrder()->assertRedirect();

        Mail::assertNothingSent();
    }

    public function test_a_mail_failure_does_not_lose_the_order(): void
    {
        Setting::put('notify_email', 'orders@store.test');

        Mail::shouldReceive('to->send')->andThrow(new \RuntimeException('SMTP unavailable'));

        $response = $this->placeOrder();
        $order = Order::first();

        $response->assertRedirect(route('thank-you', $order->order_number));
        $this->assertSame(1, Order::count());
        $this->assertSame(1060.0, (float) $order->total);
    }

    /* --------------------------------------------------- meta / tiktok pixels */
    private function pixelOrder(array $orderAttrs = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'ORD-TEST-0001',
            'customer_name' => 'Karim',
            'phone' => '01712345678',
            'email' => 'karim@example.com',
            'address' => 'X',
            'delivery_zone' => 'Inside City',
            'delivery_charge' => 60,
            'total' => 1490,
            'payment_method' => 'cod',
            'status' => 'pending',
        ], $orderAttrs));

        $order->items()->create([
            'product_id' => $order->id,
            'product_name' => 'পিচ টিস্যু সিল্ক শাড়ি',
            'price' => 1430,
            'quantity' => 1,
            'subtotal' => 1430,
        ]);

        return $order->fresh('items');
    }

    private function configureMetaPixel(array $overrides = []): void
    {
        foreach (array_merge([
            'meta_pixel_id' => '1234567890123456',
            'meta_capi_token' => 'EAABsecret',
            'meta_test_code' => '',
            'pixel_site_url' => 'https://shop.test',
        ], $overrides) as $k => $v) {
            Setting::put($k, $v);
        }
    }

    public function test_nothing_is_sent_when_no_pixel_is_configured(): void
    {
        Http::fake();

        $this->pixelOrder();
        $this->get('/thank-you/ORD-TEST-0001')->assertOk();

        Http::assertNothingSent();
    }

    public function test_no_pixel_snippets_render_when_unconfigured(): void
    {
        $this->product();

        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('fbevents.js', $html);
        $this->assertStringNotContainsString('analytics.tiktok.com', $html);
        $this->assertStringNotContainsString('js/pixel.js', $html);
    }

    public function test_meta_capi_receives_a_hashed_purchase_event(): void
    {
        Http::fake();
        $this->configureMetaPixel();
        $order = $this->pixelOrder();

        $this->get('/thank-you/ORD-TEST-0001')->assertOk();

        Http::assertSent(function ($request) use ($order) {
            $body = $request->data();
            $event = $body['data'][0];

            $expectations = [
                'event_name' => 'Purchase',
                'action_source' => 'website',
                'event_id' => 'ord_'.$order->order_number,
                'event_source_url' => 'https://shop.test/thank-you/'.$order->order_number,
            ];

            foreach ($expectations as $k => $v) {
                if (($event[$k] ?? null) !== $v) {
                    return false;
                }
            }

            // PII must be SHA-256, never plaintext
            $ud = $event['user_data'];
            if ($ud['ph'] !== hash('sha256', '8801712345678')) {
                return false;
            }
            if ($ud['em'] !== hash('sha256', 'karim@example.com')) {
                return false;
            }
            if (in_array('01712345678', $ud, true) || in_array('karim@example.com', $ud, true)) {
                return false;
            }

            $cd = $event['custom_data'];
            if ($cd['value'] != 1490.0 || $cd['currency'] !== 'BDT' || $cd['num_items'] !== 1) {
                return false;
            }
            if ($cd['order_id'] !== $order->order_number) {
                return false;
            }
            if ($cd['content_ids'] !== ['product_'.$order->items->first()->product_id]) {
                return false;
            }

            return str_contains($request->url(), 'graph.facebook.com')
                && str_contains($request->url(), '1234567890123456')
                && str_contains($request->url(), 'access_token=EAABsecret');
        });
    }

    public function test_meta_test_event_code_is_included_then_omitted(): void
    {
        Http::fake();
        $this->configureMetaPixel(['meta_test_code' => 'TEST1234']);

        $this->pixelOrder();
        $this->get('/thank-you/ORD-TEST-0001')->assertOk();

        Http::assertSent(fn ($r) => ($r->data()['test_event_code'] ?? null) === 'TEST1234');

        Http::fake();
        $this->configureMetaPixel(['meta_test_code' => '']);
        $this->pixelOrder(['order_number' => 'ORD-TEST-0002']);
        $this->get('/thank-you/ORD-TEST-0002')->assertOk();

        Http::assertSent(fn ($r) => ! array_key_exists('test_event_code', $r->data()));
    }

    public function test_tiktok_events_api_receives_complete_payment(): void
    {
        Http::fake();
        Setting::put('tiktok_pixel_id', 'C0abcdefghijk');
        Setting::put('tiktok_api_token', 'ttk-token');
        Setting::put('tiktok_test_code', 'TT123');
        Setting::put('pixel_site_url', 'https://shop.test');
        $order = $this->pixelOrder();

        $this->get('/thank-you/ORD-TEST-0001')->assertOk();

        Http::assertSent(function ($request) use ($order) {
            $body = $request->data();
            $event = $body['data'][0];

            if (($event['event'] ?? null) !== 'CompletePayment') {
                return false;
            }
            if (($event['event_id'] ?? null) !== 'ord_'.$order->order_number) {
                return false;
            }
            if (($body['test_event_code'] ?? null) !== 'TT123') {
                return false;
            }
            if (($body['event_source'] ?? null) !== 'web') {
                return false;
            }
            if (($body['event_source_id'] ?? null) !== 'C0abcdefghijk') {
                return false;
            }
            if (($event['currency'] ?? null) !== 'BDT' || (float) ($event['value'] ?? 0) !== 1490.0) {
                return false;
            }
            if (($event['user']['phone'] ?? null) !== hash('sha256', '8801712345678')) {
                return false;
            }
            $c = $event['contents'][0] ?? [];
            if (($c['currency'] ?? null) !== 'BDT' || ($c['content_name'] ?? null) !== 'পিচ টিস্যু সিল্ক শাড়ি') {
                return false;
            }

            return str_contains($request->url(), 'business-api.tiktokapis.com')
                && $request->hasHeader('Access-Token', 'ttk-token');
        });
    }

    public function test_tiktok_event_omits_user_block_when_there_is_no_email_or_phone(): void
    {
        Http::fake();
        Setting::put('tiktok_pixel_id', 'C0abcdefghijk');
        Setting::put('tiktok_api_token', 'ttk-token');
        Setting::put('pixel_site_url', 'https://shop.test');

        $this->pixelOrder(['email' => null, 'phone' => '']);

        $this->get('/thank-you/ORD-TEST-0001')->assertOk();

        Http::assertSent(fn ($r) => ! array_key_exists('user', $r->data()['data'][0]));
    }

    public function test_purchase_fires_on_thank_you_not_at_checkout(): void
    {
        Http::fake();
        $this->configureMetaPixel();
        $product = Product::create(['name' => 'P', 'regular_price' => 100, 'sale_price' => null, 'is_active' => true]);
        $zone = DeliveryZone::create(['name' => 'Z', 'charge' => 0, 'is_active' => true]);

        $this->post('/checkout', [
            'customer_name' => 'Karim', 'phone' => '01712345678', 'address' => 'X',
            'delivery_zone_id' => $zone->id,
            'items' => [0 => ['product_id' => $product->id, 'quantity' => 1, 'selected' => '1']],
        ])->assertSessionHasNoErrors();

        Http::assertNothingSent();

        $order = Order::first();
        $this->get(route('thank-you', $order->order_number))->assertOk();

        Http::assertSentCount(1);
    }

    public function test_browser_purchase_shares_the_server_event_id(): void
    {
        Http::fake();
        $this->configureMetaPixel();
        $order = $this->pixelOrder();

        $html = $this->get('/thank-you/ORD-TEST-0001')->getContent();

        $this->assertStringContainsString("window.LPTrack('Purchase'", $html);
        $this->assertStringContainsString('ord_'.$order->order_number, $html);
    }

    public function test_browser_snippets_only_render_when_configured(): void
    {
        $this->product();
        $this->configureMetaPixel();

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('fbevents.js', $html);
        $this->assertStringContainsString("fbq('init', \"1234567890123456\")", $html);
        $this->assertStringContainsString('js/pixel.js', $html);
        $this->assertStringNotContainsString('analytics.tiktok.com', $html);
    }

    public function test_product_cards_carry_pixel_content_data(): void
    {
        $this->product(['name' => 'Silk Saree']);

        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('data-content-id="product_', $html);
        $this->assertStringContainsString('data-content-name="Silk Saree"', $html);
        $this->assertStringContainsString('data-content-price=', $html);
        $this->assertStringContainsString('data-content-image=', $html);
    }

    public function test_a_failing_api_does_not_break_the_thank_you_page(): void
    {
        Http::fake(fn () => throw new \RuntimeException('network down'));

        $this->configureMetaPixel();
        $this->pixelOrder();

        $this->get('/thank-you/ORD-TEST-0001')
            ->assertOk()
            ->assertSee('অর্ডারের জন্য ধন্যবাদ!', false);
    }

    public function test_http_error_from_the_api_does_not_break_the_page(): void
    {
        Http::fake(['*' => Http::response('bad request', 400)]);

        $this->configureMetaPixel();
        $this->pixelOrder();

        $this->get('/thank-you/ORD-TEST-0001')->assertOk();
    }

    public function test_event_id_is_stable_across_reloads(): void
    {
        $order = $this->pixelOrder();

        $first = PixelService::purchaseEventId($order);
        $second = PixelService::purchaseEventId($order->fresh());

        $this->assertSame($first, $second);
        $this->assertSame('ord_ORD-TEST-0001', $first);
    }

    public function test_site_url_falls_back_to_app_url(): void
    {
        Setting::put('pixel_site_url', '');

        $this->assertSame(rtrim(config('app.url'), '/'), PixelService::siteUrl());
        $this->assertSame(
            rtrim(config('app.url'), '/').'/thank-you/X1',
            PixelService::siteUrl('thank-you/X1')
        );
    }
}

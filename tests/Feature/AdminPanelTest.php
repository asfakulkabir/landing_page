<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => 'secret1234',
        ]);
    }

    /* ---------------------------------------------------------- auth gate */

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        foreach ([
            '/admin',
            '/admin/products',
            '/admin/orders',
            '/admin/delivery-zones',
            '/admin/testimonials',
            '/admin/settings',
        ] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_admin_can_log_in_and_log_out(): void
    {
        $this->admin();

        $this->post('/admin/login', [
            'email' => 'admin@admin.com',
            'password' => 'secret1234',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();

        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->admin();

        $this->post('/admin/login', [
            'email' => 'admin@admin.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_page_is_visible_to_guests(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('Admin Login');
    }

    /* ------------------------------------------------------------- products */

    public function test_product_can_be_created_with_image_and_prices(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/products', [
            'name' => 'Desk Lamp',
            'regular_price' => '2000',
            'sale_price' => '1500',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('lamp.jpg'),
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::first();

        $this->assertNotNull($product);
        $this->assertEquals('Desk Lamp', $product->name);
        $this->assertEquals(2000.00, (float) $product->regular_price);
        $this->assertEquals(1500.00, (float) $product->sale_price);
        $this->assertNotNull($product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_product_sale_price_must_be_lower_than_regular_price(): void
    {
        $this->actingAs($this->admin())->post('/admin/products', [
            'name' => 'Bad Price',
            'regular_price' => '100',
            'sale_price' => '900',
        ]);

        $product = Product::first();
        $this->assertNull($product->sale_price);
        $this->assertEquals(100.00, (float) $product->regular_price);
    }

    public function test_product_creation_requires_name_and_regular_price(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/products', ['sale_price' => '50'])
            ->assertSessionHasErrors(['name', 'regular_price']);
    }

    public function test_product_image_must_be_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/admin/products', [
            'name' => 'Not An Image',
            'regular_price' => '100',
            'image' => UploadedFile::fake()->create('malware.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, Product::count());
    }

    public function test_product_can_be_updated_and_image_replaced(): void
    {
        Storage::fake('public');

        $product = Product::create([
            'name' => 'Old Name',
            'regular_price' => 500,
            'sale_price' => null,
        ]);

        $this->actingAs($this->admin())->put('/admin/products/'.$product->id, [
            'name' => 'New Name',
            'regular_price' => '800',
            'sale_price' => '650',
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertEquals('New Name', $product->name);
        $this->assertEquals(800.00, (float) $product->regular_price);
        $this->assertEquals(650.00, (float) $product->sale_price);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_deleting_a_product_removes_its_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('gone.png');
        $product = Product::create([
            'name' => 'To Delete',
            'regular_price' => 100,
            'image' => $file->store('products', 'public'),
        ]);

        $path = $product->image;

        $this->actingAs($this->admin())
            ->delete('/admin/products/'.$product->id)
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame(0, Product::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_product_visibility_can_be_toggled(): void
    {
        $product = Product::create(['name' => 'Toggle Me', 'regular_price' => 100, 'is_active' => true]);

        $this->actingAs($this->admin())->post('/admin/products/'.$product->id.'/toggle');

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_product_list_can_be_searched_and_filtered(): void
    {
        $this->actingAs($this->admin());
        Product::create(['name' => 'Blue Chair', 'regular_price' => 100, 'is_active' => true]);
        Product::create(['name' => 'Red Table', 'regular_price' => 200, 'is_active' => false]);

        $this->get('/admin/products?q=chair')->assertOk()->assertSee('Blue Chair')->assertDontSee('Red Table');
        $this->get('/admin/products?status=inactive')->assertOk()->assertSee('Red Table')->assertDontSee('Blue Chair');
    }

    /* --------------------------------------------------------------- orders */

    public function test_order_status_can_be_updated(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-1',
            'customer_name' => 'Customer',
            'phone' => '01700000000',
            'address' => 'Addr',
            'total' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'delivered'])
            ->assertRedirect();

        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function test_invalid_order_status_is_rejected(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-2',
            'customer_name' => 'Customer',
            'phone' => '01700000000',
            'address' => 'Addr',
            'total' => 100,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->patch('/admin/orders/'.$order->id.'/status', ['status' => 'hacked'])
            ->assertSessionHasErrors('status');

        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_orders_can_be_filtered_by_status_and_search(): void
    {
        $this->actingAs($this->admin());

        Order::create([
            'order_number' => 'ORD-AAA', 'customer_name' => 'Alpha', 'phone' => '01700000001',
            'address' => 'A', 'total' => 10, 'status' => 'pending',
        ]);
        Order::create([
            'order_number' => 'ORD-BBB', 'customer_name' => 'Beta', 'phone' => '01700000002',
            'address' => 'B', 'total' => 20, 'status' => 'delivered',
        ]);

        $this->get('/admin/orders?status=delivered')->assertOk()->assertSee('ORD-BBB')->assertDontSee('ORD-AAA');
        $this->get('/admin/orders?q=Alpha')->assertOk()->assertSee('ORD-AAA')->assertDontSee('ORD-BBB');
    }

    public function test_order_can_be_deleted(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-DEL', 'customer_name' => 'X', 'phone' => '1',
            'address' => 'A', 'total' => 1, 'status' => 'pending',
        ]);

        $this->actingAs($this->admin())
            ->delete('/admin/orders/'.$order->id)
            ->assertRedirect(route('admin.orders.index'));

        $this->assertSame(0, Order::count());
    }

    public function test_order_show_page_displays_items(): void
    {
        $this->actingAs($this->admin());

        $order = Order::create([
            'order_number' => 'ORD-SHOW', 'customer_name' => 'Show Customer', 'phone' => '01700000009',
            'address' => 'Display address', 'total' => 500, 'status' => 'pending',
        ]);
        $order->items()->create([
            'product_name' => 'Widget Pro', 'price' => 500, 'quantity' => 1, 'subtotal' => 500,
        ]);

        $this->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee('ORD-SHOW')
            ->assertSee('Show Customer')
            ->assertSee('Widget Pro')
            ->assertSee('Display address');
    }

    /* --------------------------------------------------------- delivery zones */

    public function test_delivery_zone_crud(): void
    {
        Storage::fake('public');
        $admin = $this->actingAs($this->admin());

        $admin->post('/admin/delivery-zones', [
            'name' => 'Suburb', 'charge' => '90', 'estimated_days' => '2', 'is_active' => '1',
        ])->assertRedirect(route('admin.delivery-zones.index'));

        $zone = DeliveryZone::first();
        $this->assertEquals('Suburb', $zone->name);
        $this->assertEquals(90.00, (float) $zone->charge);
        $this->assertEquals(2, $zone->estimated_days);

        $admin->put('/admin/delivery-zones/'.$zone->id, [
            'name' => 'Suburb Updated', 'charge' => '95', 'is_active' => '1',
        ])->assertRedirect(route('admin.delivery-zones.index'));

        $zone->refresh();
        $this->assertEquals('Suburb Updated', $zone->name);
        $this->assertEquals(95.00, (float) $zone->charge);
        $this->assertNull($zone->estimated_days);

        $admin->delete('/admin/delivery-zones/'.$zone->id);
        $this->assertSame(0, DeliveryZone::count());
    }

    public function test_delivery_zone_requires_name_and_charge(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/delivery-zones', [])
            ->assertSessionHasErrors(['name', 'charge']);
    }

    public function test_delivery_zones_can_be_reordered(): void
    {
        $this->actingAs($this->admin());

        $a = DeliveryZone::create(['name' => 'A', 'charge' => 10, 'is_active' => true, 'sort_order' => 1]);
        $b = DeliveryZone::create(['name' => 'B', 'charge' => 20, 'is_active' => true, 'sort_order' => 2]);
        $c = DeliveryZone::create(['name' => 'C', 'charge' => 30, 'is_active' => true, 'sort_order' => 3]);

        $this->post('/admin/delivery-zones/reorder', ['order' => [$c->id, $a->id, $b->id]]);

        $this->assertEquals(1, $c->fresh()->sort_order);
        $this->assertEquals(2, $a->fresh()->sort_order);
        $this->assertEquals(3, $b->fresh()->sort_order);

        $this->assertEquals(['C', 'A', 'B'], DeliveryZone::ordered()->pluck('name')->all());
    }

    /* ---------------------------------------------------------- testimonials */

    public function test_testimonial_image_can_be_uploaded(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/testimonials', [
                'is_active' => '1',
                'image' => UploadedFile::fake()->image('review.png'),
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::first();

        $this->assertNotNull($testimonial);
        $this->assertNotNull($testimonial->image);
        Storage::disk('public')->assertExists($testimonial->image);
    }

    public function test_testimonial_requires_an_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/testimonials', ['is_active' => '1'])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Testimonial::count());
    }

    public function test_testimonial_image_can_be_replaced_and_deleted(): void
    {
        Storage::fake('public');
        $admin = $this->actingAs($this->admin());

        $testimonial = Testimonial::create([
            'image' => UploadedFile::fake()->image('old.png')->store('testimonials', 'public'),
            'is_active' => true,
        ]);
        $oldPath = $testimonial->image;

        $admin->put('/admin/testimonials/'.$testimonial->id, [
            'is_active' => '1',
            'image' => UploadedFile::fake()->image('new.png'),
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial->refresh();

        $this->assertNotEquals($oldPath, $testimonial->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($testimonial->image);

        $admin->delete('/admin/testimonials/'.$testimonial->id);

        $this->assertSame(0, Testimonial::count());
        Storage::disk('public')->assertMissing($testimonial->image);
    }

    public function test_testimonials_can_be_reordered(): void
    {
        $this->actingAs($this->admin());

        $a = Testimonial::create(['image' => 'testimonials/a.png', 'is_active' => true, 'sort_order' => 1]);
        $b = Testimonial::create(['image' => 'testimonials/b.png', 'is_active' => true, 'sort_order' => 2]);

        $this->post('/admin/testimonials/reorder', ['order' => [$b->id, $a->id]]);

        $this->assertEquals(['testimonials/b.png', 'testimonials/a.png'], Testimonial::ordered()->pluck('image')->all());
    }

    /* -------------------------------------------------------------- settings */

    public function test_settings_can_be_saved_and_are_used_on_the_home_page(): void
    {
        $this->actingAs($this->admin())->post('/admin/settings', [
            'site_name' => 'Curled Store',
            'site_tagline' => 'Fresh deals every day',
            'site_email' => 'hi@store.test',
            'site_phone' => '0123456789',
            'site_address' => 'Barishal, Bangladesh',
            'currency_symbol' => '$',
            'footer_note' => 'Made with care',
        ])->assertRedirect();

        $this->assertEquals('Curled Store', Setting::value('site_name'));
        $this->assertEquals('$', Setting::value('currency_symbol'));

        $this->get('/')
            ->assertOk()
            ->assertSee('Curled Store')
            ->assertSee('Fresh deals every day')
            ->assertSee('Made with care');
    }

    public function test_pixel_settings_can_be_saved(): void
    {
        $this->actingAs($this->admin())->post('/admin/settings', [
            'site_name' => 'Store',
            'currency_symbol' => '৳',
            'meta_pixel_id' => '1234567890123456',
            'meta_capi_token' => 'EAABsecret',
            'meta_test_code' => 'TEST1234',
            'tiktok_pixel_id' => 'C0abcdefghijk',
            'tiktok_api_token' => 'ttk-token',
            'tiktok_test_code' => 'TT123',
            'pixel_site_url' => 'https://shop.test',
        ])->assertRedirect();

        $this->assertEquals('1234567890123456', Setting::value('meta_pixel_id'));
        $this->assertEquals('EAABsecret', Setting::value('meta_capi_token'));
        $this->assertEquals('TEST1234', Setting::value('meta_test_code'));
        $this->assertEquals('C0abcdefghijk', Setting::value('tiktok_pixel_id'));
        $this->assertEquals('ttk-token', Setting::value('tiktok_api_token'));
        $this->assertEquals('TT123', Setting::value('tiktok_test_code'));
        $this->assertEquals('https://shop.test', Setting::value('pixel_site_url'));

        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('1234567890123456', false)
            ->assertSee('C0abcdefghijk', false);
    }

    public function test_pixel_settings_are_off_by_default(): void
    {
        foreach ([
            'meta_pixel_id', 'meta_capi_token', 'meta_test_code',
            'tiktok_pixel_id', 'tiktok_api_token', 'tiktok_test_code', 'pixel_site_url',
        ] as $key) {
            $this->assertSame('', Setting::value($key), "{$key} should default to off");
        }
    }

    public function test_invalid_pixel_settings_are_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/admin/settings', [
                'site_name' => 'Store',
                'currency_symbol' => '৳',
                'meta_pixel_id' => 'not-numeric',
            ])
            ->assertSessionHasErrors('meta_pixel_id');

        $this->actingAs($admin)
            ->post('/admin/settings', [
                'site_name' => 'Store',
                'currency_symbol' => '৳',
                'pixel_site_url' => 'not a url',
            ])
            ->assertSessionHasErrors('pixel_site_url');
    }

    public function test_settings_require_a_site_name(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/settings', ['currency_symbol' => '$'])
            ->assertSessionHasErrors('site_name');
    }

    public function test_notification_email_can_be_saved(): void
    {
        $this->actingAs($this->admin())->post('/admin/settings', [
            'site_name' => 'Store',
            'currency_symbol' => '৳',
            'notify_email' => 'orders@store.test',
        ])->assertRedirect();

        $this->assertEquals('orders@store.test', Setting::value('notify_email'));

        $this->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('orders@store.test', false);
    }

    public function test_notification_email_must_be_valid(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/settings', [
                'site_name' => 'Store',
                'currency_symbol' => '৳',
                'notify_email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('notify_email');
    }

    public function test_notification_email_is_off_when_blank(): void
    {
        $this->assertSame('', Setting::value('notify_email'));

        $this->actingAs($this->admin())->post('/admin/settings', [
            'site_name' => 'Store',
            'currency_symbol' => '৳',
            'notify_email' => '',
        ])->assertRedirect();

        $this->assertSame('', Setting::value('notify_email'));
    }

    public function test_announcement_bar_is_gone(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('class="announce"', $html);
        $this->assertStringNotContainsString('announcement', $html);
    }

    public function test_settings_fall_back_to_defaults_when_nothing_saved(): void
    {
        $this->assertEquals('Landing Page', Setting::value('site_name'));
        $this->assertEquals('৳', Setting::value('currency_symbol'));

        $this->get('/')->assertOk()->assertSee('Landing Page');
    }

    public function test_landing_page_has_no_navbar_or_hero(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('class="site-header"', $html);
        $this->assertStringNotContainsString('class="nav"', $html);
        $this->assertStringNotContainsString('data-nav-toggle', $html);
        $this->assertStringNotContainsString('class="hero"', $html);
        $this->assertStringNotContainsString('hero__', $html);
    }

    public function test_landing_page_still_shows_products_and_checkout(): void
    {
        Product::create(['name' => 'Kept Product', 'regular_price' => 100, 'sale_price' => null, 'is_active' => true]);
        DeliveryZone::create(['name' => 'Kept Zone', 'charge' => 50, 'is_active' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Kept Product')
            ->assertSee('Kept Zone')
            ->assertSee('id="checkout"', false)
            ->assertSee('Place Order', false);
    }

    public function test_feature_strip_is_gone(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringNotContainsString('class="features"', $html);
        $this->assertStringNotContainsString('class="feature__icon"', $html);
        $this->assertStringNotContainsString('Cash on Delivery</h3>', $html);
        $this->assertStringNotContainsString('Best Quality</h3>', $html);
    }

    public function test_quality_section_renders_below_the_product_grid(): void
    {
        $html = $this->get('/')->getContent();

        $this->assertStringContainsString('class="section quality"', $html);
        $this->assertStringContainsString('No compromise on quality!', $html);
        $this->assertStringContainsString('best quality sari', $html);
        $this->assertStringContainsString('quality is our responsibility', $html);

        $productsEnd = strpos($html, '</section>', strpos($html, 'id="products"'));
        $qualityStart = strpos($html, 'class="section quality"');
        $this->assertNotFalse($productsEnd);
        $this->assertNotFalse($qualityStart);
        $this->assertGreaterThan($productsEnd, $qualityStart);
    }

    /* -------------------------------------------------------------- dashboard */

    public function test_dashboard_shows_stats(): void
    {
        $this->actingAs($this->admin());

        Product::create(['name' => 'P1', 'regular_price' => 100, 'sale_price' => null, 'is_active' => true]);
        Order::create([
            'order_number' => 'ORD-DASH', 'customer_name' => 'Dash', 'phone' => '1',
            'address' => 'A', 'total' => 250, 'status' => 'pending',
        ]);

        $this->get('/admin')
            ->assertOk()
            ->assertSee('ORD-DASH')
            ->assertSee('Welcome back');
    }
}

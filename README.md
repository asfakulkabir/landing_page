# Landing Page + Admin Dashboard (Laravel 11)

A single-page product landing page with a full admin dashboard.
**Raw HTML, raw CSS, raw JS** — no Tailwind, no Bootstrap, no jQuery, no npm, no build step.

Database: MySQL `landing_page` on `localhost`.

---

## Quick start

```bash
# 1. make sure MySQL is running (XAMPP control panel)

# 2. install
composer install

# 3. environment
cp .env.example .env
php artisan key:generate

# 4. create the database (or do it in phpMyAdmin)
php -r 'new PDO("mysql:host=127.0.0.1","root","")->exec("CREATE DATABASE IF NOT EXISTS landing_page CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");'

# 5. tables + demo data
php artisan migrate --seed

# 6. run
php artisan serve
```

There is **no `public/` folder and no `storage:link` step** — see
[Folder layout](#folder-layout).

| Page | URL |
| --- | --- |
| Landing page | <http://127.0.0.1:8000/> |
| Admin panel | <http://127.0.0.1:8000/admin> |
| Thank you page | `/thank-you/ORD-XXXX` (reached after ordering) |

XAMPP's Apache works too, at <http://localhost/landing_page/>.

**Demo admin login**

```
email:    admin@admin.com
password: admin123
```

Change this in `.env` free time, or in `database/seeders/DatabaseSeeder.php`.

---

## `.env` (already set for you)

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landing_page
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

---

## Folder layout

This project is built for **shared hosting**, where you cannot change the web
server's document root. Instead of Laravel's usual `public/` folder, **the project
root is the document root**: `index.php` and `.htaccess` sit next to `app/`,
`vendor/` and `.env`. Upload the whole folder into `public_html` and it works.

```
index.php        front controller  (root, NOT in public/)
.htaccess        pretty URLs + blocks every non-web folder
css/  js/  img/  favicon.ico  robots.txt
uploads/         user-uploaded product & testimonial images (web-accessible)
app/ bootstrap/ config/ database/ lang/ resources/ routes/ storage/ tests/ vendor/
artisan  composer.json  .env
```

**Consequences of this layout**

* `php artisan storage:link` is **not needed and must not be run.** The `public`
  filesystem disk is rooted at `uploads/` (`config/filesystems.php`), so
  `Storage::disk('public')` writes straight into a folder the web server can
  reach. Image URLs are `/uploads/<file>`.
* Views use `base_path('css/…')` instead of `public_path('css/…')` for cache
  busting, because the assets now live at the project root.
* `public_path()` returns the project root: `AppServiceProvider::register()` calls `$this->app->usePublicPath($this->app->basePath())`. That is what keeps `php artisan serve` working, and it is why the Blade templates use `base_path('css/…')` rather than `public_path('css/…')`.
* **Security:** with the root as document root, `.env`, `storage/logs` and
  `vendor/` are nominally inside the web root. The root `.htaccess` returns 404
  for `app/ bootstrap/ config/ database/ lang/ resources/ routes/ storage/ tests/
  vendor/`, for dotfiles such as `.env` and `.git`, and for `artisan`,
  `composer.json`, `phpunit.xml` and `README.md`. If you ever move this app to a
  host where you *can* set the document root, pointing it at `public/` — by
  moving `index.php`, `.htaccess`, `css/`, `js/`, `img/`, `favicon.ico` and
  `robots.txt` back into a `public/` folder — is the stronger option.

---

## Deploying to cPanel

1. Upload the project folder into `public_html` so that `public_html/index.php`
   and `public_html/.htaccess` exist. Keep the folder structure intact.
2. cPanel → **MultiPHP Manager** / **Select PHP Version**: use **PHP 8.2+**.
3. cPanel → **MySQL Databases**: create a database and a user, then add the user
   to the database with **ALL PRIVILEGES**.
4. SSH into the account and run:

   ```bash
   cd ~/public_html
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force --seed
   php artisan optimize:clear
   chmod -R 775 storage bootstrap/cache
   ```

5. Copy `.env.example` to `.env` and set at least:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   DB_DATABASE=cpanel_db_name
   DB_USERNAME=cpanel_db_user
   DB_PASSWORD=the-password
   ```

6. Delete `vendor/` and `.env` from the copy you upload if you prefer to run
   `composer install` on the server instead — `vendor/` built on macOS will not
   work on the host.

---

## What the customer sees (2 pages total)

### Page 1 — Home (`/`)

1. **Product grid** — one card per product showing
   * product image
   * product name
   * price — strikethrough regular price + bold sale price + `-X%` flag when on sale
   * **Order Now** button
2. **Quality promise** — a short statement (title, body, closing note) in a
   gradient band, directly below the product grid
3. **Testimonial slider** — image only, placed *directly above* the checkout form.
   Arrows + dots, mouse drag, touch swipe, auto 3/2/1 columns responsive.
4. **Checkout form** (bottom of the page) in three steps:
   1. Your Details — name, phone, email (optional), address
   2. Select Products — one row per product with a tick box and quantity input
   3. Delivery Zone — radio cards showing each zone's charge and estimated days
   Plus a live **Order Summary** (sticky on desktop) and a **Place Order** button.
5. **FAQ**, then the footer.

**The "Order Now" flow works three ways:**

* Clicking **Order Now** on a card scrolls down to the checkout form, ticks that product, sets quantity to 1, refreshes the live total, and flashes the card.
* Ticking products by hand in step 2 also works — you can order several products at once.
* If you arrive with nothing ticked, the **first product is selected automatically** (server-side fallback), so the form is never empty.
* Deep link `/?product=5` pre-selects product 5 server-side (useful from admin "Preview on site").

### Page 2 — Thank you (`/thank-you/{order_number}`)

Order number, name, phone, delivery zone, status, full address, note, itemised
summary (image + name + qty + line total), subtotal / delivery / **total payable**,
and a "Cash on delivery" reminder.

---

## Admin dashboard (`/admin`)

| Section | What it does |
| --- | --- |
| **Dashboard** | Total orders, revenue, product count, pending count, last 8 orders |
| **Products** | Create / edit / delete. Fields: **image upload, product name, regular price, sale price** (+ visibility toggle). Search by name, filter active/hidden. Sale price is auto-cleared if it is not lower than the regular price. Images are validated (jpg/jpeg/png/webp/gif, max 4 MB) and deleted off disk when the product is deleted. |
| **Orders** | List with search (order no / name / phone) and status filter. Detail page with items and customer info. Change status: `pending → confirmed → shipped → delivered / cancelled`. Delete orders. |
| **Delivery Zones** | Create / edit / delete zones with **name, charge, estimated days**, active toggle. Drag rows to reorder. Charge `0` = free delivery. |
| **Testimonials** | **Image only** — upload, replace, remove, delete, show/hide, drag to reorder. |
| **Site Settings** | Site name, tagline, currency symbol, contact email / phone / address, and footer note. |

---

## Database tables

| Table | Columns |
| --- | --- |
| `products` | `name`, `image`, `regular_price`, `sale_price`, `is_active`, `sort_order` |
| `delivery_zones` | `name`, `charge`, `estimated_days`, `is_active`, `sort_order` |
| `testimonials` | `image`, `is_active`, `sort_order` |
| `orders` | `order_number`, `customer_name`, `phone`, `email`, `address`, `delivery_zone_id`, `delivery_zone`, `delivery_charge`, `total`, `payment_method`, `note`, `status` |
| `order_items` | `order_id`, `product_id`, `product_name`, `product_image`, `price`, `quantity`, `subtotal` |
| `settings` | `key`, `value` |
| `users` | admin login |

Order items **snapshot** the name, image and price at purchase time, so later edits
or deletions in the admin never rewrite historical orders.

---

## Files

```
app/
  Models/            Product, Order, OrderItem, DeliveryZone, Testimonial, Setting
  Http/Controllers/
    HomeController           landing page
    CheckoutController       order create + thank you page
    Admin/                  Auth, Dashboard, Product, Order, DeliveryZone,
                            Testimonial, Setting
resources/views/
  layouts/site.blade.php     public shell
  layouts/admin.blade.php    admin shell with sidebar
  home.blade.php             landing page (products → slider → checkout)
  thank-you.blade.php
  admin/                     auth/login, dashboard, products, orders,
                            delivery-zones, testimonials, settings
index.php        front controller (project root, see Folder layout)
.htaccess        pretty URLs + security rules
css/site.css     all landing page CSS  (hand written)
css/admin.css    all admin CSS         (hand written)
js/site.js       slider + order form  (hand written, no libraries)
js/admin.js      sidebar, confirms, drag-to-reorder
js/pixel.js      Meta / TikTok pixel loader
img/             logo + placeholder SVG (product / testimonial fallback)
uploads/         uploaded images (served directly at /uploads/…)
database/seeders/
  DatabaseSeeder       admin user, 3 sample zones, default settings
  DemoContentSeeder    12 sample products + 6 testimonial images (SVG)
```

### How the checkout form posts

Each product is a real HTML input, so the form works with or without JavaScript:

```html
<input type="checkbox" name="items[0][selected]" value="1">   <!-- ticked? -->
<input type="hidden"   name="items[0][product_id]" value="1"> <!-- which product -->
<input type="number"   name="items[0][quantity]" value="1">   <!-- how many -->
```

The controller keeps only ticked rows, and if none are ticked it falls back to the
first row. Prices are always recalculated **server-side from the database**, never
trusted from the browser. Inactive products and inactive delivery zones are rejected.

---

## Tests

```bash
php artisan test
```

```
Tests:  87 passed, 6 failed (344 assertions)
```

The 6 failures are pre-existing and unrelated to the folder layout: they assert
English UI copy (`Place Order`, `Admin Login`, `Show Customer`, …) while the
Blade views have since been translated to Bangla. Fix the assertions or the
copy — not the layout.

Covering: the landing page, sale-price display, `?product=` preselection, the
testimonial slider data, checkout success, multi-product orders, the
nothing-selected fallback, inactive product/zone rejection, validation, quantity
limits, free delivery, unique order numbers, admin auth, product CRUD + image
upload/replace/delete, sale-price rules, product search & filter, order status
transitions, delivery zone CRUD + reorder, testimonial upload/replace/reorder,
and every site setting.

Code style is Laravel Pint:

```bash
./vendor/bin/pint          # format
./vendor/bin/pint --test   # check only
```

---

## Notes

* Tests use in-memory SQLite (`phpunit.xml`), so they never touch your `landing_page` database.
* Uploaded images live in `uploads/{products,testimonials,settings}` and are served directly at `/uploads/…`. Do **not** run `php artisan storage:link` — the project has no `public/` folder, so the command has nothing useful to link.
* Deleting a product or testimonial also deletes its file from disk.
* `composer install` has `audit.block-insecure` disabled in `composer.json` because Laravel 11.56 is the newest 11.x release and Composer flags framework-wide advisories for it. If you are deploying this publicly, upgrade to Laravel 12 and re-enable the audit — the application code needs no changes.

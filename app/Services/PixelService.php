<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Meta Pixel + TikTok Pixel tracking.
 *
 * The browser pixel is loaded by resources/views/partials/pixels.blade.php and
 * fires ViewContent / AddToCart / InitiateCheckout from site.js. Only Purchase is
 * mirrored server-side (see sendPurchase), so each purchase is reported once per
 * destination and the two halves deduplicate on a shared event_id.
 */
class PixelService
{
    private const META_GRAPH_VERSION = 'v21.0';

    private const TIKTOK_API_URL = 'https://business-api.tiktokapis.com/v1.3/event/track/';

    /** ISO-4217 code TikTok requires on every event. */
    private const CURRENCY = 'BDT';

    private const COUNTRY_CODE = '880';

    /**
     * Snapshot of the tracking config, read once per request. Setting::value()
     * hits the database per key, so avoid calling it in a loop.
     */
    public static function config(): array
    {
        $s = Setting::all_settings();

        return [
            'meta' => [
                'pixelId' => trim((string) ($s['meta_pixel_id'] ?? '')),
                'capi' => trim((string) ($s['meta_capi_token'] ?? '')) !== '',
                'testCode' => trim((string) ($s['meta_test_code'] ?? '')),
            ],
            'tiktok' => [
                'pixelId' => trim((string) ($s['tiktok_pixel_id'] ?? '')),
                'capi' => trim((string) ($s['tiktok_api_token'] ?? '')) !== '',
                'testCode' => trim((string) ($s['tiktok_test_code'] ?? '')),
            ],
        ];
    }

    public static function metaEnabled(): bool
    {
        return self::config()['meta']['pixelId'] !== '';
    }

    public static function tiktokEnabled(): bool
    {
        return self::config()['tiktok']['pixelId'] !== '';
    }

    public static function anyEnabled(): bool
    {
        return self::metaEnabled() || self::tiktokEnabled();
    }

    /** The public origin the Conversion API reports as event_source_url. */
    public static function siteUrl(?string $path = null): string
    {
        $base = rtrim(trim((string) Setting::value('pixel_site_url', '')), '/');

        if ($base === '') {
            $base = rtrim((string) config('app.url'), '/');
        }

        if ($path === null || $path === '') {
            return $base;
        }

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * Stable id shared by the browser and server halves of Purchase, so Meta and
     * TikTok collapse the pair into one conversion. Derived from the order number
     * rather than randomised, so reloading the thank-you page cannot re-report it.
     */
    public static function purchaseEventId(Order $order): string
    {
        return 'ord_'.$order->order_number;
    }

    /**
     * E-commerce payload shared by the browser pixel and both server APIs.
     */
    public static function purchasePayload(Order $order): array
    {
        $order->loadMissing('items');

        $contents = [];
        $numItems = 0;

        foreach ($order->items as $item) {
            $quantity = (int) $item->quantity;
            $numItems += $quantity;

            $contents[] = array_filter([
                'id' => self::contentId($item->product_id),
                'content_name' => $item->product_name,
                'quantity' => $quantity,
                'item_price' => (float) $item->price,
            ], fn ($v) => $v !== null && $v !== '');
        }

        return [
            'value' => round((float) $order->total, 2),
            'currency' => self::CURRENCY,
            'content_type' => 'product',
            'content_ids' => array_values(array_filter(array_column($contents, 'id'))),
            'contents' => $contents,
            'num_items' => $numItems,
            'order_id' => $order->order_number,
        ];
    }

    /**
     * Send Purchase to Meta's Conversions API and TikTok's Events API.
     *
     * Never throws: a tracking outage must not break the thank-you page. Failures
     * are reported to the log so they are visible but harmless.
     */
    public static function sendPurchase(Order $order): void
    {
        $config = self::config();

        if ($config['meta']['pixelId'] !== '' && $config['meta']['capi']) {
            self::post(self::metaUrl($config['meta']), self::metaPurchaseBody($order, $config['meta']));
        }

        if ($config['tiktok']['pixelId'] !== '' && $config['tiktok']['capi']) {
            self::post(
                self::TIKTOK_API_URL,
                self::tiktokPurchaseBody($order, $config['tiktok']),
                ['Access-Token' => trim((string) Setting::value('tiktok_api_token', ''))]
            );
        }
    }

    private static function metaUrl(array $meta): string
    {
        return sprintf(
            'https://graph.facebook.com/%s/%s/events?access_token=%s',
            self::META_GRAPH_VERSION,
            $meta['pixelId'],
            rawurlencode((string) Setting::value('meta_capi_token', ''))
        );
    }

    private static function metaPurchaseBody(Order $order, array $meta): array
    {
        $payload = self::purchasePayload($order);

        $userData = array_filter([
            'ph' => self::hashPhone($order->phone),
            'em' => $order->email ? self::hashEmail($order->email) : null,
            'fn' => self::hashName($order->customer_name),
            'client_ip_address' => request()->ip(),
            'client_user_agent' => request()->userAgent(),
        ], fn ($v) => $v !== null && $v !== '');

        $body = [
            'data' => [[
                'event_name' => 'Purchase',
                'event_time' => now()->timestamp,
                'event_id' => self::purchaseEventId($order),
                'event_source_url' => self::siteUrl('thank-you/'.$order->order_number),
                'action_source' => 'website',
                'user_data' => $userData,
                'custom_data' => [
                    'currency' => $payload['currency'],
                    'value' => $payload['value'],
                    'content_type' => $payload['content_type'],
                    'content_ids' => $payload['content_ids'],
                    'contents' => array_map(fn ($c) => [
                        'id' => $c['id'],
                        'quantity' => $c['quantity'],
                        'item_price' => $c['item_price'],
                    ], $payload['contents']),
                    'num_items' => $payload['num_items'],
                    'order_id' => $payload['order_id'],
                ],
            ]],
        ];

        if ($meta['testCode'] !== '') {
            $body['test_event_code'] = $meta['testCode'];
        }

        return $body;
    }

    private static function tiktokPurchaseBody(Order $order, array $tiktok): array
    {
        $payload = self::purchasePayload($order);
        $url = self::siteUrl('thank-you/'.$order->order_number);

        $user = array_filter([
            // TikTok requires SHA-256 of the lowercased address
            'email' => $order->email ? self::hashEmail($order->email) : null,
            'phone' => self::hashPhone($order->phone),
        ], fn ($v) => $v !== null && $v !== '');

        $event = [
            'event' => 'CompletePayment',
            'event_time' => now()->timestamp,
            'event_id' => self::purchaseEventId($order),
            // TikTok rejects standard events that arrive without these.
            'value' => $payload['value'],
            'currency' => $payload['currency'],
            'num_items' => $payload['num_items'],
        ];

        // Omit empty blocks rather than sending nulls, which the API rejects.
        if ($user !== []) {
            $event['user'] = $user;
        }

        $tiktokContents = array_map(fn ($c) => array_filter([
            'content_id' => $c['id'],
            'content_type' => 'product',
            'content_name' => $c['content_name'] ?? null,
            'price' => $c['item_price'],
            'quantity' => $c['quantity'],
            'currency' => self::CURRENCY,
        ], fn ($v) => $v !== null && $v !== ''), $payload['contents']);

        $body = [
            'event_source' => 'web',
            'event_source_id' => $tiktok['pixelId'],
            'event_source_url' => $url,
            'data' => [array_merge($event, [
                'page' => ['url' => $url],
                'contents' => $tiktokContents,
            ])],
        ];

        if ($tiktok['testCode'] !== '') {
            $body['test_event_code'] = $tiktok['testCode'];
        }

        return $body;
    }

    private static function post(string $url, array $body, array $headers = []): void
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders($headers + ['Content-Type' => 'application/json'])
                ->post($url, $body);

            if (! $response->successful()) {
                report(new \RuntimeException(sprintf(
                    'Pixel API rejected the event (HTTP %d): %s',
                    $response->status(),
                    mb_substr($response->body(), 0, 500)
                )));
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Meta/TikTok want content ids namespaced so they cannot collide. */
    private static function contentId(?int $productId): ?string
    {
        return $productId ? 'product_'.$productId : null;
    }

    private static function hashEmail(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }

    private static function hashName(string $name): string
    {
        return hash('sha256', mb_strtolower(trim($name)));
    }

    /**
     * Hash a phone number in E.164 form without the plus. Local BD numbers are
     * stored as 01XXXXXXXXX, so the country code has to be restored first.
     */
    private static function hashPhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, self::COUNTRY_CODE)) {
            $e164 = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $e164 = self::COUNTRY_CODE.substr($digits, 1);
        } else {
            $e164 = self::COUNTRY_CODE.$digits;
        }

        return hash('sha256', $e164);
    }
}

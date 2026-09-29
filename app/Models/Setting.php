<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'site_name' => 'Landing Page',
        'site_tagline' => 'Best quality products, delivered to your door.',
        'site_email' => 'hello@example.com',
        'site_phone' => '+880 1700 000000',
        'site_whatsapp' => '8801700000000',
        'site_address' => 'Dhaka, Bangladesh',
        'currency_symbol' => '৳',
        'footer_note' => 'All rights reserved.',
        'notify_email' => '',

        /* Meta Pixel + Conversions API */
        'meta_pixel_id' => '',
        'meta_capi_token' => '',
        'meta_test_code' => '',

        /* TikTok Pixel + Events API */
        'tiktok_pixel_id' => '',
        'tiktok_api_token' => '',
        'tiktok_test_code' => '',

        /* Public origin used as event_source_url by the server-side API */
        'pixel_site_url' => '',
    ];

    public static function value(string $key, $default = null)
    {
        $row = static::where('key', $key)->first();

        if ($row === null || $row->value === null || $row->value === '') {
            return $default ?? (self::DEFAULTS[$key] ?? null);
        }

        return $row->value;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function all_settings(): array
    {
        $stored = static::pluck('value', 'key')->all();

        return array_merge(self::DEFAULTS, array_filter($stored, fn ($v) => $v !== null));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private const KEYS = [
        'site_name',
        'site_tagline',
        'site_email',
        'site_phone',
        'site_whatsapp',
        'site_address',
        'currency_symbol',
        'footer_note',
        'notify_email',

        'meta_pixel_id',
        'meta_capi_token',
        'meta_test_code',
        'tiktok_pixel_id',
        'tiktok_api_token',
        'tiktok_test_code',
        'pixel_site_url',
    ];

    public function index()
    {
        return view('admin.settings.index', [
            'settings' => Setting::all_settings(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'site_email' => ['nullable', 'email', 'max:255'],
            'site_phone' => ['nullable', 'string', 'max:60'],
            'site_whatsapp' => ['nullable', 'string', 'max:60', 'regex:/^[0-9+\s-]+$/'],
            'site_address' => ['nullable', 'string', 'max:255'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'footer_note' => ['nullable', 'string', 'max:255'],
            'notify_email' => ['nullable', 'email', 'max:255'],
            'meta_pixel_id' => ['nullable', 'string', 'max:60', 'regex:/^\d{5,25}$/'],
            'meta_capi_token' => ['nullable', 'string', 'max:512'],
            'meta_test_code' => ['nullable', 'string', 'max:60'],
            'tiktok_pixel_id' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9]{5,40}$/'],
            'tiktok_api_token' => ['nullable', 'string', 'max:512'],
            'tiktok_test_code' => ['nullable', 'string', 'max:60'],
            'pixel_site_url' => ['nullable', 'url', 'max:255'],
        ], [], ['site_name' => 'site name']);

        foreach ($data as $key => $value) {
            if (in_array($key, self::KEYS, true)) {
                Setting::put($key, (string) $value);
            }
        }

        return back()->with('success', 'Settings saved.');
    }
}

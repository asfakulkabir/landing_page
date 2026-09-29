@extends('layouts.admin')

@section('title', 'সাইট সেটিংস')

@section('content')

<div class="content__head">
    <div>
        <h1>সাইট সেটিংস</h1>
        <p class="content__sub">ল্যান্ডিং পেজে দেখানো বিষয়বস্তু নিয়ন্ত্রণ করুন।</p>
    </div>
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn--ghost">সাইট দেখুন</a>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    <div class="panel">
        <div class="panel__head"><h2>সাধারণ</h2></div>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="site_name">সাইটের নাম <span class="req">*</span></label>
                    <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name']) }}" required>
                    @error('site_name') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="currency_symbol">মুদ্রার প্রতীক <span class="req">*</span></label>
                    <input type="text" id="currency_symbol" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required maxlength="10">
                    @error('currency_symbol') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="field">
                <label for="site_tagline">ট্যাগলাইন</label>
                <input type="text" id="site_tagline" name="site_tagline" value="{{ old('site_tagline', $settings['site_tagline']) }}">
                <div class="hint">ফুটারে দেখানো হয়।</div>
            </div>

            <div class="grid-2">
                <div class="field">
                    <label for="site_email">যোগাযোগের ইমেইল</label>
                    <input type="email" id="site_email" name="site_email" value="{{ old('site_email', $settings['site_email']) }}">
                    @error('site_email') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="site_phone">যোগাযোগের ফোন</label>
                    <input type="text" id="site_phone" name="site_phone" value="{{ old('site_phone', $settings['site_phone']) }}">
                </div>
            </div>

            <div class="field">
                <label for="site_whatsapp">হোয়াটসঅ্যাপ নম্বর</label>
                <input type="text" id="site_whatsapp" name="site_whatsapp" value="{{ old('site_whatsapp', $settings['site_whatsapp']) }}" placeholder="8801765442672">
                <div class="hint">দেশের কোডসহ শুধু অঙ্ক লিখুন (যেমন 8801765442672)। ল্যান্ডিং পেজের "কল করুন" সেকশনে ব্যবহৃত হয়।</div>
                @error('site_whatsapp') <div class="error">{{ $message }}</div> @enderror
            </div>

            <div class="field">
                <label for="site_address">ঠিকানা</label>
                <input type="text" id="site_address" name="site_address" value="{{ old('site_address', $settings['site_address']) }}">
            </div>

            <div class="field">
                <label for="footer_note">ফুটারের নোট</label>
                <input type="text" id="footer_note" name="footer_note" value="{{ old('footer_note', $settings['footer_note']) }}">
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>ইমেইল নোটিফিকেশন</h2></div>
        <div class="panel__body">
            <div class="field">
                <label for="notify_email">নতুন অর্ডারের নোটিফিকেশন ইমেইল</label>
                <input type="email" id="notify_email" name="notify_email" value="{{ old('notify_email', $settings['notify_email']) }}">
                <div class="hint">গ্রাহক অর্ডার সম্পন্ন করলে এই ঠিকানায় ইমেইল যাবে। খালি রাখলে কোনো ইমেইল পাঠানো হবে না।</div>
                @error('notify_email') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>মেটা পিক্সেল (Facebook)</h2></div>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="meta_pixel_id">Pixel ID</label>
                    <input type="text" id="meta_pixel_id" name="meta_pixel_id" value="{{ old('meta_pixel_id', $settings['meta_pixel_id']) }}" placeholder="1234567890123456">
                    <div class="hint">মেটা বিজন্স ম্যানেজার থেকে পাওয়া Pixel ID। খালি রাখলে মেটা পিক্সেল লোড হবে না।</div>
                    @error('meta_pixel_id') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="meta_capi_token">Conversions API Access Token</label>
                    <input type="text" id="meta_capi_token" name="meta_capi_token" value="{{ old('meta_capi_token', $settings['meta_capi_token']) }}">
                    <div class="hint">Conversion API টোকেন দিলে Purchase ইভেন্ট সার্ভার থেকেও পাঠানো হবে (অ্যাড ব্লকার হলেও গোনা হবে)।</div>
                    @error('meta_capi_token') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="field">
                <label for="meta_test_code">Test Event Code</label>
                <input type="text" id="meta_test_code" name="meta_test_code" value="{{ old('meta_test_code', $settings['meta_test_code']) }}">
                <div class="hint">ইভেন্ট ম্যানেজার থেকে টেস্ট কোড দিলে ইভেন্টগুলো টেস্ট মোডে যাবে। লাইভে যাওয়ার আগে খালি করুন।</div>
                @error('meta_test_code') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>টিকটক পিক্সেল</h2></div>
        <div class="panel__body">
            <div class="grid-2">
                <div class="field">
                    <label for="tiktok_pixel_id">Pixel ID</label>
                    <input type="text" id="tiktok_pixel_id" name="tiktok_pixel_id" value="{{ old('tiktok_pixel_id', $settings['tiktok_pixel_id']) }}" placeholder="C0abcdefghijk">
                    <div class="hint">টিকটক Ads Manager থেকে পাওয়া Pixel ID। খালি রাখলে টিকটক পিক্সেল লোড হবে না।</div>
                    @error('tiktok_pixel_id') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="tiktok_api_token">Events API Access Token</label>
                    <input type="text" id="tiktok_api_token" name="tiktok_api_token" value="{{ old('tiktok_api_token', $settings['tiktok_api_token']) }}">
                    <div class="hint">Events API টোকেন দিলে Purchase ইভেন্ট সার্ভার থেকেও পাঠানো হবে।</div>
                    @error('tiktok_api_token') <div class="error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="field">
                <label for="tiktok_test_code">Test Event Code</label>
                <input type="text" id="tiktok_test_code" name="tiktok_test_code" value="{{ old('tiktok_test_code', $settings['tiktok_test_code']) }}">
                <div class="hint">টেস্ট কোড দিলে ইভেন্টগুলো টেস্ট মোডে যাবে। লাইভে যাওয়ার আগে খালি করুন।</div>
                @error('tiktok_test_code') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__head"><h2>পিক্সেল উন্নত সেটিংস</h2></div>
        <div class="panel__body">
            <div class="field">
                <label for="pixel_site_url">সাইটের পাবলিক ঠিকানা</label>
                <input type="url" id="pixel_site_url" name="pixel_site_url" value="{{ old('pixel_site_url', $settings['pixel_site_url']) }}" placeholder="https://example.com">
                <div class="hint">সার্ভার থেকে ইভেন্ট পাঠানোর সময় এই ঠিকানাই ইভেন্টের উৎস হিসেবে যায়। খালি রাখলে APP_URL ব্যবহার হবে। লোকাল APP_URL (localhost) দিলে মেটা/টিকটক ইভেন্ট গ্রহণ করবে না, তাই লাইভ সাইটে অবশ্যই দিন।</div>
                @error('pixel_site_url') <div class="error">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel__foot" style="display:flex;gap:9px;flex-wrap:wrap">
            <button type="submit" class="btn btn--primary">সেটিংস সংরক্ষণ করুন</button>
            <a href="{{ route('admin.settings.index') }}" class="btn btn--ghost">বাতিল</a>
        </div>
    </div>
</form>

@endsection

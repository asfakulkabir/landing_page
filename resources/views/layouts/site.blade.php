<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings['site_name'] ?? 'ল্যান্ডিং পেজ')</title>
    <meta name="description" content="{{ $settings['site_tagline'] ?? '' }}">
    <link rel="icon" href="{{ asset('img/placeholder.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(base_path('css/site.css')) }}">
    @include('partials.pixels')
</head>
<body>

<main>
    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="footer-brand__name">{{ $settings['site_name'] ?? 'ল্যান্ডিং পেজ' }}</span>
                <p>{{ $settings['site_tagline'] ?? '' }}</p>
            </div>
            <div>
                <h4>দ্রুত লিংক</h4>
                <ul>
                    <li><a href="{{ route('home') }}#products">পণ্যসমূহ</a></li>
                    <li><a href="{{ route('home') }}#testimonials">গ্রাহকের মতামত</a></li>
                    <li><a href="{{ route('home') }}#checkout">অর্ডার করুন</a></li>
                </ul>
            </div>
            <div>
                <h4>সহায়তা</h4>
                <ul>
                    <li><a href="{{ route('home') }}#checkout">ডেলিভারি এলাকা</a></li>
                    <li><a href="{{ route('home') }}#checkout">ক্যাশ অন ডেলিভারি</a></li>
                    <li><a href="{{ route('home') }}#faq">সাধারণ জিজ্ঞাসা</a></li>
                </ul>
            </div>
            <div>
                <h4>যোগাযোগ</h4>
                <ul class="footer-contact">
                    @if(!empty($settings['site_phone']))
                        <li><span>ফোন:</span> <a href="tel:{{ preg_replace('/\s+/', '', $settings['site_phone']) }}">{{ $settings['site_phone'] }}</a></li>
                    @endif
                    @if(!empty($settings['site_email']))
                        <li><span>ইমেইল:</span> <a href="mailto:{{ $settings['site_email'] }}">{{ $settings['site_email'] }}</a></li>
                    @endif
                    @if(!empty($settings['site_address']))
                        <li><span>ঠিকানা:</span> {{ $settings['site_address'] }}</li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} {{ $settings['site_name'] ?? 'ল্যান্ডিং পেজ' }}. {{ $settings['footer_note'] ?? '' }}
        </div>
    </div>
</footer>

<script src="{{ asset('js/site.js') }}?v={{ filemtime(base_path('js/site.js')) }}"></script>
@stack('scripts')
</body>
</html>

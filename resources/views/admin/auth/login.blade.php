<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>অ্যাডমিন লগইন &middot; {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('img/placeholder.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(base_path('css/admin.css')) }}">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <div class="login-card__brand">
            <div class="login-card__logo">A</div>
            <h1>অ্যাডমিন লগইন</h1>
            <p class="login-card__sub">{{ config('app.name') }} কন্ট্রোল প্যানেল</p>
        </div>

        @if($errors->any())
            <div class="alert alert--error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="field">
                <label for="email">ইমেইল ঠিকানা</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>

            <div class="field">
                <label for="password">পাসওয়ার্ড</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>

            <div class="field">
                <label class="check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    আমাকে লগইন রাখুন
                </label>
            </div>

            <button type="submit" class="btn btn--primary btn--block" style="padding:11px">লগইন করুন</button>
        </form>

        <div class="login-hint">
            ডেমো &rarr; <strong>admin@admin.com</strong> / <strong>admin123</strong>
        </div>

        <p class="muted" style="text-align:center;margin:18px 0 0;font-size:13px">
            <a href="{{ route('home') }}">&larr; ওয়েবসাইটে ফিরে যান</a>
        </p>
    </div>
</div>
</body>
</html>

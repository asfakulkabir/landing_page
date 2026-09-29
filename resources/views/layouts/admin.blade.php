<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &middot; Admin</title>
    <link rel="icon" href="{{ asset('img/placeholder.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(base_path('css/admin.css')) }}">
</head>
<body>
<div class="admin">

    <aside class="sidebar" data-sidebar>
        <a class="sidebar__brand" href="{{ route('admin.dashboard') }}">
            <span class="sidebar__mark">A</span>
            <span>
                <strong>Admin Panel</strong>
                <span>{{ config('app.name') }}</span>
            </span>
        </a>

        <nav class="sidebar__nav">
            <span class="sidebar__label">Overview</span>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                Dashboard
            </a>

            <span class="sidebar__label">Catalog</span>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                Products
            </a>

            <span class="sidebar__label">Sales</span>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Orders
                @if($pendingBadge ?? 0)
                    <span class="sidebar__badge">{{ $pendingBadge }}</span>
                @endif
            </a>
            <a href="{{ route('admin.delivery-zones.index') }}" class="{{ request()->routeIs('admin.delivery-zones.*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Delivery Zones
            </a>

            <span class="sidebar__label">Content</span>
            <a href="{{ route('admin.testimonials.index') }}" class="{{ request()->routeIs('admin.testimonials.*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><circle cx="8.5" cy="9" r="1.6"/><path d="m21 15-4.5-4.5L7 20"/></svg>
                Customer Reviews
            </a>
            <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Site Settings
            </a>
        </nav>

        <div class="sidebar__foot">
            <div class="sidebar__user">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span><strong>{{ auth()->user()->name }}</strong>Signed in</span>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn--ghost btn--sm btn--block" style="margin-bottom:7px">View site</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn--danger-soft btn--sm btn--block">Log out</button>
            </form>
        </div>
    </aside>

    <div class="admin-main">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Toggle navigation menu">&#9776;</button>
                <span class="topbar__title">@yield('title', 'Dashboard')</span>
            </div>
            <div class="topbar__right">
                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn--ghost btn--sm">View site</a>
            </div>
        </header>

        <div class="content">
            @if(session('success'))
                <div class="alert alert--success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert--error">
                    {{ $errors->first() }}
                    @if($errors->count() > 1)
                        <ul style="margin:6px 0 0;padding-left:18px">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script src="{{ asset('js/admin.js') }}?v={{ filemtime(base_path('js/admin.js')) }}"></script>
@stack('scripts')
</body>
</html>

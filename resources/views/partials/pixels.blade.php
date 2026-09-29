@php
    use App\Services\PixelService;

    $pixelConfig = PixelService::config();
    $metaOn = $pixelConfig['meta']['pixelId'] !== '';
    $tiktokOn = $pixelConfig['tiktok']['pixelId'] !== '';
@endphp

@if ($metaOn || $tiktokOn)
    {{-- Meta Pixel base. Loads before site.js so window.LPTrack exists when the
         page's own scripts run. --}}
    @if ($metaOn)
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}
            (window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($pixelConfig['meta']['pixelId']));
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" alt=""
            src="https://www.facebook.com/tr?id={{ $pixelConfig['meta']['pixelId'] }}&ev=PageView&noscript=1"></noscript>
    @endif

    {{-- TikTok Pixel base --}}
    @if ($tiktokOn)
        <script>
            !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify"];
            ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
            for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){
            for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
            ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};
            ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};
            ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript";o.async=!0;
            o.src=i+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];
            a.parentNode.insertBefore(o,a)};
            ttq.load(@json($pixelConfig['tiktok']['pixelId']));
            ttq.page();}(window,document,'ttq');
        </script>
    @endif

    <script>
        window.LP = window.LP || {};
        window.LP.pixels = @json([
            'meta' => $metaOn,
            'tiktok' => $tiktokOn,
        ]);
    </script>
    <script src="{{ asset('js/pixel.js') }}?v={{ filemtime(base_path('js/pixel.js')) }}"></script>
@endif

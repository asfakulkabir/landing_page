/* Landing Page - Meta Pixel + TikTok Pixel (browser half).
   Only loaded when a pixel id is configured; see resources/views/partials/pixels.blade.php.
   Purchase is also sent server-side by App\Services\PixelService. */
(function () {
    'use strict';

    var CFG = (window.LP && window.LP.pixels) || {};

    if (!CFG.meta && !CFG.tiktok) return;

    /* TikTok names its conversion event CompletePayment; everything else matches. */
    var TIKTOK_EVENT = {
        ViewContent: 'ViewContent',
        AddToCart: 'AddToCart',
        InitiateCheckout: 'InitiateCheckout',
        Purchase: 'CompletePayment'
    };

    /* Guard against a destination being blocked or failing to load. */
    function hasFbq() {
        return typeof window.fbq === 'function';
    }

    /* The TikTok base snippet does `w[t] = w[t] || []`, so ttq is an array with
       methods hung off it, not a function. fbq really is a function, so testing
       for one silently killed every TikTok event. */
    function hasTtq() {
        return !!(window.ttq && typeof window.ttq.track === 'function');
    }

    function track(event, data) {
        var payload = data || {};

        if (CFG.meta && hasFbq()) {
            try {
                window.fbq('track', event, payload);
            } catch (e) {}
        }

        if (CFG.tiktok && hasTtq()) {
            var name = TIKTOK_EVENT[event] || event;
            var contents = (payload.contents || []).map(function (c) {
                return {
                    content_id: c.id,
                    content_type: 'product',
                    quantity: c.quantity,
                    price: c.item_price
                };
            });

            try {
                window.ttq.track(name, {
                    // TikTok keys dedup off event_id; Meta uses eventID. The shared
                    // id keeps the browser and server halves as one conversion.
                    event_id: payload.eventID || payload.event_id,
                    value: payload.value,
                    currency: payload.currency || 'BDT',
                    contents: contents
                });
            } catch (e2) {}
        }
    }

    window.LPTrack = track;
})();

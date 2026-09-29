@extends('layouts.site')

@section('title', 'ধন্যবাদ')

@php
    $cur = $settings['currency_symbol'] ?? '৳';
@endphp

@section('content')

<section class="thankyou">
    <div class="container">
        <div class="card thankyou__card">
            <div class="card__body">
                <div class="thankyou__icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>

                <h1>অর্ডারের জন্য ধন্যবাদ!</h1>
                <p class="muted">
                    আমরা আপনার অর্ডার পেয়েছি। ডেলিভারি নিশ্চিত করতে আমাদের টিম শীঘ্রই আপনাকে ফোন করবে।
                </p>

                <div class="thankyou__order">{{ $order->order_number }}</div>

                <div class="thankyou__meta">
                    <div>
                        <span>আপনার নাম</span>
                        <strong>{{ $order->customer_name }}</strong>
                    </div>
                    <div>
                        <span>ফোন নম্বর</span>
                        <strong>{{ $order->phone }}</strong>
                    </div>
                    <div>
                        <span>ডেলিভারি এলাকা</span>
                        <strong>{{ $order->delivery_zone ?: 'প্রযোজ্য নয়' }}</strong>
                    </div>
                    <div>
                        <span>অর্ডারের অবস্থা</span>
                        <strong>{{ $order->status_label }}</strong>
                    </div>
                </div>

                <div class="thankyou__panel">
                    <h2 style="font-size:16px">ডেলিভারি ঠিকানা</h2>
                    <p class="muted" style="white-space:pre-line">{{ $order->address }}</p>

                    @if($order->note)
                        <h2 style="font-size:16px">আপনার নোট</h2>
                        <p class="muted" style="white-space:pre-line">{{ $order->note }}</p>
                    @endif

                    <h2 style="font-size:16px">অর্ডার সারসংক্ষেপ</h2>
                    <ul class="summary__list" style="list-style:none;padding:0;margin:0 0 12px">
                        @foreach($order->items as $item)
                            <li class="summary__item">
                                <img class="summary__thumb" src="{{ $item->image_url }}" alt="" width="56" height="56">
                                <div class="summary__body">
                                    <p class="summary__name">{{ $item->product_name }}</p>
                                    <span class="summary__meta">{{ $cur }}{{ price((float) $item->price) }} &times; {{ $item->quantity }}</span>
                                    <div class="summary__line">
                                        <span>এই পণ্যের মোট দাম</span>
                                        <span>{{ $cur }}{{ price((float) $item->subtotal) }}</span>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <div class="totals">
                        <div class="totals__row">
                            <span>সাবটোটাল</span>
                            <strong>{{ $cur }}{{ price($order->subtotal) }}</strong>
                        </div>
                        <div class="totals__row">
                            <span>ডেলিভারি চার্জ</span>
                            <strong>{{ (float) $order->delivery_charge > 0 ? $cur . price((float) $order->delivery_charge) : 'ফ্রি' }}</strong>
                        </div>
                        <div class="totals__row totals__row--grand">
                            <span>বেরি করুন যা দিতে হবে</span>
                            <span>{{ $cur }}{{ price((float) $order->total) }}</span>
                        </div>
                    </div>

                    <div class="alert alert--success" style="margin:22px 0 0">
                        <strong>পেমেন্ট:</strong> ক্যাশ অন ডেলিভারি &mdash; অনুগ্রহ করে {{ $cur }}{{ price((float) $order->total) }} সাথে রাখুন।
                    </div>
                </div>

                <a href="{{ route('home') }}" class="btn btn--primary btn--lg" style="margin-top:26px">শপিং চালিয়ে যান</a>
            </div>
        </div>
    </div>
</section>

@endsection

@if (! empty($pixelPurchase))
@push('scripts')
<script>
    /* Browser half of Purchase. Shares eventId with the server-side Conversion API
       so Meta and TikTok deduplicate the pair into a single conversion. */
    if (typeof window.LPTrack === 'function') {
        window.LPTrack('Purchase', @json($pixelPurchase['data']) + { eventID: @json($pixelPurchase['eventId']) });
    }
</script>
@endpush
@endif

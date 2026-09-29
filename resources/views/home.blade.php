@extends('layouts.site')

@section('title', 'হোম')

@php
    $cur = $settings['currency_symbol'] ?? '৳';
@endphp

@section('content')

{{-- ============================== PRODUCTS ============================== --}}
<section class="section section--soft" id="products">
    <section class="elementor-section elementor-top-section elementor-element elementor-element-61b2141a elementor-section-content-middle elementor-section-boxed elementor-section-height-default elementor-section-height-default"
             data-id="61b2141a"
             data-element_type="section"
             data-e-type="section"
             data-settings="{&quot;background_background&quot;:&quot;classic&quot;,&quot;shape_divider_bottom_negative&quot;:&quot;yes&quot;,&quot;shape_divider_bottom&quot;:&quot;triangle&quot;}">
        <div class="elementor-shape elementor-shape-bottom" aria-hidden="true" data-negative="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" preserveAspectRatio="none">
                <path class="elementor-shape-fill" d="M500.2,94.7L0,0v100h1000V0L500.2,94.7z"></path>
            </svg>
        </div>
        <div class="elementor-container elementor-column-gap-wider">
            <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-581e1b18"
                 data-id="581e1b18"
                 data-element_type="column"
                 data-e-type="column">
                <div class="elementor-widget-wrap elementor-element-populated">
                    <div class="elementor-element elementor-element-f30e024 elementor-widget elementor-widget-image"
                         data-id="f30e024"
                         data-element_type="widget"
                         data-e-type="widget"
                         data-widget_type="image.default">
                        <img fetchpriority="high" decoding="async" width="220" height="220"
                             src="{{ asset('img/logo.jpg') }}" alt="Logo" class="attachment-full size-full wp-image-72">
                             <button type="button" class="btn btn--accent" data-order-now="6" data-name="Wireless Mouse Silent Click" data-price="1450">
                                অর্ডার করতে চাই
                            </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        @if($products->isEmpty())
            <div class="card"><div class="card__body">
                <p class="muted mb-0" style="text-align:center">এই মুহূর্তে কোনো পণ্য নেই। অনুগ্রহ করে একটু পরে আবার দেখুন।</p>
            </div></div>
        @else
            <div class="products" id="product-grid">
                @foreach($products as $product)
                    <article class="product-card" data-card="{{ $product->id }}"
                             data-content-id="product_{{ $product->id }}"
                             data-content-name="{{ $product->name }}"
                             data-content-price="{{ $product->current_price }}"
                             data-content-image="{{ $product->image_url }}">
                        <div class="product-card__media">
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" width="400" height="400">
                            @if($product->hasDiscount())
                                <span class="product-card__flag">-{{ $product->discount_percent }}%</span>
                            @endif
                        </div>
                        <div class="product-card__body">
                            <h3 class="product-card__name">{{ $product->name }}</h3>
                            <div class="product-card__price">
                                <span class="price-now">{{ $cur }}{{ price($product->current_price) }}</span>
                                @if($product->hasDiscount())
                                    <span class="price-was">{{ $cur }}{{ price((float) $product->regular_price) }}</span>
                                    <span class="price-off">সাশ্রয় {{ $cur }}{{ price((float) $product->regular_price - (float) $product->current_price) }}</span>
                                @else
                                    <span class="price-off price-off--plain">সর্বোচ্চ মূল্যে</span>
                                @endif
                            </div>
                            <button type="button"
                                    class="btn btn--accent"
                                    data-order-now="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-price="{{ $product->current_price }}">
                                অর্ডার করতে চাই
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- =============================== STATS =============================== --}}
<section class="section section--tight stats" id="stats">
    <div class="container">
        <div class="elementor-section elementor-element elementor-element-317a197"
             data-id="317a197"
             data-element_type="container"
             data-e-type="container">
            <div class="elementor-container elementor-element elementor-element-d87a29a"
                 data-id="d87a29a"
                 data-element_type="container"
                 data-e-type="container">
                <div class="elementor-element elementor-widget elementor-widget-counter"
                     data-id="f56c5cc"
                     data-element_type="widget"
                     data-e-type="widget"
                     data-widget_type="counter.default">
                    <div class="elementor-counter">
                        <div class="elementor-counter-title">খুশি কাষ্টমার</div>
                        <div class="elementor-counter-number-wrapper">
                            <span class="elementor-counter-number-prefix"></span>
                            <span class="elementor-counter-number"
                                  data-duration="2000"
                                  data-to-value="10000"
                                  data-from-value="0"
                                  data-delimiter=",">10,000</span>
                            <span class="elementor-counter-number-suffix">+</span>
                        </div>
                    </div>
                </div>
                <div class="elementor-element elementor-widget elementor-widget-heading"
                     data-id="8bb4c99"
                     data-element_type="widget"
                     data-e-type="widget"
                     data-widget_type="heading.default">
                    <h2 class="elementor-heading-title elementor-size-default">রিয়াল রিভিউ ও সন্তুষ্ট ক্রেতাদের বিশ্বাস</h2>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================= QUALITY ============================= --}}
<section class="section quality">
    <div class="elementor-section elementor-element elementor-element-7980dce"
         data-id="7980dce"
         data-element_type="section"
         data-e-type="section"
         data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
        <div class="elementor-container">
            <div class="elementor-column elementor-element elementor-element-1820676"
                 data-id="1820676"
                 data-element_type="column"
                 data-e-type="column"
                 data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
                <div class="elementor-widget-wrap">
                    <div class="elementor-element elementor-widget elementor-widget-heading"
                         data-id="eb5f227"
                         data-element_type="widget"
                         data-e-type="widget"
                         data-widget_type="heading.default">
                        <h2 class="elementor-heading-title quality__title">✨ কোয়ালিটিতে কোনো আপস নয়!</h2>
                    </div>
                    <div class="elementor-element elementor-widget elementor-widget-heading"
                         data-id="8ab5b55"
                         data-element_type="widget"
                         data-e-type="widget"
                         data-widget_type="heading.default">
                        <p class="elementor-heading-title quality__text">একই শাড়িরও বিভিন্ন কোয়ালিটি থাকে। আমরা বেছে দিই <em>সেরা কোয়ালিটির শাড়ি</em>—যাতে আপনি পান দারুণ মান ও সুন্দর ফিনিশিং।</p>
                    </div>
                    <div class="elementor-element elementor-widget elementor-widget-heading"
                         data-id="b190779"
                         data-element_type="widget"
                         data-e-type="widget"
                         data-widget_type="heading.default">
                        <p class="elementor-heading-title quality__note">💖 <em>নিশ্চিন্তে অর্ডার করুন, কোয়ালিটির দায়িত্ব আমাদের।</em></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================== TESTIMONIALS =========================== --}}
@if($testimonials->isNotEmpty())
<section class="section testimonials" id="testimonials">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">অভিজ্ঞতা</span>
            <h2>সম্মানিত কাস্টমার রিভিউ</h2>
        </div>

        <div class="slider" data-slider>
            <button class="slider__nav slider__nav--prev" type="button" data-slider-prev aria-label="আগের অভিজ্ঞতা">&#10094;</button>
            <div class="slider__viewport">
                <div class="slider__track" data-slider-track>
                    @foreach($testimonials as $testimonial)
                        <div class="slide">
                            <img src="{{ $testimonial->image_url }}" alt="গ্রাহকের অভিজ্ঞতা" loading="lazy" width="400" height="400" draggable="false">
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="slider__nav slider__nav--next" type="button" data-slider-next aria-label="পরের অভিজ্ঞতা">&#10095;</button>
            <div class="slider__dots" data-slider-dots></div>
        </div>
    </div>
</section>
@endif

@php
    $phoneDisplay = $settings['site_phone'] ?? '';
    $phoneTel = preg_replace('/\s+/', '', $phoneDisplay);
    $waRaw = preg_replace('/\D+/', '', $settings['site_whatsapp'] ?? '');
    $waDisplay = $waRaw !== '' ? $waRaw : $phoneTel;
@endphp

@if($phoneDisplay !== '' || $waDisplay !== '')
<section class="section call-section">
    <section class="elementor-section elementor-top-section elementor-element elementor-element-226e41c9 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
             data-id="226e41c9"
             data-element_type="section"
             data-e-type="section"
             data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
        <div class="elementor-container elementor-column-gap-no">
            <div class="elementor-column elementor-col-100 elementor-top-column elementor-element elementor-element-5d98f560"
                 data-id="5d98f560"
                 data-element_type="column"
                 data-e-type="column"
                 data-settings="{&quot;background_background&quot;:&quot;classic&quot;}">
                <div class="elementor-widget-wrap elementor-element-populated">
                    <div class="elementor-element elementor-element-51ba1d52 elementor-widget elementor-widget-heading"
                         data-id="51ba1d52"
                         data-element_type="widget"
                         data-e-type="widget"
                         data-widget_type="heading.default">
                        <h2 class="elementor-heading-title elementor-size-default">কল করুন</h2>
                    </div>

                    @if($phoneDisplay !== '')
                        <div class="elementor-element elementor-element-1c3eb0c2 elementor-widget elementor-widget-heading"
                             data-id="1c3eb0c2"
                             data-element_type="widget"
                             data-e-type="widget"
                             data-widget_type="heading.default">
                            <h1 class="elementor-heading-title elementor-size-default">
                                <a href="tel:{{ $phoneTel }}">{{ $phoneDisplay }}</a>
                            </h1>
                        </div>
                    @endif

                    @if($phoneTel !== '')
                        <div class="elementor-element elementor-element-3e0fad00 elementor-align-center elementor-widget elementor-widget-button"
                             data-id="3e0fad00"
                             data-element_type="widget"
                             data-e-type="widget"
                             data-widget_type="button.default">
                            <a class="elementor-button elementor-button-link elementor-size-lg elementor-animation-push" href="tel:{{ $phoneTel }}">
                                <span class="elementor-button-content-wrapper">
                                    <span class="elementor-button-icon">
                                        <svg aria-hidden="true" class="e-font-icon-svg e-fas-phone-alt" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M497.39 361.8l-112-48a24 24 0 0 0-28 6.9l-49.6 60.6A370.66 370.66 0 0 1 130.6 204.11l60.6-49.6a23.94 23.94 0 0 0 6.9-28l-48-112A24.16 24.16 0 0 0 122.6.61l-104 24A24 24 0 0 0 0 48c0 256.5 207.9 464 464 464a24 24 0 0 0 23.4-18.6l24-104a24.29 24.29 0 0 0-14.01-27.6z"></path></svg>
                                    </span>
                                    <span class="elementor-button-text">{{ $phoneDisplay }}</span>
                                </span>
                            </a>
                        </div>
                    @endif

                    @if($waDisplay !== '')
                        <div class="elementor-element elementor-element-fc59e4d elementor-align-center elementor-widget elementor-widget-button"
                             data-id="fc59e4d"
                             data-element_type="widget"
                             data-e-type="widget"
                             data-widget_type="button.default">
                            <a class="elementor-button elementor-button-link elementor-size-lg elementor-animation-push elementor-button--whatsapp" href="https://wa.me/{{ $waDisplay }}" target="_blank" rel="noopener">
                                <span class="elementor-button-content-wrapper">
                                    <span class="elementor-button-icon">
                                        <svg aria-hidden="true" class="e-font-icon-svg e-fab-whatsapp" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"></path></svg>
                                    </span>
                                    <span class="elementor-button-text">{{ $waDisplay }}</span>
                                </span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
</section>
@endif
{{-- ============================== CHECKOUT ============================== --}}
<section class="section checkout-wrap" id="checkout">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">অর্ডার করা</span>
            <h1>অর্ডার করতে নিচের ফর্মটি পূরন করুন</h1>
        </div>

        @if($errors->any())
            <div class="container" style="max-width:900px;padding:0">
                <div class="form-grid-error">{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="checkout" novalidate data-checkout-form>
            @csrf

            <div class="checkout__steps">

            <section class="checkout__step" data-step="products">
                <div class="checkout__rail">
                    <span class="checkout__num">১</span>
                </div>
                <div class="checkout__main">
                    <div class="checkout__head">
                        <h3>আপনার অর্ডার</h3>
                        <p>টিক দিন &rarr; পরিমাণ ঠিক করুন</p>
                    </div>
                    <div class="checkout__body">
                        @error('items') <div class="form-grid-error">{{ $message }}</div> @enderror

                        @if($products->isEmpty())
                            <div class="empty-note">এই মুহূর্তে অর্ডার করার মতো কোনো পণ্য নেই।</div>
                        @else
                            <div class="picker" data-product-rows>
                                @foreach($products as $index => $product)
                                    @php
                                        $oldQty = old("items.$index.quantity", 1);
                                        $checked = old("items.$index.selected", null) !== null
                                            ? (bool) old("items.$index.selected")
                                            : ($selectedId ? $product->id === $selectedId : $index === 0);
                                    @endphp
                                    <label class="picker-row" data-product-row="{{ $product->id }}" data-price="{{ $product->current_price }}">
                                        <input type="checkbox" name="items[{{ $index }}][selected]" value="1"
                                               data-row-checkbox
                                               @checked($checked)>
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                                        <img class="picker-row__thumb" src="{{ $product->image_url }}" alt="" width="56" height="56">
                                        <span class="picker-row__body">
                                            <span class="picker-row__title" data-row-name>{{ $product->name }}</span>
                                            <span class="picker-row__unit">{{ $cur }}{{ price($product->current_price) }} প্রতি পিস</span>
                                        </span>
                                        <span class="qty-stepper">
                                            <button type="button" class="qty-stepper__btn" data-qty-down
                                                    tabindex="-1" aria-label="{{ $product->name }} এর পরিমাণ কমান">&minus;</button>
                                            <input type="number" name="items[{{ $index }}][quantity]" value="{{ $oldQty }}"
                                                   min="1" max="99" step="1" data-row-qty
                                                   inputmode="numeric" aria-label="{{ $product->name }} এর পরিমাণ">
                                            <button type="button" class="qty-stepper__btn" data-qty-up
                                                    tabindex="-1" aria-label="{{ $product->name }} এর পরিমাণ বাড়ান">+</button>
                                        </span>
                                        <span class="picker-row__price" data-row-line>{{ $cur }}{{ price($product->current_price * (int) $oldQty) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            </div>

            <div class="checkout__pair">

            <section class="checkout__step" data-step="info">
                <div class="checkout__rail">
                    <span class="checkout__num">২</span>
                </div>
                <div class="checkout__main">
                    <div class="checkout__head">
                        <h3>আপনার তথ্য</h3>
                        <p>অর্ডার যাচাই ও ডেলিভারির জন্য প্রয়োজন</p>
                    </div>
                    <div class="checkout__body field-grid">
                        <div class="field field--full">
                            <label for="customer_name">আপনার নাম <span class="req">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" required
                                   value="{{ old('customer_name') }}" placeholder="যেমন: রহিম উদ্দিন"
                                   autocomplete="name">
                            @error('customer_name') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div class="field field--full">
                            <label for="address">আপনার সম্পূর্ণ ঠিকানা <span class="req">*</span></label>
                            <input type="text" id="address" name="address" required
                                   value="{{ old('address') }}" placeholder="বাসা, রোড, এলাকা, শহর"
                                   autocomplete="street-address">
                            @error('address') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div class="field field--full">
                            <label for="phone">আপনার ফোন নাম্বার <span class="req">*</span></label>
                            <input type="tel" id="phone" name="phone" required
                                   value="{{ old('phone') }}" placeholder="01XXXXXXXXX"
                                   inputmode="tel" autocomplete="tel"
                                   data-phone-input aria-describedby="phone-hint">
                            <div class="field__ok" data-phone-ok hidden>
                                <span aria-hidden="true">&#10003;</span> নম্বরটি সঠিক
                            </div>
                            <div class="hint" id="phone-hint">০১, ৮৮০ বা +৮৮০ দিয়ে শুরু হতে পারে।</div>
                            @error('phone') <div class="error">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </section>

            <div class="checkout__stack">

            <section class="checkout__step" data-step="zone">
                <div class="checkout__rail">
                    <span class="checkout__num">৩</span>
                </div>
                <div class="checkout__main">
                    <div class="checkout__head">
                        <h3>ডেলিভারি এলাকা</h3>
                        <p>আপনার এলাকা বেছে নিন, চার্জ সাথে দেখা যাবে</p>
                    </div>
                    <div class="checkout__body">
                        @error('delivery_zone_id') <div class="form-grid-error">{{ $message }}</div> @enderror

                        @if($zones->isEmpty())
                            <div class="empty-note">এখনো কোনো ডেলিভারি এলাকা যুক্ত করা হয়নি। অনুগ্রহ করে সরাসরি আমাদের সাথে যোগাযোগ করুন।</div>
                            <input type="hidden" name="delivery_zone_id" value="">
                        @else
                            <div class="radio-cards radio-cards--zones">
                                @foreach($zones as $index => $zone)
                                    <label class="radio-card" data-zone-card data-charge="{{ $zone->charge }}" data-zone-name="{{ $zone->name }}">
                                        <input type="radio" name="delivery_zone_id" value="{{ $zone->id }}" data-zone-radio
                                               @checked((string) old('delivery_zone_id', $zones->first()->id) === (string) $zone->id)>
                                        <span class="radio-card__line">
                                            <span class="radio-card__title">{{ $zone->name }}</span>
                                            @if ($zone->charge > 0)
                                                <strong class="radio-card__charge">{{ $cur }}{{ price((float) $zone->charge) }}</strong>
                                            @else
                                                <strong class="radio-card__charge radio-card__charge--free">ডেলিভারি ফ্রি</strong>
                                            @endif
                                            @if ($zone->estimated_days)
                                                <span class="radio-card__days">{{ $zone->estimated_days }} দিন</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="checkout__step checkout__step--last" data-step="payment">
                <div class="checkout__rail">
                    <span class="checkout__num">৪</span>
                </div>
                <div class="checkout__main">
                    <div class="checkout__head">
                        <h3>পেমেন্ট ও নিশ্চায়ন</h3>
                        <p>পণ্য হাতে পেয়ে টাকা পরিশোধ করুন</p>
                    </div>
                    <div class="checkout__body">
                        <div class="radio-cards radio-cards--pay">
                            <label class="radio-card radio-card--static">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <span>
                                    <span class="radio-card__title">ক্যাশ অন ডেলিভারি</span>
                                    <span class="radio-card__meta">পণ্য হাতে পেয়ে টাকা পরিশোধ করুন</span>
                                </span>
                            </label>
                        </div>

                        <div class="sticky-submit">
                            <button type="submit" class="btn btn--primary btn--lg btn--block order-cta" data-submit>
                                কনফার্ম অর্ডার &middot; <span data-submit-total>{{ $cur }}{{ price(0) }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            </div>

            </div>

            <div class="checkout__summary">

            <aside class="summary card checkout__aside">
                <div class="card__head"><h3>অর্ডার সারসংক্ষেপ</h3></div>
                <div class="card__body">
                    <table class="review">
                        <thead>
                            <tr>
                                <th class="review__col-product">পণ্য</th>
                                <th class="review__col-total">সাবটোটাল</th>
                            </tr>
                        </thead>
                        <tbody data-summary-list>
                            <tr class="review__row review__row--empty" data-summary-empty>
                                <td colspan="2">
                                    <p class="summary__name">কোনো পণ্য নির্বাচন করা হয়নি</p>
                                    <span class="summary__meta">উপরে কোনো পণ্যে টিক দিন অথবা &ldquo;এখনই অর্ডার করুন&rdquo; বাটনে চাপুন।</span>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="row">সাবটোটাল</th>
                                <td data-summary-subtotal>{{ $cur }}{{ price(0) }}</td>
                            </tr>
                            <tr>
                                <th scope="row">ডেলিভারি চার্জ</th>
                                <td data-summary-delivery>{{ $cur }}{{ price(0) }}</td>
                            </tr>
                            <tr class="review__grand">
                                <th scope="row">সর্বমোট</th>
                                <td data-summary-total>{{ $cur }}{{ price(0) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </aside>

            </div>

        </form>
    </div>
</section>

{{-- ================================ FAQ ================================ --}}
<section class="section section--tight" id="faq">
    <div class="container" style="max-width:780px">
        <div class="section-head">
            <span class="eyebrow">সাধারণ প্রশ্ন</span>
            <h2>সচরাচর জিজ্ঞাসিত প্রশ্ন</h2>
        </div>
        <div class="card"><div class="card__body">
            <h3 style="font-size:15px">ডেলিভারিতে কত সময় লাগে?</h3>
            <p class="muted">আপনার ডেলিভারি এলাকা অনুযায়ী অধিকাংশ অর্ডার ১–৩ কর্মদিবসের মধ্যে পৌঁছে যায়।</p>
            <h3 style="font-size:15px">কিভাবে টাকা পরিশোধ করব?</h3>
            <p class="muted">আমরা শুধু ক্যাশ অন ডেলিভারি গ্রহণ করি। সম্ভব হলে সঠিক পরিমাণ টাকা সাথে রাখার জন্য অনুরোধ করি।</p>
            <h3 style="font-size:15px">আমি কি একাধিক পণ্য অর্ডার করতে পারি?</h3>
            <p class="muted">জি, অবশ্যই। অর্ডার ফর্মে যত খুশি পণ্যে টিক দিন এবং প্রতিটির পরিমাণ ঠিক করে দিন।</p>
        </div></div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    window.LP = {
        currency: @json($cur),
        selectedProduct: {{ $selectedId ?: 'null' }}
    };
</script>
@endpush

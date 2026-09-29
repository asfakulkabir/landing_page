@extends('layouts.admin')

@section('title', 'পণ্যসমূহ')

@php
    $cur = \App\Models\Setting::value('currency_symbol');
@endphp

@section('content')

<div class="content__head">
    <div>
        <h1>পণ্যসমূহ</h1>
        <p class="content__sub">ল্যান্ডিং পেজে দেখানো পণ্যগুলো পরিচালনা করুন।</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="btn btn--primary">+ নতুন পণ্য</a>
</div>

<div class="panel">
    <div class="panel__head">
        <form method="GET" class="filters">
            <div class="field">
                <label for="q">খুঁজুন</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="পণ্যের নাম&hellip;">
            </div>
            <div class="field">
                <label for="status">অবস্থা</label>
                <select id="status" name="status">
                    <option value="">সব</option>
                    <option value="active" @selected(request('status') === 'active')>সক্রিয়</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>নিষ্ক্রিয়</option>
                </select>
            </div>
            <button type="submit" class="btn btn--ghost">ফিল্টার</button>
            @if(request('q') || request('status'))
                <a href="{{ route('admin.products.index') }}" class="btn btn--ghost">রিসেট</a>
            @endif
        </form>
    </div>

    @if($products->isEmpty())
        <div class="empty">
            <div class="empty__icon">&#128230;</div>
            <h3>কোনো পণ্য পাওয়া যায়নি</h3>
            <p>ছবি, নিয়মিত মূল্য ও ছাড়ের মূল্য দিয়ে আপনার প্রথম পণ্যটি যোগ করুন।</p>
            <a href="{{ route('admin.products.create') }}" class="btn btn--primary btn--sm">+ নতুন পণ্য</a>
        </div>
    @else
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th style="width:64px">ছবি</th>
                        <th>পণ্য</th>
                        <th class="right">নিয়মিত মূল্য</th>
                        <th class="right">ছাড়ের মূল্য</th>
                        <th>অবস্থা</th>
                        <th class="right">করণীয়</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <img class="thumb" src="{{ $product->image_url }}" alt="{{ $product->name }}" width="48" height="48">
                            </td>
                            <td>
                                <div class="cell-product">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        @if($product->hasDiscount())
                                            <span class="price-sale">{{ $product->discount_percent }}% ছাড়</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="right nowrap">{{ $cur }}{{ price((float) $product->regular_price) }}</td>
                            <td class="right nowrap">
                                @if($product->hasDiscount())
                                    <strong class="price-sale">{{ $cur }}{{ price((float) $product->sale_price) }}</strong>
                                    <span class="price-was">{{ $cur }}{{ price((float) $product->regular_price) }}</span>
                                @else
                                    <span class="muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.products.toggle', $product) }}" class="inline-form" data-confirm="&quot;{{ $product->name }}&quot; এর দৃশ্যমানতা বদলাবেন?">
                                    @csrf
                                    <label class="switch" title="দৃশ্যমানতা বদলান">
                                        <input type="checkbox" @checked($product->is_active) onchange="this.form.submit()">
                                        <span class="switch__track"></span>
                                        <span class="muted" style="font-size:13px">{{ $product->is_active ? 'সক্রিয়' : 'লুকানো' }}</span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn--ghost btn--sm">সম্পাদনা</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline-form"
                                          data-confirm="&quot;{{ $product->name }}&quot; মুছে ফেলবেন? এটি আর ফেরানো যাবে না।">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn--danger-soft btn--sm">মুছুন</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($products->hasPages())
            <div class="panel__foot">
                <div class="pager">
                    <span class="pager__info">{{ $products->total() }} টির মধ্যে {{ $products->firstItem() }}&ndash;{{ $products->lastItem() }} দেখানো হচ্ছে</span>
                    <div class="pager__links">
                        @if($products->onFirstPage())
                            <span class="is-disabled">&lsaquo;</span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @foreach($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                            @if($page == $products->currentPage())
                                <span class="is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}">&rsaquo;</a>
                        @else
                            <span class="is-disabled">&rsaquo;</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

@endsection

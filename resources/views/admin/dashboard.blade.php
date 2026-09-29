@extends('layouts.admin')

@section('title', 'ড্যাশবোর্ড')

@section('content')

@php
    $cur = \App\Models\Setting::value('currency_symbol');
@endphp

<div class="content__head">
    <div>
        <h1>স্বাগতম, {{ Str::before(auth()->user()->name, ' ') }}!</h1>
        <p class="content__sub">আজ আপনার দোকানে যা ঘটছে, তা নিচে দেওয়া আছে।</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('admin.products.create') }}" class="btn btn--primary">+ নতুন পণ্য</a>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn--ghost">+ অভিজ্ঞতা</a>
    </div>
</div>

<div class="stats">
    <div class="stat stat--brand">
        <div class="stat__label">মোট অর্ডার</div>
        <div class="stat__value">{{ $orderCount }}</div>
        <div class="stat__foot">{{ $pendingCount }} টি নিশ্চিতকরণের অপেক্ষায়</div>
    </div>
    <div class="stat stat--green">
        <div class="stat__label">আয়</div>
        <div class="stat__value">{{ $cur }}{{ price((float) $revenue) }}</div>
        <div class="stat__foot">বাতিল অর্ডার বাদে</div>
    </div>
    <div class="stat">
        <div class="stat__label">পণ্য</div>
        <div class="stat__value">{{ $productCount }}</div>
        <div class="stat__foot">{{ $activeProductCount }} টি সাইটে দেখা যাচ্ছে</div>
    </div>
    <div class="stat stat--amber">
        <div class="stat__label">অপেক্ষমাণ অর্ডার</div>
        <div class="stat__value">{{ $pendingCount }}</div>
        <div class="stat__foot">
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}">এখনই দেখুন &rarr;</a>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <h2>সাম্প্রতিক অর্ডার</h2>
        <a href="{{ route('admin.orders.index') }}" class="btn btn--ghost btn--sm">সব দেখুন</a>
    </div>

    @if($recentOrders->isEmpty())
        <div class="empty">
            <div class="empty__icon">&#128230;</div>
            <h3>এখনো কোনো অর্ডার নেই</h3>
            <p>ল্যান্ডিং পেজ থেকে কোনো গ্রাহক অর্ডার করলে সেটি এখানে দেখা যাবে।</p>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn--primary btn--sm">ল্যান্ডিং পেজ খুলুন</a>
        </div>
    @else
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th>অর্ডার</th>
                        <th>গ্রাহক</th>
                        <th>পণ্য</th>
                        <th>মোট</th>
                        <th>অবস্থা</th>
                        <th class="right">সময়</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($recentOrders as $order)
                        <tr>
                            <td class="nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="mono">{{ $order->order_number }}</a>
                            </td>
                            <td>
                                <strong>{{ $order->customer_name }}</strong><br>
                                <span class="muted" style="font-size:12.5px">{{ $order->phone }}</span>
                            </td>
                            <td class="nowrap">{{ $order->items->sum('quantity') }} পিস</td>
                            <td class="nowrap"><strong>{{ $cur }}{{ price((float) $order->total) }}</strong></td>
                            <td>
                                @php
                                    $badge = match ($order->status) {
                                        'delivered' => 'green',
                                        'confirmed', 'shipped' => 'blue',
                                        'cancelled' => 'red',
                                        default => 'amber',
                                    };
                                @endphp
                                <span class="badge badge--{{ $badge }}">{{ $order->status_label }}</span>
                            </td>
                            <td class="right nowrap muted" style="font-size:13px">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@endsection

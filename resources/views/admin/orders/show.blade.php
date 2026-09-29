@extends('layouts.admin')

@section('title', 'Order ' . $order->order_number)

@section('content')

@php
    $badge = match ($order->status) {
        'delivered' => 'green',
        'confirmed', 'shipped' => 'blue',
        'cancelled' => 'red',
        default => 'amber',
    };
@endphp

<div class="content__head">
    <div>
        <h1>Order {{ $order->order_number }}</h1>
        <p class="content__sub">
            Placed {{ $order->created_at->format('d M Y \a\t h:i A') }} &middot;
            <span class="badge badge--{{ $badge }}">{{ $order->status_label_en }}</span>
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a href="{{ route('admin.orders.index') }}" class="btn btn--ghost">&larr; Back to orders</a>
        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline-form"
              data-confirm="Delete order {{ $order->order_number }}? This cannot be undone.">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn--danger">Delete order</button>
        </form>
    </div>
</div>

<div class="split split--order">
    <div>
        <div class="panel">
            <div class="panel__head"><h2>Items ordered</h2></div>
            <div class="panel__body panel__body--flush">
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                        <tr>
                            <th style="width:64px">Image</th>
                            <th>Product</th>
                            <th class="right num">Price</th>
                            <th class="right num">Qty</th>
                            <th class="right num">Subtotal</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td><img class="thumb" src="{{ $item->image_url }}" alt="" width="48" height="48"></td>
                                <td>
                                    @if($item->product_id && $item->product)
                                        <a href="{{ route('admin.products.edit', $item->product) }}">{{ $item->product_name }}</a>
                                    @else
                                        {{ $item->product_name }}
                                        <br><span class="muted" style="font-size:12px">This product has been deleted</span>
                                    @endif
                                </td>
                                <td class="right nowrap num">{{ $currency }}{{ price((float) $item->price) }}</td>
                                <td class="right num">{{ $item->quantity }}</td>
                                <td class="right nowrap num"><strong>{{ $currency }}{{ price((float) $item->subtotal) }}</strong></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel__foot">
                <div class="totals" style="border:0;margin-left:auto;max-width:280px;padding:0">
                    <div class="totals__row">
                        <span>Subtotal</span>
                        <strong>{{ $currency }}{{ price($order->subtotal) }}</strong>
                    </div>
                    <div class="totals__row">
                        <span>Delivery</span>
                        <strong>{{ (float) $order->delivery_charge > 0 ? $currency . price((float) $order->delivery_charge) : 'Free' }}</strong>
                    </div>
                    <div class="totals__row totals__row--grand">
                        <span>Total</span>
                        <span>{{ $currency }}{{ price((float) $order->total) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel__head"><h2>Change status</h2></div>
            <div class="panel__body">
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="status-form">
                    @csrf
                    @method('PATCH')
                    <div class="field">
                        <label for="status">Order status</label>
                        <select id="status" name="status">
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn--primary">Save</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head"><h2>Customer details</h2></div>
            <div class="panel__body">
                <dl class="kv">
                    <dt>Name</dt>
                    <dd>{{ $order->customer_name }}</dd>

                    <dt>Phone</dt>
                    <dd><a href="tel:{{ $order->phone }}">{{ $order->phone }}</a></dd>

                    <dt>Email</dt>
                    <dd>
                        @if($order->email)
                            <a href="mailto:{{ $order->email }}">{{ $order->email }}</a>
                        @else
                            <span class="muted">Not provided</span>
                        @endif
                    </dd>

                    <dt>Delivery zone</dt>
                    <dd>{{ $order->delivery_zone ?: '—' }}</dd>

                    <dt>Address</dt>
                    <dd style="white-space:pre-line">{{ $order->address }}</dd>

                    <dt>Payment</dt>
                    <dd>Cash on delivery</dd>

                    <dt>Note</dt>
                    <dd style="white-space:pre-line">{{ $order->note ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

@endsection

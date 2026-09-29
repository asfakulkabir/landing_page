@extends('layouts.admin')

@section('title', 'Orders')

@section('content')

<div class="content__head">
    <div>
        <h1>Orders</h1>
        <p class="content__sub">Every order placed from the landing page.</p>
    </div>
</div>

<div class="panel">
    <div class="panel__head">
        <form method="GET" class="filters">
            <div class="field">
                <label for="q">Search</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Order number, name or phone">
            </div>
            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All ({{ array_sum($counts) }})</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>
                            {{ $label }} ({{ $counts[$key] ?? 0 }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn--ghost">Filter</button>
            @if(request('q') || request('status'))
                <a href="{{ route('admin.orders.index') }}" class="btn btn--ghost">Reset</a>
            @endif
        </form>
    </div>

    @if($orders->isEmpty())
        <div class="empty">
            <div class="empty__icon">&#128230;</div>
            <h3>No orders found</h3>
            <p>New orders placed on the landing page will appear here automatically.</p>
        </div>
    @else
        <div class="panel__body panel__body--flush">
            <div class="table-wrap">
                <table class="data">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Products</th>
                        <th>Zone</th>
                        <th class="right num">Total</th>
                        <th>Status</th>
                        <th>Placed</th>
                        <th class="right">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td class="nowrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="mono"><strong>{{ $order->order_number }}</strong></a>
                            </td>
                            <td>
                                <strong>{{ $order->customer_name }}</strong><br>
                                <span class="muted" style="font-size:12.5px">{{ $order->phone }}</span>
                            </td>
                            <td>
                                @foreach($order->items->take(2) as $item)
                                    <div class="muted" style="font-size:12.5px">{{ $item->product_name }} &times; {{ $item->quantity }}</div>
                                @endforeach
                                @if($order->items->count() > 2)
                                    <span class="muted" style="font-size:12px">+{{ $order->items->count() - 2 }} more</span>
                                @endif
                            </td>
                            <td class="nowrap" style="font-size:13.5px">{{ $order->delivery_zone ?: '—' }}</td>
                            <td class="right nowrap num"><strong>{{ $currency }}{{ price((float) $order->total) }}</strong></td>
                            <td class="nowrap">
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}"
                                      class="status-form" data-status-form data-status="{{ $order->status }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="status-select" data-status-select
                                            aria-label="Change status for {{ $order->order_number }}">
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><button type="submit" class="btn btn--ghost btn--sm">Update</button></noscript>
                                </form>
                            </td>
                            <td class="nowrap muted" style="font-size:13px">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn--ghost btn--sm">View</a>
                                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" class="inline-form"
                                          data-confirm="Delete order {{ $order->order_number }}? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn--danger-soft btn--sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($orders->hasPages())
            <div class="panel__foot">
                <div class="pager">
                    <span class="pager__info">Showing {{ $orders->firstItem() }}&ndash;{{ $orders->lastItem() }} of {{ $orders->total() }}</span>
                    <div class="pager__links">
                        @if($orders->onFirstPage())
                            <span class="is-disabled">&lsaquo;</span>
                        @else
                            <a href="{{ $orders->previousPageUrl() }}">&lsaquo;</a>
                        @endif

                        @foreach($orders->getUrlRange(1, $orders->lastPage()) as $page => $url)
                            @if($page == $orders->currentPage())
                                <span class="is-current">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($orders->hasMorePages())
                            <a href="{{ $orders->nextPageUrl() }}">&rsaquo;</a>
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

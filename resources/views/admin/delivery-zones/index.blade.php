@extends('layouts.admin')

@section('title', 'ডেলিভারি এলাকা')

@section('content')

<div class="content__head">
    <div>
        <h1>ডেলিভারি এলাকা</h1>
        <p class="content__sub">এই তালিকা ল্যান্ডিং পেজের অর্ডার ফর্মে দেখানো হয়।</p>
    </div>
    <a href="{{ route('admin.delivery-zones.create') }}" class="btn btn--primary">+ নতুন এলাকা</a>
</div>

<div class="panel">
    <div class="panel__head"><h2>এলাকাসমূহ ({{ $zones->count() }})</h2></div>

    @if($zones->isEmpty())
        <div class="empty">
            <div class="empty__icon">&#128666;</div>
            <h3>এখনো কোনো ডেলিভারি এলাকা নেই</h3>
            <p>গ্রাহকরা অর্ডার করার সময় ডেলিভারি চার্জ বেছে নিতে পারে—এর জন্য অন্তত একটি এলাকা যোগ করুন।</p>
            <a href="{{ route('admin.delivery-zones.create') }}" class="btn btn--primary btn--sm">+ নতুন এলাকা</a>
        </div>
    @else
        <form method="POST" action="{{ route('admin.delivery-zones.reorder') }}" data-reorder-form>
            @csrf
            <div class="panel__body panel__body--flush">
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th>এলাকার নাম</th>
                            <th class="right">চার্জ</th>
                            <th class="right">আনুমানিক দিন</th>
                            <th>অবস্থা</th>
                            <th class="right">করণীয়</th>
                        </tr>
                        </thead>
                        <tbody data-sortable>
                        @foreach($zones as $zone)
                            <tr data-id="{{ $zone->id }}">
                                <td class="sortable__handle" title="টেনে ক্রম বদলান">&#8942;&#8942;</td>
                                <td><strong>{{ $zone->name }}</strong></td>
                                <td class="right nowrap">
                                    {{ (float) $zone->charge > 0 ? $currency . price((float) $zone->charge) : 'ফ্রি' }}
                                </td>
                                <td class="right nowrap">{{ $zone->estimated_days ? $zone->estimated_days . ' দিন' : '—' }}</td>
                                <td>
                                    <span class="badge badge--{{ $zone->is_active ? 'green' : 'grey' }}">
                                        {{ $zone->is_active ? 'সক্রিয়' : 'লুকানো' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.delivery-zones.edit', $zone) }}" class="btn btn--ghost btn--sm">সম্পাদনা</a>
                                        <form method="POST" action="{{ route('admin.delivery-zones.destroy', $zone) }}" class="inline-form"
                                              data-confirm="&quot;{{ $zone->name }}&quot; এলাকাটি মুছে ফেলবেন?">
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
            <div class="panel__foot">
                <button type="submit" class="btn btn--primary btn--sm">নতুন ক্রম সংরক্ষণ করুন</button>
            </div>
        </form>
    @endif
</div>

@endsection

@push('scripts')
<script>window.LP_REORDER = true;</script>
@endpush

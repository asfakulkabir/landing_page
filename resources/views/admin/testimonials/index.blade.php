@extends('layouts.admin')

@section('title', 'গ্রাহকের অভিজ্ঞতা')

@section('content')

<div class="content__head">
    <div>
        <h1>গ্রাহকের অভিজ্ঞতা</h1>
        <p class="content__sub">শুধু ছবি &mdash; এই ছবিগুলো ল্যান্ডিং পেজের স্লাইডারে দেখানো হয়।</p>
    </div>
    <a href="{{ route('admin.testimonials.create') }}" class="btn btn--primary">+ ছবি আপলোড</a>
</div>

<div class="panel">
    <div class="panel__head"><h2>স্লাইড ({{ $testimonials->count() }})</h2></div>

    @if($testimonials->isEmpty())
        <div class="empty">
            <div class="empty__icon">&#128444;</div>
            <h3>কোনো ছবি নেই</h3>
            <p>গ্রাহকদের ছবি আপলোড করলে সেগুলো অর্ডার ফর্মের ঠিক উপরে স্লাইডারে দেখা যাবে।</p>
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn--primary btn--sm">+ ছবি আপলোড</a>
        </div>
    @else
        <form method="POST" action="{{ route('admin.testimonials.reorder') }}" data-reorder-form>
            @csrf
            <div class="panel__body panel__body--flush">
                <div class="table-wrap">
                    <table class="data">
                        <thead>
                        <tr>
                            <th style="width:40px"></th>
                            <th style="width:80px">ছবি</th>
                            <th>ফাইল</th>
                            <th>অবস্থা</th>
                            <th>যোগ করা হয়েছে</th>
                            <th class="right">করণীয়</th>
                        </tr>
                        </thead>
                        <tbody data-sortable>
                        @foreach($testimonials as $testimonial)
                            <tr data-id="{{ $testimonial->id }}">
                                <td class="sortable__handle" title="টেনে ক্রম বদলান">&#8942;&#8942;</td>
                                <td><img class="thumb thumb--lg" src="{{ $testimonial->image_url }}" alt="" width="74" height="74"></td>
                                <td class="mono">{{ basename((string) $testimonial->image) }}</td>
                                <td>
                                    <span class="badge badge--{{ $testimonial->is_active ? 'green' : 'grey' }}">
                                        {{ $testimonial->is_active ? 'সক্রিয়' : 'লুকানো' }}
                                    </span>
                                </td>
                                <td class="nowrap muted" style="font-size:13px">{{ $testimonial->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn--ghost btn--sm">সম্পাদনা</a>
                                        <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="inline-form"
                                              data-confirm="এই ছবিটি মুছে ফেলবেন?">
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

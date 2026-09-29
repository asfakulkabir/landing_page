@extends('layouts.admin')

@section('title', $zone->exists ? 'ডেলিভারি এলাকা সম্পাদনা' : 'নতুন ডেলিভারি এলাকা')

@section('content')

<div class="content__head">
    <div>
        <h1>{{ $zone->exists ? 'ডেলিভারি এলাকা সম্পাদনা করুন' : 'নতুন ডেলিভারি এলাকা যোগ করুন' }}</h1>
        <p class="content__sub"><a href="{{ route('admin.delivery-zones.index') }}">&larr; এলাকা তালিকায় ফিরে যান</a></p>
    </div>
</div>

<div style="max-width:640px">
    <form method="POST" action="{{ $zone->exists ? route('admin.delivery-zones.update', $zone) : route('admin.delivery-zones.store') }}">
        @csrf
        @if($zone->exists) @method('PUT') @endif

        <div class="panel">
            <div class="panel__body">
                <div class="field">
                    <label for="name">এলাকার নাম <span class="req">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $zone->name) }}" required
                           placeholder="যেমন: ঢাকা শহরের ভিতরে">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label for="charge">ডেলিভারি চার্জ <span class="req">*</span></label>
                        <input type="number" id="charge" name="charge" step="0.01" min="0" required
                               value="{{ old('charge', $zone->charge ?? 0) }}">
                        <div class="hint">ফ্রি ডেলিভারির জন্য 0 দিন।</div>
                        @error('charge') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label for="estimated_days">আনুমানিক দিন</label>
                        <input type="number" id="estimated_days" name="estimated_days" min="0" max="60"
                               value="{{ old('estimated_days', $zone->estimated_days) }}">
                        <div class="hint">ঐচ্ছিক। ডেলিভারি সময় হিসেবে দেখানো হবে।</div>
                        @error('estimated_days') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="field">
                    <label>দৃশ্যমানতা</label>
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $zone->exists ? $zone->is_active : true))>
                        <span class="switch__track"></span>
                        <span>অর্ডার ফর্মে দেখান</span>
                    </label>
                </div>
            </div>
            <div class="panel__foot" style="display:flex;gap:9px;flex-wrap:wrap">
                <button type="submit" class="btn btn--primary">{{ $zone->exists ? 'সংরক্ষণ করুন' : 'এলাকা তৈরি করুন' }}</button>
                <a href="{{ route('admin.delivery-zones.index') }}" class="btn btn--ghost">বাতিল</a>
            </div>
        </div>
    </form>
</div>

@endsection

@extends('layouts.admin')

@section('title', $product->exists ? 'পণ্য সম্পাদনা' : 'নতুন পণ্য')

@php
    $isEdit = $product->exists;
@endphp

@section('content')

<div class="content__head">
    <div>
        <h1>{{ $isEdit ? 'পণ্য সম্পাদনা করুন' : 'নতুন পণ্য যোগ করুন' }}</h1>
        <p class="content__sub">
            <a href="{{ route('admin.products.index') }}">&larr; পণ্য তালিকায় ফিরে যান</a>
        </p>
    </div>
    @if($isEdit)
        <a href="{{ route('home', ['product' => $product->id]) }}#checkout" target="_blank" rel="noopener" class="btn btn--ghost">সাইটে দেখুন</a>
    @endif
</div>

<form method="POST" enctype="multipart/form-data"
      action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="split split--product">
        <div>
            <div class="panel">
                <div class="panel__head"><h2>মৌলিক তথ্য</h2></div>
                <div class="panel__body">
                    <div class="field">
                        <label for="name">পণ্যের নাম <span class="req">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                               placeholder="যেমন: ওয়্যারলেস ব্লুটুথ হেডসেট">
                        @error('name') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid-2">
                        <div class="field">
                            <label for="regular_price">নিয়মিত মূল্য <span class="req">*</span></label>
                            <input type="number" id="regular_price" name="regular_price" step="0.01" min="0" required
                                   value="{{ old('regular_price', $product->regular_price) }}">
                            @error('regular_price') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <div class="field">
                            <label for="sale_price">ছাড়ের মূল্য</label>
                            <input type="number" id="sale_price" name="sale_price" step="0.01" min="0"
                                   value="{{ old('sale_price', $product->sale_price) }}">
                            <div class="hint">ছাড় না থাকলে খালি রাখুন। এটি নিয়মিত মূল্যের চেয়ে কম হতে হবে।</div>
                            @error('sale_price') <div class="error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="field">
                        <label>দৃশ্যমানতা</label>
                        <label class="switch">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->exists ? $product->is_active : true))>
                            <span class="switch__track"></span>
                            <span>এই পণ্যটি ল্যান্ডিং পেজে দেখান</span>
                        </label>
                    </div>
                </div>
                <div class="panel__foot" style="display:flex;gap:9px;flex-wrap:wrap">
                    <button type="submit" class="btn btn--primary">{{ $isEdit ? 'সংরক্ষণ করুন' : 'পণ্য তৈরি করুন' }}</button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn--ghost">বাতিল</a>
                </div>
            </div>
        </div>

        <div>
            <div class="panel">
                <div class="panel__head"><h2>পণ্যের ছবি</h2></div>
                <div class="panel__body">
                    <div class="img-preview">
                        <img src="{{ $product->image_url }}" alt="প্রিভিউ" data-image-preview>
                        <p class="muted mb-0" style="font-size:12.5px">
                            {{ $product->image ? basename($product->image) : 'এখনো কোনো ছবি আপলোড করা হয়নি' }}
                        </p>
                    </div>

                    <div class="field" style="margin-top:16px">
                        <label for="image">ছবি আপলোড করুন</label>
                        <input type="file" id="image" name="image" accept="image/*" data-image-input>
                        <div class="hint">JPG, PNG, WEBP বা GIF &middot; সর্বোচ্চ ৪ এমবি</div>
                        @error('image') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    @if($product->exists && $product->image)
                        <div class="field">
                            <label class="check">
                                <input type="checkbox" name="remove_image" value="1" data-remove-image>
                                বর্তমান ছবিটি মুছে ফেলুন
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    (function () {
        var input = document.querySelector('[data-image-input]');
        var preview = document.querySelector('[data-image-preview]');
        var remove = document.querySelector('[data-remove-image]');

        if (input && preview) {
            input.addEventListener('change', function () {
                if (!input.files || !input.files[0]) return;
                preview.src = URL.createObjectURL(input.files[0]);
                if (remove) remove.checked = false;
            });
        }

        if (remove) {
            remove.addEventListener('change', function () {
                if (remove.checked && input) input.value = '';
            });
        }
    })();
</script>
@endpush

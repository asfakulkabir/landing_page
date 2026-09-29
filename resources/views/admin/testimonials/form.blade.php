@extends('layouts.admin')

@section('title', $testimonial->exists ? 'অভিজ্ঞতা সম্পাদনা' : 'অভিজ্ঞতার ছবি আপলোড')

@php
    $isEdit = $testimonial->exists;
@endphp

@section('content')

<div class="content__head">
    <div>
        <h1>{{ $isEdit ? 'অভিজ্ঞতা সম্পাদনা করুন' : 'অভিজ্ঞতার ছবি আপলোড করুন' }}</h1>
        <p class="content__sub"><a href="{{ route('admin.testimonials.index') }}">&larr; অভিজ্ঞতা তালিকায় ফিরে যান</a></p>
    </div>
</div>

<div style="max-width:680px">
    <form method="POST" enctype="multipart/form-data"
          action="{{ $isEdit ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="panel">
            <div class="panel__head"><h2>ছবি</h2></div>
            <div class="panel__body">
                <div class="img-preview">
                    <img src="{{ $testimonial->image_url }}" alt="প্রিভিউ" data-image-preview>
                    <p class="muted mb-0" style="font-size:12.5px">
                        {{ $testimonial->image ? basename($testimonial->image) : 'এখনো কোনো ছবি নেই' }}
                    </p>
                </div>

                <div class="field" style="margin-top:16px">
                    <label for="image">
                        {{ $isEdit ? 'ছবি বদলান' : 'ছবি আপলোড করুন' }}
                        @unless($isEdit) <span class="req">*</span> @endunless
                    </label>
                    <input type="file" id="image" name="image" accept="image/*" data-image-input @unless($isEdit) required @endunless>
                    <div class="hint">বর্গাকার ছবি ভালো দেখায় &middot; JPG, PNG, WEBP বা GIF &middot; সর্বোচ্চ ৪ এমবি</div>
                    @error('image') <div class="error">{{ $message }}</div> @enderror
                </div>

                @if($isEdit)
                    <div class="field">
                        <label class="check">
                            <input type="checkbox" name="remove_image" value="1" data-remove-image>
                            বর্তমান ছবিটি মুছে ফেলুন
                        </label>
                    </div>
                @endif

                <div class="field">
                    <label>দৃশ্যমানতা</label>
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $isEdit ? $testimonial->is_active : true))>
                        <span class="switch__track"></span>
                        <span>স্লাইডারে দেখান</span>
                    </label>
                </div>
            </div>
            <div class="panel__foot" style="display:flex;gap:9px;flex-wrap:wrap">
                <button type="submit" class="btn btn--primary">{{ $isEdit ? 'সংরক্ষণ করুন' : 'আপলোড করুন' }}</button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn--ghost">বাতিল</a>
            </div>
        </div>
    </form>
</div>

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

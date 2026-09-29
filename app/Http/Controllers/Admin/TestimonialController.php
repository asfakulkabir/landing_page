<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::ordered()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $path = $request->file('image')->store('testimonials', 'public');

        Testimonial::create([
            'image' => $path,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => (int) Testimonial::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'ছবিটি আপলোড করা হয়েছে।');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', ['testimonial' => $testimonial]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ]);

        $data = ['is_active' => $request->boolean('is_active')];

        if ($request->boolean('remove_image')) {
            $this->deleteImage($testimonial->image);
            $testimonial->update(['image' => null, 'is_active' => false]);
        } elseif ($request->hasFile('image')) {
            $this->deleteImage($testimonial->image);
            $data['image'] = $request->file('image')->store('testimonials', 'public');
        }

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'স্লাইডের তথ্য পরিবর্তন করা হয়েছে।');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->deleteImage($testimonial->image);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial deleted.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:testimonials,id'],
        ]);

        foreach ($data['order'] as $index => $id) {
            Testimonial::whereKey($id)->update(['sort_order' => $index + 1]);
        }

        return back()->with('success', 'Slide order saved.');
    }

    private function deleteImage(?string $image): void
    {
        if ($image && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }
    }
}

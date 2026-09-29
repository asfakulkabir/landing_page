<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->ordered();

        if ($search = trim((string) $request->query('q'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($request->query('status') === 'active') {
            $query->where('is_active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('is_active', false);
        }

        return view('admin.products.index', ['products' => $query->paginate(20)->withQueryString()]);
    }

    public function create()
    {
        return view('admin.products.form', ['product' => new Product]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['image'] = $this->storeImage($request);
        $data['sort_order'] = (int) Product::max('sort_order') + 1;

        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'পণ্যটি সফলভাবে যোগ করা হয়েছে।');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);

        if ($request->boolean('remove_image')) {
            $this->deleteImage($product->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $this->deleteImage($product->image);
            $data['image'] = $this->storeImage($request);
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'পণ্যের তথ্য সফলভাবে পরিবর্তন করা হয়েছে।');
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', 'পণ্যের দৃশ্যমানতা পরিবর্তন করা হয়েছে।');
    }

    public function destroy(Product $product)
    {
        $this->deleteImage($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'পণ্যটি মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'regular_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['regular_price' => 'নিয়মিত মূল্য', 'sale_price' => 'ছাড়ের মূল্য']);

        $data['regular_price'] = round((float) $data['regular_price'], 2);
        $data['sale_price'] = ($data['sale_price'] ?? '') === '' ? null : round((float) $data['sale_price'], 2);
        $data['is_active'] = $request->boolean('is_active');

        if ($data['sale_price'] !== null && $data['sale_price'] >= $data['regular_price']) {
            $data['sale_price'] = null;
        }

        return $data;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $path = $request->file('image')->store('products', 'public');

        return $path ?: null;
    }

    private function deleteImage(?string $image): void
    {
        if ($image && Storage::disk('public')->exists($image)) {
            Storage::disk('public')->delete($image);
        }
    }
}

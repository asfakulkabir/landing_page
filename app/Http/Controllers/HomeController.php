<?php

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()->ordered()->get();
        $testimonials = Testimonial::active()->ordered()->get();
        $zones = DeliveryZone::active()->ordered()->get();
        $settings = Setting::all_settings();

        $requested = (int) $request->query('product', 0);
        $selectedId = $products->contains('id', $requested) ? $requested : 0;

        return view('home', compact('products', 'testimonials', 'zones', 'settings', 'selectedId'));
    }
}

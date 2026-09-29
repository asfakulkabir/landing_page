<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'productCount' => Product::count(),
            'activeProductCount' => Product::active()->count(),
            'orderCount' => Order::count(),
            'pendingCount' => Order::where('status', 'pending')->count(),
            'revenue' => Order::whereNotIn('status', ['cancelled'])->sum('total'),
            'recentOrders' => Order::with('items')->latest()->limit(8)->get(),
        ]);
    }
}

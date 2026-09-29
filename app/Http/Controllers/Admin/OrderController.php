<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items')->latest();

        if ($status = $request->query('status')) {
            if (array_key_exists($status, Order::STATUSES)) {
                $query->where('status', $status);
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(20)->withQueryString(),
            'statuses' => Order::$statusesEn,
            'counts' => Order::selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')->all(),
            'currency' => Setting::value('currency_symbol'),
        ]);
    }

    public function show(Order $order)
    {
        return view('admin.orders.show', [
            'order' => $order->load('items'),
            'statuses' => Order::$statusesEn,
            'currency' => Setting::value('currency_symbol'),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(Order::STATUSES))],
        ]);

        $order->update($data);

        return back()->with('success', 'Order status updated to "'.$order->status_label_en.'".');
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }
}

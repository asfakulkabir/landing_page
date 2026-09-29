<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\Setting;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        return view('admin.delivery-zones.index', [
            'zones' => DeliveryZone::ordered()->get(),
            'currency' => Setting::value('currency_symbol'),
        ]);
    }

    public function create()
    {
        return view('admin.delivery-zones.form', ['zone' => new DeliveryZone]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) DeliveryZone::max('sort_order') + 1;

        DeliveryZone::create($data);

        return redirect()->route('admin.delivery-zones.index')->with('success', 'ডেলিভারি এলাকা যোগ করা হয়েছে।');
    }

    public function edit(DeliveryZone $deliveryZone)
    {
        return view('admin.delivery-zones.form', ['zone' => $deliveryZone]);
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $deliveryZone->update($this->validated($request));

        return redirect()->route('admin.delivery-zones.index')->with('success', 'ডেলিভারি এলাকার তথ্য পরিবর্তন করা হয়েছে।');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $deliveryZone->delete();

        return redirect()->route('admin.delivery-zones.index')->with('success', 'ডেলিভারি এলাকা মুছে ফেলা হয়েছে।');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:delivery_zones,id'],
        ]);

        foreach ($data['order'] as $index => $id) {
            DeliveryZone::whereKey($id)->update(['sort_order' => $index + 1]);
        }

        return back()->with('success', 'ডেলিভারি এলাকার ক্রম সংরক্ষণ করা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'charge' => ['required', 'numeric', 'min:0'],
            'estimated_days' => ['nullable', 'integer', 'min:0', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ], [], ['estimated_days' => 'আনুমানিক দিন']);

        $data['charge'] = round((float) $data['charge'], 2);
        $data['estimated_days'] = ($data['estimated_days'] ?? '') === '' ? null : (int) $data['estimated_days'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}

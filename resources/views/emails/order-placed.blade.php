<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>নতুন অর্ডার {{ $order->order_number }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f9;font-family:'Noto Sans Bengali','Hind Siliguri',Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#14162b;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f5f9;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e4e6f0;border-radius:14px;overflow:hidden;">

                <tr>
                    <td style="background:#102c2a;padding:20px 24px;">
                        <div style="font-size:18px;font-weight:700;color:#ffffff;">{{ $siteName }}</div>
                        <div style="font-size:13px;color:#9fc4bf;margin-top:2px;">নতুন অর্ডার পেয়েছেন</div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:24px;">
                        <div style="font-size:20px;font-weight:700;margin-bottom:4px;">অর্ডার নম্বর: {{ $order->order_number }}</div>
                        <div style="font-size:13px;color:#7a8099;margin-bottom:20px;">
                            {{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : '' }}
                            &middot; অবস্থা: {{ $order->status_label }}
                        </div>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size:14px;line-height:1.8;">
                            <tr>
                                <td style="padding:4px 0;width:130px;color:#7a8099;">গ্রাহকের নাম</td>
                                <td style="padding:4px 0;font-weight:600;">{{ $order->customer_name }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;color:#7a8099;">ফোন নম্বর</td>
                                <td style="padding:4px 0;font-weight:600;">{{ $order->phone }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;color:#7a8099;">ঠিকানা</td>
                                <td style="padding:4px 0;font-weight:600;">{{ $order->address }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;color:#7a8099;">ডেলিভারি এলাকা</td>
                                <td style="padding:4px 0;font-weight:600;">{{ $order->delivery_zone ?: 'প্রযোজ্য নয়' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;color:#7a8099;">পেমেন্ট</td>
                                <td style="padding:4px 0;font-weight:600;">ক্যাশ অন ডেলিভারি</td>
                            </tr>
                        </table>

                        <div style="font-size:15px;font-weight:700;margin:22px 0 8px;">পণ্যের তালিকা</div>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;font-size:14px;">
                            <thead>
                                <tr>
                                    <th align="left" style="padding:8px 6px;border-bottom:2px solid #e4e6f0;color:#7a8099;font-size:12px;font-weight:700;text-transform:uppercase;">পণ্য</th>
                                    <th align="center" style="padding:8px 6px;border-bottom:2px solid #e4e6f0;color:#7a8099;font-size:12px;font-weight:700;">পরিমাণ</th>
                                    <th align="right" style="padding:8px 6px;border-bottom:2px solid #e4e6f0;color:#7a8099;font-size:12px;font-weight:700;">দাম</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->items as $item)
                                    <tr>
                                        <td style="padding:9px 6px;border-bottom:1px solid #eef0f7;">{{ $item->product_name }}</td>
                                        <td align="center" style="padding:9px 6px;border-bottom:1px solid #eef0f7;">{{ $item->quantity }}</td>
                                        <td align="right" style="padding:9px 6px;border-bottom:1px solid #eef0f7;white-space:nowrap;">
                                            {{ $currency }}{{ price((float) $item->subtotal) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="padding:12px 6px;color:#7a8099;">কোনো পণ্য নেই।</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;font-size:14px;">
                            <tr>
                                <td style="padding:4px 0;color:#4a4f68;">সাবটোটাল</td>
                                <td align="right" style="padding:4px 0;font-weight:600;">{{ $currency }}{{ price($order->subtotal) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:4px 0;color:#4a4f68;">ডেলিভারি চার্জ</td>
                                <td align="right" style="padding:4px 0;font-weight:600;">
                                    {{ (float) $order->delivery_charge > 0 ? $currency . price((float) $order->delivery_charge) : 'ফ্রি' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:10px 0;border-top:1px dashed #e4e6f0;font-size:17px;font-weight:700;">সর্বমোট</td>
                                <td align="right" style="padding:10px 0;border-top:1px dashed #e4e6f0;font-size:17px;font-weight:700;color:#d92b2b;">
                                    {{ $currency }}{{ price((float) $order->total) }}
                                </td>
                            </tr>
                        </table>

                        @if($order->note)
                            <div style="margin-top:18px;padding:12px 14px;background:#fff4dc;border:1px solid #e8cf9b;border-radius:9px;font-size:14px;">
                                <strong>অর্ডার নোট:</strong> {{ $order->note }}
                            </div>
                        @endif

                        <div style="margin-top:22px;">
                            <a href="{{ $adminUrl }}"
                               style="display:inline-block;padding:12px 22px;background:#3b2fd1;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;border-radius:9px;">
                                অর্ডারটি দেখুন
                            </a>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:16px 24px;background:#f7f8fc;border-top:1px solid #eef0f7;font-size:12px;color:#7a8099;">
                        এই ইমেইলটি {{ $siteName }} থেকে স্বয়ংক্রিয়ভাবে পাঠানো হয়েছে।
                    </td>
                </tr>
<tr>From Beginners Hut</tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

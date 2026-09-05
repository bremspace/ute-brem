<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\OnlineOrder;

class OrderController extends Controller
{
    public function show(string $code)
    {
        $order = OnlineOrder::with(['items.product', 'customer'])
            ->where('order_code', $code)
            ->firstOrFail();

        return view('website.orders.show', [
            'order' => $this->orderViewModel($order),
            'customer' => session('website_customer'),
        ]);
    }

    public function track(string $code)
    {
        $order = OnlineOrder::with(['items.product', 'deliveryTrackings'])
            ->where('order_code', $code)
            ->firstOrFail();

        return view('website.orders.track', [
            'order' => $order,
            'customer' => session('website_customer'),
        ]);
    }

    /**
     * Build the view model expected by website/orders/show.blade.php.
     */
    private function orderViewModel(OnlineOrder $order): object
    {
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Dibayar',
            'processing' => 'Diproses',
            'ready_for_pickup' => 'Siap Diambil',
            'shipped' => 'Dikirim',
            'in_transit' => 'Dalam Perjalanan',
            'delivered' => 'Terkirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'refunded' => 'Dikembalikan',
        ];

        $paymentNames = [
            'va' => 'Virtual Account',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
            'card' => 'Kartu Kredit',
        ];

        $deliveryEta = match ($order->order_type) {
            'instant' => '± 30 menit',
            'delivery' => '2–3 hari',
            default => 'Siap diambil sesuai jadwal',
        };

        return (object) [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'status_label' => $statusLabels[$order->status] ?? ucfirst((string) $order->status),
            'grand_total' => (float) $order->grand_total,
            'payment_method' => $order->payment_method,
            'payment_method_name' => $paymentNames[$order->payment_method] ?? ucfirst((string) $order->payment_method),
            'payment_reference' => $order->payment_reference,
            'payment_expires_at' => $order->paid_at ? null : $order->created_at->addHours(24),
            'delivery_method' => $order->order_type,
            'delivery_eta' => $deliveryEta,
            'items' => $order->items->map(fn ($item) => (object) [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ]),
        ];
    }
}
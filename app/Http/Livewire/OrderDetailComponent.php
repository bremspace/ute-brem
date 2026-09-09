<?php

namespace App\Http\Livewire;

use App\Models\OnlineOrder;
use Livewire\Component;

class OrderDetailComponent extends Component
{
    public string $code = '';

    public ?object $order = null;

    public string $toastMessage = '';
    public bool $showToast = false;

    public ?object $customer = null;

    public string $expiresAt = '';

    public string $paymentName = '';
    public string $paymentReference = '';

    public function mount(string $code): void
    {
        $this->code = $code;
        $this->order = $this->orderViewModel();
    }

    public function copyPaymentReference(): void
    {
        // Removed hallucinated server‑side clipboard logic – copying is handled client‑side via Alpine.
    }

    public function render()
    {
        return view('livewire.order-detail-component', [
            'customer' => $this->customer,
        ]);
    }

    private function orderViewModel(): ?object
    {
        try {
            /** @var OnlineOrder $order */
            $order = OnlineOrder::with(['items.product', 'customer'])
                ->where('order_code', $this->code)
                ->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return null;
        }

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

        $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

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
            'items' => $order->items->map(fn($item) => (object) [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ]),
            // QR modules generation for QRIS
            'qr_modules' => (function () use ($order) {
                $qrSize = 21;
                $qrModules = [];
                mt_srand(crc32($order->order_code ?? 'UTE'));
                for ($y = 0; $y < $qrSize; $y++) {
                    for ($x = 0; $x < $qrSize; $x++) {
                        $qrModules[$y][$x] = mt_rand(0, 1) === 1;
                    }
                }
                $drawFinder = function ($fx, $fy) use (&$qrModules) {
                    for ($y = 0; $y < 7; $y++) {
                        for ($x = 0; $x < 7; $x++) {
                            $on = $x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4);
                            $qrModules[$fy + $y][$fx + $x] = $on;
                        }
                    }
                };
                $drawFinder(0, 0);
                $drawFinder(14, 0);
                $drawFinder(0, 14);
                mt_srand();
                return $qrModules;
            })(),
        ];
    }
}
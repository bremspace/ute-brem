<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\DeliveryTracking;

class DeliveryService
{
    public function requestInstantDelivery(OnlineOrder $order): array
    {
        $tracking = DeliveryTracking::create([
            'online_order_id' => $order->id,
            'provider' => 'gogreen',
            'tracking_number' => 'GO-'.strtoupper(uniqid()),
            'status' => 'requested',
            'notes' => 'Driver sedang dicari',
        ]);

        $order->update(['shipping_tracking_number' => $tracking->tracking_number]);

        return [
            'success' => true,
            'provider' => 'Gojek GoSend',
            'estimated_time' => '± 30 menit',
            'tracking_number' => $tracking->tracking_number,
            'estimated_cost' => $this->calculateInstantCost($order),
        ];
    }

    public function calculateInstantCost(OnlineOrder $order): float
    {
        $baseRate = 10000.00;
        $perKm = 2500.00;
        $distance = 5.0;

        return $baseRate + ($perKm * $distance);
    }

    public function updateDeliveryStatus(OnlineOrder $order, string $status, ?string $notes = null): bool
    {
        $tracking = DeliveryTracking::where('online_order_id', $order->id)
            ->latest()
            ->first();

        if ($tracking) {
            $tracking->update([
                'status' => $status,
                'notes' => $notes,
            ]);
        }

        if ($status === 'delivered') {
            $order->update(['status' => 'delivered']);
        } elseif ($status === 'in_transit') {
            $order->update(['status' => 'in_transit']);
        }

        return true;
    }

    public function getTracking(OnlineOrder $order): array
    {
        return DeliveryTracking::where('online_order_id', $order->id)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}

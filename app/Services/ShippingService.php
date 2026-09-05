<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\ShippingProvider;
use App\Models\ShippingRate;
use App\Models\DeliveryTracking;

class ShippingService
{
    public function getShippingOptions(): array
    {
        return ShippingProvider::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function calculateRate(string $providerCode, string $originCity, string $destinationCity, float $weight): float
    {
        $rate = ShippingRate::whereHas('provider', fn ($q) => $q->where('code', $providerCode))
            ->where('origin_city', $originCity)
            ->where('destination_city', $destinationCity)
            ->where('is_active', true)
            ->where('min_weight', '<=', $weight)
            ->where('max_weight', '>=', $weight)
            ->first();

        if (! $rate) {
            return $rate?->base_rate ?? 15000.00;
        }

        return $rate->base_rate + ($rate->per_kg_rate * $weight);
    }

    public function getEstimatedDelivery(string $providerCode, string $orderType): string
    {
        if ($orderType === 'instant') {
            return '± 30 menit';
        }
        if ($orderType === 'pickup') {
            return 'Siap diambil';
        }

        return match ($providerCode) {
            'jnt' => '2–3 hari kerja',
            'jne' => '2–3 hari kerja',
            'sicepat' => '1–2 hari kerja',
            default => '3–5 hari kerja',
        };
    }

    public function createShipment(OnlineOrder $order): array
    {
        $tracking = DeliveryTracking::create([
            'online_order_id' => $order->id,
            'provider' => $order->shipping_provider ?? 'manual',
            'tracking_number' => 'TRK-'.strtoupper(uniqid()),
            'status' => 'pending',
            'notes' => 'Shipment created',
        ]);

        $order->update(['shipping_tracking_number' => $tracking->tracking_number]);

        return [
            'success' => true,
            'tracking_number' => $tracking->tracking_number,
        ];
    }

    public function trackShipment(OnlineOrder $order): array
    {
        return DeliveryTracking::where('online_order_id', $order->id)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }
}

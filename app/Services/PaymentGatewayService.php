<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\PaymentGatewayLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected string $merchantCode;
    protected string $apiKey;
    protected string $sandboxMode;
    protected string $baseUrl;

    public function __construct()
    {
        $this->merchantCode = config('services.duitku.merchant_code', '');
        $this->apiKey = config('services.duitku.api_key', '');
        $this->sandboxMode = config('services.duitku.sandbox', true);
        $this->baseUrl = $this->sandboxMode
            ? 'https://sandbox.duitku.com/webapi/api/merchant'
            : 'https://passport.duitku.com/webapi/api/merchant';
    }

    public function createPayment(OnlineOrder $order): array
    {
        $method = $order->payment_method;
        $paymentCode = $this->mapPaymentMethod($method);

        $payload = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => (int) ($order->grand_total * 100),
            'merchantOrderId' => $order->order_code,
            'productDetails' => 'UTE Parts Order #' . $order->order_code,
            'email' => $order->guest_email ?? $order->customer?->email ?? '',
            'itemDetails' => $order->items->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'name' => $item->product_name,
                'price' => (int) ($item->unit_price * 100),
                'quantity' => (int) $item->quantity,
            ])->toArray(),
            'customerDetail' => [
                'firstName' => $order->guest_name ?? $order->customer?->name ?? '',
                'email' => $order->guest_email ?? $order->customer?->email ?? '',
                'phone' => $order->guest_phone ?? $order->customer?->phone ?? '',
            ],
            'callbackUrl' => route('website.payment.callback'),
            'returnUrl' => route('website.payment.return').'?order='.$order->order_code,
            'paymentMethod' => $paymentCode,
        ];

        $signature = $this->generateSignature($payload);
        $payload['signature'] = $signature;
        $payload['expiryPeriod'] = 1440;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl.'/v2/inquiry', $payload);

            $data = $response->json();

            PaymentGatewayLog::create([
                'online_order_id' => $order->id,
                'gateway' => 'duitku',
                'gateway_order_id' => $data['referenceNo'] ?? null,
                'payment_type' => $method,
                'amount' => $order->grand_total,
                'status' => $data['statusCode'] ?? 'pending',
                'raw_response' => $data,
            ]);

            return [
                'success' => true,
                'payment_url' => $data['paymentUrl'] ?? null,
                'reference_no' => $data['referenceNo'] ?? null,
                'va_number' => $data['vaNumber'] ?? null,
                'qr_string' => $data['qrString'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('DuitKu payment creation failed', [
                'order' => $order->order_code,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function handleCallback(array $payload): bool
    {
        $signature = hash_hmac('sha256',
            $payload['merchantCode'].$payload['amount'].$payload['merchantOrderId'],
            $this->apiKey
        );

        if ($signature !== $payload['signature']) {
            Log::error('DuitKu callback signature mismatch', $payload);

            return false;
        }

        $order = OnlineOrder::where('order_code', $payload['merchantOrderId'])->first();
        if (! $order) {
            return false;
        }

        $success = ($payload['resultCode'] ?? '') === '00';

        $order->update([
            'status' => $success ? 'paid' : 'cancelled',
            'payment_reference' => $payload['reference'] ?? null,
            'paid_at' => $success ? now() : null,
        ]);

        PaymentGatewayLog::create([
            'online_order_id' => $order->id,
            'gateway' => 'duitku',
            'gateway_order_id' => $payload['reference'] ?? null,
            'payment_type' => $order->payment_method,
            'amount' => $payload['amount'] / 100,
            'status' => $success ? 'success' : 'failed',
            'raw_response' => $payload,
        ]);

        return true;
    }

    public function checkStatus(OnlineOrder $order): array
    {
        $signature = hash_hmac('sha256',
            $this->merchantCode.$order->order_code,
            $this->apiKey
        );

        try {
            $response = Http::post($this->baseUrl.'/transactionStatus', [
                'merchantCode' => $this->merchantCode,
                'merchantOrderId' => $order->order_code,
                'signature' => $signature,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('DuitKu status check failed', ['order' => $order->order_code]);

            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function generateSignature(array $payload): string
    {
        return hash_hmac('sha256',
            $payload['merchantCode'].$payload['merchantOrderId'].$payload['paymentAmount'],
            $this->apiKey
        );
    }

    protected function mapPaymentMethod(string $method): string
    {
        return match ($method) {
            'va' => 'VA',
            'ewallet' => 'OL',
            'qris' => 'QR',
            'card' => 'VC',
            default => 'VA',
        };
    }
}

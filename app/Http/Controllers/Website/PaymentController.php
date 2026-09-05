<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\OnlineOrder;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayService $paymentService
    ) {}

    /**
     * Handle DuitKu payment callback webhook.
     */
    public function callback(Request $request)
    {
        $result = $this->paymentService->handleCallback($request->all());

        return response()->json(['success' => $result]);
    }

    /**
     * Handle customer redirect after payment.
     */
    public function return(Request $request)
    {
        $orderCode = $request->query('order');

        if ($orderCode) {
            return redirect()->route('website.payment.status', $orderCode);
        }

        return redirect()->route('website.products.index');
    }

    /**
     * Show payment status page with instructions.
     */
    public function status(string $code)
    {
        $order = OnlineOrder::with('items.product')
            ->where('order_code', $code)
            ->firstOrFail();

        return view('website.orders.payment', compact('order'));
    }
}

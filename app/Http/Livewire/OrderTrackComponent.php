<?php

namespace App\Http\Livewire;

use App\Models\OnlineOrder;
use Livewire\Component;

class OrderTrackComponent extends Component
{
    public string $code = '';

    public ?object $order = null;

    public bool $refreshing = false;

    public function mount(string $code): void
    {
        $this->code = $code;
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->refreshing = true;

        $this->order = OnlineOrder::with(['items.product', 'deliveryTrackings'])
            ->where('order_code', $this->code)
            ->first();

        $this->refreshing = false;
    }

    public function render()
    {
        $order = OnlineOrder::with(['items.product', 'deliveryTrackings'])
            ->where('order_code', $this->code)
            ->first();

        return view('livewire.order-track-component', [
            'order' => $order,
            'customer' => session('website_customer'),
        ]);
    }
}
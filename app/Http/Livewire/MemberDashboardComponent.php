<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\OnlineOrder;
use App\Models\CustomerPointLedger;
use Illuminate\Support\Facades\Session;

class MemberDashboardComponent extends Component
{
    public string $tab = 'orders';

    public function mount(string $tab = 'orders')
    {
        $this->tab = $tab;
    }

    private function currentCustomer(): ?Customer
    {
        $session = session('website_customer');
        $id = is_array($session) ? (int) ($session['id'] ?? 0) : 0;
        if ($id <= 0) {
            return null;
        }
        return Customer::where('is_active', true)
            ->where('type', 'member')
            ->find($id);
    }

    public function setTab(string $tab)
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $customer = $this->currentCustomer();
        $orders = collect();
        $pointsHistory = collect();

        if ($customer) {
            if ($this->tab === 'orders') {
                $orders = OnlineOrder::with('items')
                    ->where('customer_id', $customer->id)
                    ->orderByDesc('created_at')
                    ->get();
            } elseif ($this->tab === 'points') {
                $pointsHistory = CustomerPointLedger::where('customer_id', $customer->id)
                    ->orderByDesc('created_at')
                    ->get()
                    ->map(fn ($ledger) => (object) [
                        'description' => $ledger->notes ?: ($ledger->source ?: 'Poin'),
                        'points' => (int) $ledger->points,
                        'created_at' => $ledger->created_at,
                    ]);
            }
        }

        return view('livewire.member.member-dashboard-component', [
            'tab' => $this->tab,
            'customer' => $customer,
            'orders' => $orders,
            'pointsHistory' => $pointsHistory,
        ]);
    }
}

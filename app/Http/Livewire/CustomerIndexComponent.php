<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerPointLedger;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $typeFilter = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?int $selectedCustomerId = null;
    public bool $showRedeemModal = false;
    public ?int $customerToRedeem = null;
    public int $redeemPoints = 0;
    public string $redeemReason = '';

    protected $queryString = ['search', 'statusFilter', 'typeFilter', 'perPage'];

    public function mount(): void
    {
        $this->perPage = 15;
    }

    #[On('customer-created')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    #[On('customer-updated')]
    public function onCustomerUpdated(): void
    {
        session()->flash('success', 'Customer berhasil diperbarui.');
        $this->closeDetailModal();
    }

    public function openDetailModal(int $customerId): void
    {
        $this->selectedCustomerId = $customerId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedCustomerId = null;
    }

    public function confirmRedeem(int $customerId): void
    {
        $this->customerToRedeem = $customerId;
        $this->redeemPoints = 0;
        $this->redeemReason = '';
        $this->showRedeemModal = true;
    }

    public function cancelRedeem(): void
    {
        $this->showRedeemModal = false;
        $this->customerToRedeem = null;
    }

    public function executeRedeem(): void
    {
        if (! $this->customerToRedeem || $this->redeemPoints <= 0) {
            return;
        }

        $customer = Customer::find($this->customerToRedeem);
        if (! $customer) {
            session()->flash('error', 'Customer tidak ditemukan.');
            return;
        }

        if ($customer->points_balance < $this->redeemPoints) {
            session()->flash('error', 'Poin tidak mencukupi.');
            return;
        }

        try {
            DB::transaction(function () use ($customer) {
                $customer->decrement('points_balance', $this->redeemPoints);

                CustomerPointLedger::create([
                    'customer_id' => $customer->id,
                    'amount' => -$this->redeemPoints,
                    'reference_type' => 'redeem',
                    'reference_code' => 'REDEEM-' . now()->format('YmdHis'),
                    'description' => $this->redeemReason ?: 'Redeem poin',
                    'created_by' => auth()->id(),
                ]);
            });

            session()->flash('success', 'Poin berhasil ditukarkan.');
            $this->cancelRedeem();
            $this->resetPage();
        } catch (\Throwable $exception) {
            session()->flash('error', 'Gagal menukarkan poin: ' . $exception->getMessage());
            $this->cancelRedeem();
        }
    }

    public function confirmDelete(int $customerId): void
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return;
        }

        if ($customer->sales()->exists() || $customer->serviceTransactions()->exists()) {
            session()->flash('error', 'Customer memiliki riwayat transaksi. Tidak bisa dihapus.');
            return;
        }

        $customer->delete();
        session()->flash('success', 'Customer berhasil dihapus.');
        $this->resetPage();
    }

    public function getCustomersProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Customer::with(['group', 'creator'])
            ->orderByDesc('id');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('member_code', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => Customer::count(),
            'members' => Customer::where('type', 'member')->count(),
            'regular' => Customer::where('type', 'regular')->count(),
            'active' => Customer::where('is_active', true)->count(),
        ];
    }

    public function getCustomerGroupsProperty()
    {
        return CustomerGroup::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.customers.customer-index-component', [
            'customers' => $this->customers,
            'stats' => $this->stats,
            'customerGroups' => $this->customerGroups,
        ]);
    }
}
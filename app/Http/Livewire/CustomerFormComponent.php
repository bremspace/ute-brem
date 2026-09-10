<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class CustomerFormComponent extends Component
{
    public ?Customer $customer = null;
    public bool $isEditing = false;

    public ?int $customerGroupId = null;
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $type = 'regular';
    public string $password = '';
    public bool $isActive = true;
    public ?string $memberCode = null;

    public array $customerGroups = [];

    protected $rules = [
        'customerGroupId' => 'required|exists:customer_groups,id',
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'email' => 'required|email|max:255',
        'type' => 'required|in:regular,member',
        'password' => 'nullable|string|min:6',
        'isActive' => 'boolean',
    ];

    public function mount(?int $customerId = null): void
    {
        $this->customerGroups = CustomerGroup::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        if ($customerId) {
            $this->loadCustomer($customerId);
        }
    }

    public function loadCustomer(int $customerId): void
    {
        $this->customer = Customer::findOrFail($customerId);
        $this->isEditing = true;
        $this->customerGroupId = $this->customer->customer_group_id;
        $this->name = $this->customer->name;
        $this->phone = $this->customer->phone;
        $this->email = $this->customer->email;
        $this->type = $this->customer->type;
        $this->isActive = $this->customer->is_active;
        $this->memberCode = $this->customer->member_code;
        $this->password = '';
    }

    public function updatedType(): void
    {
        if ($this->type === 'member' && empty($this->password)) {
            $this->password = 'Member123!';
        }
    }

    public function store()
    {
        $validated = $this->validate();

        $customer = DB::transaction(function () use ($validated) {
            $customer = Customer::create([
                'customer_group_id' => $validated['customerGroupId'],
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'type' => $validated['type'],
                'password' => Hash::make($validated['password'] ?? 'Member123!'),
                'is_active' => $validated['isActive'],
            ]);

            $this->ensureMemberCode($customer);

            return $customer;
        });

        session()->flash('success', 'Customer berhasil ditambahkan.');
        return redirect()->route('customers.index');
    }

    public function update()
    {
        if (! $this->customer) {
            return;
        }

        $validated = $this->validate([
            'customerGroupId' => 'required|exists:customer_groups,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'type' => 'required|in:regular,member',
            'password' => 'nullable|string|min:6',
            'isActive' => 'boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $this->customer->update([
                'customer_group_id' => $validated['customerGroupId'],
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'type' => $validated['type'],
                'is_active' => $validated['isActive'],
            ]);

            if (! empty($validated['password'])) {
                $this->customer->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            $this->ensureMemberCode($this->customer);
        });

        session()->flash('success', 'Customer berhasil diperbarui.');
        return redirect()->route('customers.index');
    }

    private function ensureMemberCode(Customer $customer): void
    {
        if (empty($customer->member_code) && $customer->type === 'member') {
            $code = 'MBR-' . str_pad($customer->id, 6, '0', STR_PAD_LEFT);
            $customer->update(['member_code' => $code]);
        }
    }

    public function render()
    {
        return view('livewire.customers.customer-form-component');
    }
}
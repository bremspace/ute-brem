<?php

namespace App\Http\Livewire;

use App\Models\BackOfficeCashAccount;
use Livewire\Component;

class CashAccountsComponent extends Component
{
    public string $code = '';
    public string $name = '';
    public string $type = 'cash';
    public float $openingBalance = 0;
    public bool $isActive = true;

    public bool $showFormModal = false;

    protected $rules = [
        'code' => 'required|string|max:50|unique:back_office_cash_accounts,code',
        'name' => 'required|string|max:255',
        'type' => 'required|in:cash,bank,ewallet',
        'openingBalance' => 'nullable|numeric|min:0',
        'isActive' => 'boolean',
    ];

    public function openFormModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function resetForm(): void
    {
        $this->code = '';
        $this->name = '';
        $this->type = 'cash';
        $this->openingBalance = 0;
        $this->isActive = true;
    }

    public function store(): void
    {
        $validated = $this->validate();

        $opening = (float) ($validated['openingBalance'] ?? 0);
        BackOfficeCashAccount::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'opening_balance' => $opening,
            'current_balance' => $opening,
            'is_active' => (bool) ($validated['isActive']),
        ]);

        session()->flash('success', 'Data kas berhasil ditambahkan.');
        $this->showFormModal = false;
    }

    public function toggleStatus(int $accountId): void
    {
        $account = BackOfficeCashAccount::find($accountId);
        if ($account) {
            $account->update(['is_active' => ! $account->is_active]);
        }
    }

    public function getCashAccountsProperty()
    {
        return BackOfficeCashAccount::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.back-office.cash-accounts-component');
    }
}
<?php

namespace App\Http\Livewire;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\BackOfficeCostCategory;
use App\Models\Customer;
use App\Services\AccountingPostingService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CashTransactionComponent extends Component
{
    protected AccountingPostingService $accountingPostingService;

    public function boot(AccountingPostingService $accountingPostingService): void
    {
        $this->accountingPostingService = $accountingPostingService;
    }

    public string $type = 'expense';
    public string $transactionDate = '';
    public ?int $cashAccountId = null;
    public ?int $costCategoryId = null;
    public ?int $customerId = null;
    public float $amount = 0;
    public string $description = '';
    public string $reference = '';

    public bool $showFormModal = false;

    public function mount(string $type = 'expense'): void
    {
        $this->type = in_array($type, ['income', 'expense']) ? $type : 'expense';
        $this->transactionDate = now()->toDateString();
    }

    public function openFormModal(): void
    {
        $this->resetForm();
        $this->transactionDate = now()->toDateString();
        $this->showFormModal = true;
    }

    public function resetForm(): void
    {
        $this->cashAccountId = null;
        $this->costCategoryId = null;
        $this->customerId = null;
        $this->amount = 0;
        $this->description = '';
        $this->reference = '';
    }

    public function store(): void
    {
        $validated = $this->validate([
            'transactionDate' => 'required|date',
            'cashAccountId' => 'required|exists:back_office_cash_accounts,id',
            'costCategoryId' => 'nullable|exists:back_office_cost_categories,id',
            'customerId' => 'nullable|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:120',
        ]);

        DB::transaction(function () use ($validated) {
            $account = BackOfficeCashAccount::lockForUpdate()->findOrFail($validated['cashAccountId']);
            $amount = (float) $validated['amount'];
            $delta = $this->type === 'income' ? $amount : -$amount;

            $next = (float) $account->current_balance + $delta;
            if ($next < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => "Saldo {$account->name} tidak mencukupi.",
                ]);
            }
            $account->update(['current_balance' => $next]);

            $code = $this->generateCode($this->type === 'income' ? 'TPK' : 'TKK', BackOfficeCashTransaction::class, 'transaction_code');
            $trx = BackOfficeCashTransaction::create([
                'transaction_code' => $code,
                'transaction_date' => $validated['transactionDate'],
                'transaction_type' => $this->type,
                'cash_account_id' => $validated['cashAccountId'],
                'cost_category_id' => $validated['costCategoryId'] ?? null,
                'customer_id' => $validated['customerId'] ?? null,
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->accountingPostingService->postCash($trx, auth()->id());
        });

        session()->flash('success', ($this->type === 'income' ? 'Pemasukan' : 'Pengeluaran') . ' berhasil dicatat.');
        $this->showFormModal = false;
    }

    private function generateCode(string $prefix, string $modelClass, string $column): string
    {
        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while ($modelClass::where($column, $code)->exists());
        return $code;
    }

    public function getActiveCashAccountsProperty()
    {
        return BackOfficeCashAccount::where('is_active', true)->orderBy('name')->get();
    }

    public function getCategoriesProperty()
    {
        return BackOfficeCostCategory::where('is_active', true)->where('type', $this->type)->orderBy('name')->get();
    }

    public function getCustomersProperty()
    {
        return Customer::where('is_active', true)->orderBy('name')->limit(200)->get(['id', 'name', 'member_code']);
    }

    public function getTransactionsProperty()
    {
        return BackOfficeCashTransaction::with(['cashAccount', 'costCategory', 'customer'])
            ->where('transaction_type', $this->type)
            ->latest('transaction_date')
            ->latest('id')
            ->limit(100)
            ->get();
    }

    public function render()
    {
        return view('livewire.back-office.cash-transaction-component');
    }
}
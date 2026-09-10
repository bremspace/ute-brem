<?php

namespace App\Http\Livewire;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\BackOfficeEmployeeAdvance;
use App\Models\User;
use App\Services\AccountingPostingService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EmployeeAdvancesComponent extends Component
{
    protected AccountingPostingService $accountingPostingService;

    public function boot(AccountingPostingService $accountingPostingService): void
    {
        $this->accountingPostingService = $accountingPostingService;
    }

    public string $advanceDate = '';
    public ?int $employeeId = null;
    public ?int $cashAccountId = null;
    public float $amount = 0;
    public string $description = '';

    public bool $showFormModal = false;

    public function mount(): void
    {
        $this->advanceDate = now()->toDateString();
    }

    public function openFormModal(): void
    {
        $this->resetForm();
        $this->advanceDate = now()->toDateString();
        $this->showFormModal = true;
    }

    public function resetForm(): void
    {
        $this->employeeId = null;
        $this->cashAccountId = null;
        $this->amount = 0;
        $this->description = '';
    }

    public function store(): void
    {
        $validated = $this->validate([
            'advanceDate' => 'required|date',
            'employeeId' => 'required|exists:users,id',
            'cashAccountId' => 'required|exists:back_office_cash_accounts,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $account = BackOfficeCashAccount::lockForUpdate()->findOrFail($validated['cashAccountId']);
            $amount = (float) $validated['amount'];

            $next = (float) $account->current_balance - $amount;
            if ($next < 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'amount' => "Saldo {$account->name} tidak mencukupi.",
                ]);
            }
            $account->update(['current_balance' => $next]);

            $code = $this->generateCode('KSB', BackOfficeCashTransaction::class, 'transaction_code');
            $cashTransaction = BackOfficeCashTransaction::create([
                'transaction_code' => $code,
                'transaction_date' => $validated['advanceDate'],
                'transaction_type' => 'employee_advance',
                'cash_account_id' => $validated['cashAccountId'],
                'employee_id' => $validated['employeeId'],
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            BackOfficeEmployeeAdvance::create([
                'advance_code' => $this->generateCode('KSB', BackOfficeEmployeeAdvance::class, 'advance_code'),
                'advance_date' => $validated['advanceDate'],
                'employee_id' => $validated['employeeId'],
                'cash_account_id' => $validated['cashAccountId'],
                'amount' => $amount,
                'status' => 'open',
                'description' => $validated['description'] ?? null,
                'cash_transaction_id' => $cashTransaction->id,
                'created_by' => auth()->id(),
            ]);

            $this->accountingPostingService->postEmployeeAdvance($cashTransaction, auth()->id());
        });

        session()->flash('success', 'Kasbon karyawan berhasil dicatat.');
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

    public function getEmployeesProperty()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function getAdvancesProperty()
    {
        return BackOfficeEmployeeAdvance::with(['employee', 'cashAccount'])
            ->latest('advance_date')
            ->latest('id')
            ->limit(100)
            ->get();
    }

    public function render()
    {
        return view('livewire.back-office.employee-advances-component');
    }
}
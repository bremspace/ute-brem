<?php

namespace App\Services;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\BackOfficeCostCategory;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class BackOfficeCashService
{
    /**
     * Record cash inflow from POS / Cashier transactions.
     */
    public function recordPOSInflow(
        float $amount,
        string $paymentMethod,
        string $reference,
        ?Customer $customer = null,
        ?string $notes = null,
        $transactionDate = null
    ): ?BackOfficeCashTransaction {
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($amount, $paymentMethod, $reference, $customer, $notes, $transactionDate) {
            $accountMethod = (strtolower($paymentMethod) === 'tempo') ? 'cash' : $paymentMethod;
            $account = $this->resolveAccountForPaymentMethod($accountMethod);
            if (!$account) {
                return null; // No active cash account found
            }

            // Lock account for update
            $account = BackOfficeCashAccount::lockForUpdate()->find($account->id);
            $account->update([
                'current_balance' => (float) $account->current_balance + $amount
            ]);

            $category = $this->resolveIncomeCategory();

            // Prefix transaction code based on payment method
            $prefix = match (strtolower($paymentMethod)) {
                'cash' => 'POS-CSH',
                'transfer' => 'POS-TRF',
                'qris' => 'POS-QRS',
                'tempo' => 'POS-TMP',
                default => 'POS-INC',
            };

            $desc = $notes ?: ($paymentMethod === 'tempo' 
                ? 'Uang Muka POS (Tempo)' 
                : 'Pendapatan POS (' . ucfirst($paymentMethod) . ')');

            return BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateTransactionCode($prefix),
                'transaction_date' => $transactionDate ?: now()->toDateString(),
                'transaction_type' => 'income',
                'cash_account_id' => $account->id,
                'cost_category_id' => $category?->id,
                'customer_id' => $customer?->id,
                'amount' => $amount,
                'description' => $desc,
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Record cash inflow from credit / debt payments.
     */
    public function recordPaymentInflow(
        float $amount,
        string $paymentMethod,
        string $reference,
        Customer $customer,
        ?string $notes = null,
        $transactionDate = null
    ): ?BackOfficeCashTransaction {
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($amount, $paymentMethod, $reference, $customer, $notes, $transactionDate) {
            $account = $this->resolveAccountForPaymentMethod($paymentMethod);
            if (!$account) {
                return null;
            }

            $account = BackOfficeCashAccount::lockForUpdate()->find($account->id);
            $account->update([
                'current_balance' => (float) $account->current_balance + $amount
            ]);

            $category = $this->resolveIncomeCategory();

            $prefix = match (strtolower($paymentMethod)) {
                'cash' => 'PAY-CSH',
                'transfer' => 'PAY-TRF',
                'qris' => 'PAY-QRS',
                default => 'PAY-INC',
            };

            return BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateTransactionCode($prefix),
                'transaction_date' => $transactionDate ?: now()->toDateString(),
                'transaction_type' => 'income',
                'cash_account_id' => $account->id,
                'cost_category_id' => $category?->id,
                'customer_id' => $customer->id,
                'amount' => $amount,
                'description' => $notes ?: 'Pelunasan Piutang Customer - ' . $customer->name,
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Reverse a previous inflow (e.g. on Void).
     */
    public function reversePOSInflow(
        float $amount,
        string $paymentMethod,
        string $reference,
        ?Customer $customer = null,
        ?string $notes = null
    ): ?BackOfficeCashTransaction {
        if ($amount <= 0) {
            return null;
        }

        if (strtolower($paymentMethod) === 'tempo') {
            return null;
        }

        return DB::transaction(function () use ($amount, $paymentMethod, $reference, $customer, $notes) {
            $account = $this->resolveAccountForPaymentMethod($paymentMethod);
            if (!$account) {
                return null;
            }

            $account = BackOfficeCashAccount::lockForUpdate()->find($account->id);
            $account->update([
                'current_balance' => max(0.0, (float) $account->current_balance - $amount)
            ]);

            $expenseCategory = BackOfficeCostCategory::where('is_active', true)
                ->where('type', 'expense')
                ->where(function ($q) {
                    $q->where('code', 'VOID')
                      ->orWhere('name', 'like', '%Void%')
                      ->orWhere('name', 'like', '%Pengembalian%');
                })
                ->first() ?: BackOfficeCostCategory::where('is_active', true)->where('type', 'expense')->first();

            return BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateTransactionCode('POS-VOD'),
                'transaction_date' => now()->toDateString(),
                'transaction_type' => 'expense',
                'cash_account_id' => $account->id,
                'cost_category_id' => $expenseCategory?->id,
                'customer_id' => $customer?->id,
                'amount' => $amount,
                'description' => $notes ?: 'Void Pendapatan POS (' . ucfirst($paymentMethod) . ')',
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function resolveAccountForPaymentMethod(string $paymentMethod): ?BackOfficeCashAccount
    {
        $paymentMethod = strtolower($paymentMethod);

        if ($paymentMethod === 'cash') {
            return BackOfficeCashAccount::where('is_active', true)
                ->where(function ($q) {
                    $q->where('type', 'cash')
                      ->orWhere('code', 'like', '%CASH%')
                      ->orWhere('code', 'like', '%TOKO%');
                })
                ->first() ?: BackOfficeCashAccount::where('is_active', true)->first();
        }

        if ($paymentMethod === 'transfer') {
            return BackOfficeCashAccount::where('is_active', true)
                ->where(function ($q) {
                    $q->where('type', 'bank')
                      ->orWhere('code', 'like', '%BANK%')
                      ->orWhere('code', 'like', '%TRF%')
                      ->orWhere('code', 'like', '%TRANSFER%');
                })
                ->first() ?: BackOfficeCashAccount::where('is_active', true)->first();
        }

        if ($paymentMethod === 'qris') {
            return BackOfficeCashAccount::where('is_active', true)
                ->where(function ($q) {
                    $q->whereIn('type', ['ewallet', 'bank'])
                      ->orWhere('code', 'like', '%QRIS%')
                      ->orWhere('code', 'like', '%EWALLET%')
                      ->orWhere('code', 'like', '%QRS%');
                })
                ->first() ?: BackOfficeCashAccount::where('is_active', true)->first();
        }

        return BackOfficeCashAccount::where('is_active', true)->first();
    }

    private function resolveIncomeCategory(): ?BackOfficeCostCategory
    {
        return BackOfficeCostCategory::where('is_active', true)
            ->where('type', 'income')
            ->where(function ($q) {
                $q->where('code', 'PEMASUKAN')
                  ->orWhere('name', 'like', '%Pemasukan%')
                  ->orWhere('name', 'like', '%Penjualan%');
            })
            ->first() ?: BackOfficeCostCategory::where('is_active', true)->where('type', 'income')->first();
    }

    public function recordPOOutflow(
        float $amount,
        string $paymentMethod,
        string $reference,
        ?\App\Models\Supplier $supplier = null,
        ?string $notes = null,
        $transactionDate = null
    ): ?BackOfficeCashTransaction {
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($amount, $paymentMethod, $reference, $supplier, $notes, $transactionDate) {
            $accountMethod = (strtolower($paymentMethod) === 'tempo') ? 'cash' : $paymentMethod;
            $account = $this->resolveAccountForPaymentMethod($accountMethod);
            if (!$account) {
                return null; // No active cash account found
            }

            // Lock account for update
            $account = BackOfficeCashAccount::lockForUpdate()->find($account->id);
            $account->update([
                'current_balance' => (float) $account->current_balance - $amount
            ]);

            $category = $this->resolveExpenseCategory();

            // Prefix transaction code based on payment method
            $prefix = match (strtolower($paymentMethod)) {
                'cash' => 'PO-CSH',
                'transfer' => 'PO-TRF',
                'qris' => 'PO-QRS',
                'tempo' => 'PO-TMP', // Down payment for tempo PO
                default => 'PO-EXP',
            };

            $desc = $notes ?: ($paymentMethod === 'tempo'
                ? 'Uang Muka Pembelian PO'
                : 'Pengeluaran Pembelian PO (' . ucfirst($paymentMethod) . ')');

            return BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateTransactionCode($prefix),
                'transaction_date' => $transactionDate ?: now()->toDateString(),
                'transaction_type' => 'expense',
                'cash_account_id' => $account->id,
                'cost_category_id' => $category?->id,
                'supplier_id' => $supplier?->id,
                'amount' => $amount,
                'description' => $desc,
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function recordSupplierPaymentOutflow(
        float $amount,
        string $paymentMethod,
        string $reference,
        ?\App\Models\Supplier $supplier = null,
        ?string $notes = null,
        $transactionDate = null
    ): ?BackOfficeCashTransaction {
        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($amount, $paymentMethod, $reference, $supplier, $notes, $transactionDate) {
            $account = $this->resolveAccountForPaymentMethod($paymentMethod);
            if (!$account) {
                return null;
            }

            $account = BackOfficeCashAccount::lockForUpdate()->find($account->id);
            $account->update([
                'current_balance' => (float) $account->current_balance - $amount
            ]);

            $category = $this->resolveExpenseCategory();

            $prefix = match (strtolower($paymentMethod)) {
                'cash' => 'SUP-CSH',
                'transfer' => 'SUP-TRF',
                'qris' => 'SUP-QRS',
                default => 'SUP-EXP',
            };

            return BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateTransactionCode($prefix),
                'transaction_date' => $transactionDate ?: now()->toDateString(),
                'transaction_type' => 'expense',
                'cash_account_id' => $account->id,
                'cost_category_id' => $category?->id,
                'supplier_id' => $supplier?->id,
                'amount' => $amount,
                'description' => $notes ?: 'Pelunasan hutang supplier (' . ucfirst($paymentMethod) . ')',
                'reference' => $reference,
                'created_by' => auth()->id(),
            ]);
        });
    }

    private function resolveExpenseCategory(): ?BackOfficeCostCategory
    {
        return BackOfficeCostCategory::where('is_active', true)
            ->where('type', 'expense')
            ->where(function ($q) {
                $q->where('code', 'PENGELUARAN')
                  ->orWhere('name', 'like', '%Pengeluaran%')
                  ->orWhere('name', 'like', '%Pembelian%');
            })
            ->first() ?: BackOfficeCostCategory::where('is_active', true)->where('type', 'expense')->first();
    }

    private function generateTransactionCode(string $prefix): string
    {
        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while (BackOfficeCashTransaction::where('transaction_code', $code)->exists());

        return $code;
    }
}

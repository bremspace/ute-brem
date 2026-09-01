<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\BackOfficeCostCategory;
use App\Models\BackOfficeEmployeeAdvance;
use App\Models\BackOfficeStockDocument;
use App\Models\Customer;
use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use App\Models\User;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BackOfficeController extends Controller
{
    public function __construct(private readonly StockLedgerService $stockLedgerService)
    {
    }

    public function dashboard()
    {
        return view('back-office.dashboard', [
            'cashAccounts' => BackOfficeCashAccount::orderBy('name')->get(),
            'recentCashTransactions' => BackOfficeCashTransaction::with(['cashAccount', 'targetCashAccount', 'costCategory', 'employee'])
                ->latest('transaction_date')
                ->latest('id')
                ->limit(12)
                ->get(),
            'recentStockDocuments' => BackOfficeStockDocument::with(['product', 'location'])
                ->latest('document_date')
                ->latest('id')
                ->limit(12)
                ->get(),
        ]);
    }

    public function cashAccounts()
    {
        return view('back-office.cash-accounts', [
            'rows' => BackOfficeCashAccount::orderBy('name')->get(),
        ]);
    }

    public function storeCashAccount(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:back_office_cash_accounts,code',
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['cash', 'bank', 'ewallet'])],
            'opening_balance' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $opening = (float) ($validated['opening_balance'] ?? 0);
        BackOfficeCashAccount::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'opening_balance' => $opening,
            'current_balance' => $opening,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Data kas berhasil ditambahkan.');
    }

    public function costCategories()
    {
        return view('back-office.cost-categories', [
            'rows' => BackOfficeCostCategory::orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function storeCostCategory(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:back_office_cost_categories,code',
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['income', 'expense'])],
            'is_active' => 'nullable|boolean',
        ]);

        BackOfficeCostCategory::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return back()->with('success', 'Data biaya berhasil ditambahkan.');
    }

    public function cashTransactions(string $type)
    {
        $type = $this->normalizeCashType($type);

        return view('back-office.cash-transactions', [
            'type' => $type,
            'title' => $type === 'income' ? 'Pemasukan' : 'Pengeluaran',
            'cashAccounts' => $this->activeCashAccounts(),
            'categories' => BackOfficeCostCategory::where('is_active', true)->where('type', $type)->orderBy('name')->get(),
            'customers' => Customer::where('is_active', true)->orderBy('name')->limit(200)->get(['id', 'name', 'member_code']),
            'rows' => BackOfficeCashTransaction::with(['cashAccount', 'costCategory', 'customer'])
                ->where('transaction_type', $type)
                ->latest('transaction_date')
                ->latest('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function storeCashTransaction(Request $request, string $type)
    {
        $type = $this->normalizeCashType($type);
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'cash_account_id' => 'required|exists:back_office_cash_accounts,id',
            'cost_category_id' => 'nullable|exists:back_office_cost_categories,id',
            'customer_id' => 'nullable|exists:customers,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:120',
        ]);

        DB::transaction(function () use ($validated, $type) {
            $account = BackOfficeCashAccount::lockForUpdate()->findOrFail($validated['cash_account_id']);
            $amount = (float) $validated['amount'];
            $this->applyCashDelta($account, $type === 'income' ? $amount : -$amount);

            BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateCode($type === 'income' ? 'TPK' : 'TKK', BackOfficeCashTransaction::class, 'transaction_code'),
                'transaction_date' => $validated['transaction_date'],
                'transaction_type' => $type,
                'cash_account_id' => $account->id,
                'cost_category_id' => $validated['cost_category_id'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', ($type === 'income' ? 'Pemasukan' : 'Pengeluaran') . ' berhasil dicatat.');
    }

    public function cashMutations()
    {
        return view('back-office.cash-mutations', [
            'cashAccounts' => $this->activeCashAccounts(),
            'rows' => BackOfficeCashTransaction::with(['cashAccount', 'targetCashAccount'])
                ->where('transaction_type', 'mutation')
                ->latest('transaction_date')
                ->latest('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function storeCashMutation(Request $request)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'cash_account_id' => 'required|exists:back_office_cash_accounts,id',
            'target_cash_account_id' => 'required|exists:back_office_cash_accounts,id|different:cash_account_id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:120',
        ]);

        DB::transaction(function () use ($validated) {
            $source = BackOfficeCashAccount::lockForUpdate()->findOrFail($validated['cash_account_id']);
            $target = BackOfficeCashAccount::lockForUpdate()->findOrFail($validated['target_cash_account_id']);
            $amount = (float) $validated['amount'];
            $this->applyCashDelta($source, -$amount);
            $this->applyCashDelta($target, $amount);

            BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateCode('MTK', BackOfficeCashTransaction::class, 'transaction_code'),
                'transaction_date' => $validated['transaction_date'],
                'transaction_type' => 'mutation',
                'cash_account_id' => $source->id,
                'target_cash_account_id' => $target->id,
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'reference' => $validated['reference'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Mutasi kas berhasil dicatat.');
    }

    public function employeeAdvances()
    {
        return view('back-office.employee-advances', [
            'cashAccounts' => $this->activeCashAccounts(),
            'employees' => User::orderBy('name')->get(['id', 'name']),
            'rows' => BackOfficeEmployeeAdvance::with(['employee', 'cashAccount'])
                ->latest('advance_date')
                ->latest('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function storeEmployeeAdvance(Request $request)
    {
        $validated = $request->validate([
            'advance_date' => 'required|date',
            'employee_id' => 'required|exists:users,id',
            'cash_account_id' => 'required|exists:back_office_cash_accounts,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $account = BackOfficeCashAccount::lockForUpdate()->findOrFail($validated['cash_account_id']);
            $amount = (float) $validated['amount'];
            $this->applyCashDelta($account, -$amount);

            $cashTransaction = BackOfficeCashTransaction::create([
                'transaction_code' => $this->generateCode('KSB', BackOfficeCashTransaction::class, 'transaction_code'),
                'transaction_date' => $validated['advance_date'],
                'transaction_type' => 'employee_advance',
                'cash_account_id' => $account->id,
                'employee_id' => $validated['employee_id'],
                'amount' => $amount,
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            BackOfficeEmployeeAdvance::create([
                'advance_code' => $this->generateCode('KSB', BackOfficeEmployeeAdvance::class, 'advance_code'),
                'advance_date' => $validated['advance_date'],
                'employee_id' => $validated['employee_id'],
                'cash_account_id' => $account->id,
                'amount' => $amount,
                'status' => 'open',
                'description' => $validated['description'] ?? null,
                'cash_transaction_id' => $cashTransaction->id,
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Kasbon karyawan berhasil dicatat.');
    }

    public function stockDocuments(string $type)
    {
        $type = $this->normalizeStockDocumentType($type);

        return view('back-office.stock-documents', [
            'type' => $type,
            'title' => $type === 'correction' ? 'Koreksi Stok' : 'Pemakaian Barang',
            'products' => Product::where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'product_code', 'name']),
            'locations' => Location::with(['racks' => fn ($q) => $q->where('is_active', true)->orderBy('name')])->where('is_active', true)->orderBy('name')->get(),
            'rows' => BackOfficeStockDocument::with(['product', 'location', 'rack'])
                ->where('document_type', $type)
                ->latest('document_date')
                ->latest('id')
                ->limit(100)
                ->get(),
        ]);
    }

    public function storeStockDocument(Request $request, string $type)
    {
        $type = $this->normalizeStockDocumentType($type);
        $movementRule = $type === 'correction'
            ? Rule::in([StockLedgerService::TYPE_ADJUSTMENT_PLUS, StockLedgerService::TYPE_ADJUSTMENT_MINUS])
            : Rule::in([StockLedgerService::TYPE_OUT]);

        $validated = $request->validate([
            'document_date' => 'required|date',
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'location_rack_id' => 'nullable|exists:location_racks,id',
            'movement_type' => ['required', $movementRule],
            'quantity' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $location = Location::findOrFail($validated['location_id']);
        $rack = ! empty($validated['location_rack_id'])
            ? LocationRack::where('location_id', $location->id)->findOrFail($validated['location_rack_id'])
            : null;

        try {
            DB::transaction(function () use ($validated, $type, $product, $location, $rack) {
                $code = $this->generateCode($type === 'correction' ? 'KRS' : 'PMB', BackOfficeStockDocument::class, 'document_code');
                $movement = $this->stockLedgerService->applyMovement(
                    $product,
                    $location,
                    $validated['movement_type'],
                    (float) $validated['quantity'],
                    $validated['description'] ?? null,
                    $validated['document_date'],
                    $type === 'correction' ? 'back_office_correction' : 'back_office_usage',
                    $code,
                    $rack
                );

                BackOfficeStockDocument::create([
                    'document_code' => $code,
                    'document_date' => $validated['document_date'],
                    'document_type' => $type,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'location_rack_id' => $rack?->id,
                    'movement_type' => $validated['movement_type'],
                    'quantity' => (float) $validated['quantity'],
                    'description' => $validated['description'] ?? null,
                    'stock_movement_id' => $movement->id,
                    'created_by' => auth()->id(),
                ]);
            });
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return back()->with('success', ($type === 'correction' ? 'Koreksi stok' : 'Pemakaian barang') . ' berhasil dicatat.');
    }

    private function activeCashAccounts()
    {
        return BackOfficeCashAccount::where('is_active', true)->orderBy('name')->get();
    }

    private function applyCashDelta(BackOfficeCashAccount $account, float $delta): void
    {
        $next = (float) $account->current_balance + $delta;
        if ($next < 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount' => "Saldo {$account->name} tidak mencukupi.",
            ]);
        }

        $account->update(['current_balance' => $next]);
    }

    private function normalizeCashType(string $type): string
    {
        return in_array($type, ['income', 'expense'], true) ? $type : 'expense';
    }

    private function normalizeStockDocumentType(string $type): string
    {
        return in_array($type, ['correction', 'usage'], true) ? $type : 'usage';
    }

    private function generateCode(string $prefix, string $modelClass, string $column): string
    {
        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while ($modelClass::where($column, $code)->exists());

        return $code;
    }
}

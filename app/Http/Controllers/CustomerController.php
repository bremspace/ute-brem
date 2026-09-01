<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPointLedger;
use App\Models\CustomerGroup;
use App\Models\Sale;
use App\Models\ServiceTransaction;
use App\Models\TransactionPayment;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customers.index', [
            'customersTableReady' => Schema::hasTable('customers'),
        ]);
    }

    public function importForm()
    {
        return view('masters.import', [
            'title' => 'Import Customer',
            'backRoute' => route('customers.index'),
            'templateRoute' => route('customers.import.template'),
            'previewRoute' => route('customers.import.preview'),
            'storeRoute' => route('customers.import.store'),
            'preview' => session('customer_import_preview'),
            'templateNote' => 'Kolom wajib: name dan type. Untuk type member, password wajib diisi jika belum ada default.',
            'columns' => [
                'member_code' => 'Kode Member',
                'customer_group' => 'Customer Group',
                'name' => 'Nama',
                'phone' => 'No HP',
                'email' => 'Email',
                'type' => 'Tipe',
                'password' => 'Password',
                'is_active' => 'Aktif',
            ],
        ]);
    }

    public function downloadImportTemplate()
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->route('customers.import')->with('error', 'Package Excel belum terinstall.');
        }

        return $this->downloadSpreadsheetTemplate(
            'Import Customer',
            ['member_code', 'customer_group', 'name', 'phone', 'email', 'type', 'password', 'is_active'],
            [
                ['MBR-000111', 'Member Gold', 'Fajar Pratama', '081234567890', 'fajar@example.com', 'member', 'Member123!', 'ya'],
                ['', 'Retail', 'Rina Toko', '081277788899', '', 'regular', '', 'ya'],
            ],
            'template-import-customer.xlsx'
        );
    }

    public function previewImport(Request $request)
    {
        if (! class_exists(IOFactory::class)) {
            return redirect()->route('customers.import')->with('error', 'Package Excel belum terinstall.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $preview = $this->buildImportPreview(
            $this->parseImportExcel($validated['file']->getRealPath()),
            function (array $data): array {
                $errors = [];
                $notes = [];

                $name = trim((string) ($data['name'] ?? ''));
                $phone = trim((string) ($data['phone'] ?? ''));
                $email = trim((string) ($data['email'] ?? ''));
                $type = mb_strtolower(trim((string) ($data['type'] ?? '')));
                $memberCode = trim((string) ($data['member_code'] ?? ''));
                $groupName = trim((string) ($data['customer_group'] ?? ''));
                $password = (string) ($data['password'] ?? '');

                if ($name === '') {
                    $errors[] = 'Nama customer wajib diisi.';
                }

                if (! in_array($type, ['regular', 'member'], true)) {
                    $errors[] = 'Tipe harus regular atau member.';
                }

                if ($type === 'member' && trim($password) === '') {
                    $errors[] = 'Password wajib diisi untuk customer member.';
                }

                if ($phone !== '' && Customer::where('phone', $phone)->exists()) {
                    $errors[] = 'No HP customer sudah ada.';
                }

                if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Format email tidak valid.';
                }

                if ($email !== '' && Customer::whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->exists()) {
                    $errors[] = 'Email customer sudah ada.';
                }

                if ($memberCode !== '' && Schema::hasColumn('customers', 'member_code') && Customer::where('member_code', $memberCode)->exists()) {
                    $errors[] = 'Kode member sudah ada.';
                }

                if ($groupName !== '' && ! CustomerGroup::whereRaw('LOWER(name) = ?', [mb_strtolower($groupName)])->exists()) {
                    $errors[] = 'Customer group tidak ditemukan.';
                }

                if (($data['is_active'] ?? '') === '') {
                    $notes[] = 'Status aktif kosong, default ke aktif.';
                }

                return [$errors, $notes];
            }
        );

        session(['customer_import_preview' => $preview]);

        return redirect()->route('customers.import');
    }

    public function storeImport()
    {
        $preview = session('customer_import_preview');

        if (! $preview || empty($preview['rows'])) {
            return redirect()->route('customers.import')->with('error', 'Preview import tidak ditemukan.');
        }

        if (! empty($preview['has_errors'])) {
            return redirect()->route('customers.import')->with('error', 'Import belum bisa diproses karena masih ada baris error.');
        }

        $count = 0;
        DB::transaction(function () use ($preview, &$count) {
            foreach ($preview['rows'] as $row) {
                $data = $row['data'];
                $groupId = null;
                if (! empty($data['customer_group'])) {
                    $groupId = CustomerGroup::whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $data['customer_group']))])->value('id');
                }

                $payload = [
                    'customer_group_id' => $groupId,
                    'name' => trim((string) $data['name']),
                    'phone' => $this->nullableString($data['phone'] ?? null),
                    'email' => $this->nullableString($data['email'] ?? null),
                    'type' => mb_strtolower(trim((string) $data['type'])),
                    'password' => $this->nullableString($data['password'] ?? null),
                    'is_active' => $this->parseBoolean($data['is_active'] ?? 'ya', true),
                ];

                if (Schema::hasColumn('customers', 'member_code')) {
                    $payload['member_code'] = $this->nullableString($data['member_code'] ?? null);
                }

                $customer = Customer::create($payload);
                $this->ensureMemberCode($customer);
                $count++;
            }
        });

        session()->forget('customer_import_preview');

        UserLog::log('IMPORT_CUSTOMERS', 'Imported customers from Excel template', null, null, ['count' => $count]);

        return redirect()->route('customers.index')->with('success', $count . ' customer berhasil diimport.');
    }

    public function export(Request $request)
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->route('customers.index')->with('error', 'Package Excel belum terinstall.');
        }

        if (! Schema::hasTable('customers')) {
            return redirect()->route('customers.index')->with('error', 'Tabel customers belum tersedia.');
        }

        $validated = $request->validate([
            'scope' => ['nullable', Rule::in(['selected', 'all'])],
            'selected_ids' => 'nullable|string',
        ]);

        $query = Customer::query()->with('group')->orderBy('name');

        if (($validated['scope'] ?? 'all') === 'selected') {
            $ids = collect(explode(',', (string) ($validated['selected_ids'] ?? '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                return redirect()->route('customers.index')->with('error', 'Pilih minimal 1 customer untuk export.');
            }

            $query->whereIn('id', $ids);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return redirect()->route('customers.index')->with('error', 'Tidak ada customer untuk diexport.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customer');
        $headers = ['Kode Member', 'Customer Group', 'Nama', 'No HP', 'Email', 'Tipe', 'Total Poin', 'Status'];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        foreach ($rows as $rowIndex => $row) {
            $values = [
                Schema::hasColumn('customers', 'member_code') ? ($row->member_code ?? '') : '',
                $row->group?->name,
                $row->name,
                $row->phone,
                $row->email,
                $row->type === 'member' ? 'member' : 'regular',
                (string) ((int) ($row->points_balance ?? 0)),
                $row->is_active ? 'Aktif' : 'Nonaktif',
            ];

            foreach ($values as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValueExplicit($column . ($rowIndex + 2), (string) $value, DataType::TYPE_STRING);
            }
        }

        return $this->downloadSpreadsheet($spreadsheet, 'customer-export.xlsx');
    }

    public function create()
    {
        return view('customers.create', $this->formData());
    }

    public function show(Customer $customer)
    {
        $customer->load('group');

        $ledgers = collect();
        if (Schema::hasTable('customer_point_ledgers')) {
            $ledgers = CustomerPointLedger::query()
                ->when(Schema::hasTable('customer_point_ledger_files'), fn ($q) => $q->with('files'))
                ->where('customer_id', $customer->id)
                ->latest('id')
                ->limit(60)
                ->get();
        }

        $sales = collect();
        $salesTableReady = Schema::hasTable('sales');
        if ($salesTableReady) {
            $sales = Sale::query()
                ->with(['location:id,name', 'cashier:id,name'])
                ->where('customer_id', $customer->id)
                ->orderByDesc('sale_at')
                ->limit(50)
                ->get(['id', 'sale_code', 'sale_at', 'location_id', 'cashier_id', 'grand_total', 'paid_amount', 'payment_method', 'credit_status', 'credit_due_at', 'points_earned', 'status']);
        }

        $creditSales = collect();
        $creditServices = collect();
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'credit_status')) {
            $creditSales = Sale::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'paid')
                ->whereIn('credit_status', ['unpaid', 'partial'])
                ->orderBy('credit_due_at')
                ->get(['id', 'sale_code', 'sale_at', 'grand_total', 'paid_amount', 'credit_due_at', 'credit_status']);
        }

        if (Schema::hasTable('service_transactions') && Schema::hasColumn('service_transactions', 'credit_status')) {
            $creditServices = ServiceTransaction::query()
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->whereIn('credit_status', ['unpaid', 'partial'])
                ->orderBy('credit_due_at')
                ->get(['id', 'service_code', 'service_at', 'grand_total', 'paid_amount', 'credit_due_at', 'credit_status']);
        }

        return view('customers.show', [
            'customer' => $customer,
            'ledgersTableReady' => Schema::hasTable('customer_point_ledgers'),
            'ledgers' => $ledgers,
            'customersTableReady' => Schema::hasTable('customers'),
            'salesTableReady' => $salesTableReady,
            'sales' => $sales,
            'creditSales' => $creditSales,
            'creditServices' => $creditServices,
            'pointsBalanceReady' => Schema::hasColumn('customers', 'points_balance'),
            'ledgerFilesReady' => Schema::hasTable('customer_point_ledger_files'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        $customer = DB::transaction(function () use ($validated) {
            $customer = Customer::create($this->payload($validated));
            $this->ensureMemberCode($customer);
            return $customer;
        });

        UserLog::log('CREATE_CUSTOMER', "Created customer: {$customer->name}", null, null, $customer->only(['name', 'email', 'phone', 'type', 'is_active']));

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', array_merge($this->formData(), compact('customer')));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $this->validateCustomer($request, $customer);
        $oldValues = $customer->only(['name', 'email', 'phone', 'type', 'customer_group_id', 'is_active']);

        DB::transaction(function () use ($validated, $customer) {
            $customer->update($this->payload($validated, $customer));
            $this->ensureMemberCode($customer);
        });

        UserLog::log('UPDATE_CUSTOMER', "Updated customer: {$customer->name}", null, $oldValues, $customer->fresh()->only(['name', 'email', 'phone', 'type', 'customer_group_id', 'is_active']));

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer)
    {
        UserLog::log('DELETE_CUSTOMER', "Deleted customer: {$customer->name}", null, $customer->only(['name', 'email', 'phone', 'type', 'is_active']));
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }

    public function redeemPoints(Request $request, Customer $customer)
    {
        if (! Schema::hasTable('customers') || ! Schema::hasTable('customer_point_ledgers') || ! Schema::hasColumn('customers', 'points_balance')) {
            return back()->with('error', 'Fitur poin belum tersedia. Pastikan migration poin sudah dijalankan.');
        }

        if ($customer->type !== 'member' || ! $customer->is_active) {
            return back()->with('error', 'Tukar poin hanya bisa untuk customer member aktif.');
        }

        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,webp,pdf,xls,xlsx,doc,docx',
        ]);

        $points = (int) $validated['points'];
        $current = (int) ($customer->points_balance ?? 0);
        if ($points > $current) {
            return back()->withErrors(['points' => 'Poin tidak cukup.'])->withInput();
        }

        $hasFilesTable = Schema::hasTable('customer_point_ledger_files');

        DB::transaction(function () use ($request, $customer, $points, $current, $validated, $hasFilesTable) {
            $after = $current - $points;
            $referencePrefix = PrinterSettingController::referencePrefix('point_redemption', 'REDEEM');
            $reference = $referencePrefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);

            $ledger = CustomerPointLedger::create([
                'customer_id' => $customer->id,
                'sale_id' => null,
                'points' => -$points,
                'balance_after' => $after,
                'source' => 'redeem',
                'reference_code' => $reference,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $customer->update(['points_balance' => $after]);

            if ($hasFilesTable && $request->hasFile('attachments')) {
                foreach ((array) $request->file('attachments') as $file) {
                    if (! $file) {
                        continue;
                    }

                    $path = $file->store("customer-point-ledgers/{$ledger->id}", 'public');

                    \App\Models\CustomerPointLedgerFile::create([
                        'customer_point_ledger_id' => $ledger->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }
        });

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', "Tukar poin berhasil dicatat ({$points} poin).");
    }

    public function storeCreditPayment(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'transaction_type' => ['required', Rule::in(['sale', 'service'])],
            'transaction_id' => 'required|integer',
            'payment_at' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris'])],
            'reference' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['transaction_type'] === 'sale') {
            $transaction = Sale::query()
                ->where('customer_id', $customer->id)
                ->where('status', 'paid')
                ->findOrFail((int) $validated['transaction_id']);
            $saleId = $transaction->id;
            $serviceId = null;
            $success = 'Pelunasan tempo transaksi berhasil dicatat.';
        } else {
            $transaction = ServiceTransaction::query()
                ->where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->findOrFail((int) $validated['transaction_id']);
            $saleId = null;
            $serviceId = $transaction->id;
            $success = 'Pelunasan tempo service berhasil dicatat.';
        }

        $outstanding = max(0, (float) $transaction->grand_total - (float) $transaction->paid_amount);
        $amount = (float) $validated['amount'];

        if ($outstanding <= 0) {
            return back()->with('error', 'Tagihan tempo ini sudah lunas.');
        }

        if ($amount > $outstanding) {
            return back()->withInput()->with('error', 'Nominal pelunasan lebih besar dari sisa tempo.');
        }

        DB::transaction(function () use ($validated, $customer, $transaction, $saleId, $serviceId, $amount) {
            TransactionPayment::create([
                'sale_id' => $saleId,
                'service_transaction_id' => $serviceId,
                'customer_id' => $customer->id,
                'payment_at' => $validated['payment_at'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            $paid = (float) $transaction->paid_amount + $amount;
            $transaction->update([
                'paid_amount' => $paid,
                'change_amount' => 0,
                'credit_status' => $paid >= (float) $transaction->grand_total ? 'paid' : 'partial',
            ]);
        });

        return back()->with('success', $success);
    }

    public function getData()
    {
        if (! Schema::hasTable('customers')) {
            return response()->json([
                'draw' => (int) request('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $select = ['id', 'customer_group_id', 'name', 'phone', 'email', 'type', 'is_active', 'created_at'];
        if (Schema::hasColumn('customers', 'points_balance')) {
            $select[] = 'points_balance';
        }
        if (Schema::hasColumn('customers', 'member_code')) {
            $select[] = 'member_code';
        }

        $rows = Customer::query()
            ->with('group')
            ->select($select);

        return datatables()->of($rows)
            ->addColumn('select_checkbox', fn ($row) => '<input type="checkbox" class="form-check-input row-export-checkbox" value="' . e($row->id) . '">')
            ->addColumn('name_link', fn ($row) => '<a href="' . route('customers.show', $row->id) . '" class="fw-semibold text-decoration-none text-primary">' . e($row->name) . '</a>')
            ->addColumn('member_code_link', function ($row) {
                if (! Schema::hasColumn('customers', 'member_code')) {
                    return '-';
                }

                $code = $row->member_code ?: '-';
                if ($code === '-') {
                    return $code;
                }

                return '<a href="' . route('customers.show', $row->id) . '" class="fw-semibold text-decoration-none text-primary">' . e($code) . '</a>';
            })
            ->addColumn('group_name', fn ($row) => $row->group?->name ?: '-')
            ->addColumn('type_badge', fn ($row) => '<span class="badge bg-label-' . ($row->type === 'member' ? 'primary' : 'secondary') . '">' . ($row->type === 'member' ? 'Member' : 'Biasa') . '</span>')
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('points_label', function ($row) {
                $points = property_exists($row, 'points_balance') ? (int) ($row->points_balance ?? 0) : 0;
                return number_format($points, 0, ',', '.');
            })
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';

                $actions .= '<a class="dropdown-item" href="' . route('customers.show', $row->id) . '"><i class="bx bx-show me-1"></i>Detail</a>';

                if (auth()->user()->hasPermission('master.customer_groups.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('customers.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }

                if (auth()->user()->hasPermission('master.customer_groups.delete')) {
                    $actions .= '<form action="' . route('customers.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus customer ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }

                return $actions . '</div></div>';
            })
            ->rawColumns(['select_checkbox', 'name_link', 'member_code_link', 'type_badge', 'status_badge', 'action'])
            ->make(true);
    }

    private function formData(): array
    {
        return [
            'customerGroups' => CustomerGroup::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
        ];
    }

    private function validateCustomer(Request $request, ?Customer $customer = null): array
    {
        $rules = [
            'customer_group_id' => 'nullable|exists:customer_groups,id',
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('customers', 'phone')->ignore($customer?->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer?->id)],
            'type' => ['required', Rule::in(['regular', 'member'])],
            'password' => [$customer ? 'nullable' : 'required_if:type,member', 'nullable', 'string', 'min:8'],
            'is_active' => 'nullable|boolean',
        ];

        if (Schema::hasColumn('customers', 'member_code')) {
            $rules['member_code'] = ['nullable', 'string', 'max:50', Rule::unique('customers', 'member_code')->ignore($customer?->id)];
        }

        return $request->validate($rules);
    }

    private function payload(array $validated, ?Customer $customer = null): array
    {
        $payload = [
            'customer_group_id' => $validated['customer_group_id'] ?? null,
            'member_code' => Schema::hasColumn('customers', 'member_code')
                ? ($validated['member_code'] ?? ($customer?->member_code ?? null))
                : null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'type' => $validated['type'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        } elseif (! $customer && $validated['type'] === 'regular') {
            $payload['password'] = null;
        }

        return $payload;
    }

    private function ensureMemberCode(Customer $customer): void
    {
        if (! Schema::hasColumn('customers', 'member_code')) {
            return;
        }

        if ($customer->type !== 'member') {
            return;
        }

        if (! empty($customer->member_code)) {
            return;
        }

        $code = 'MBR-' . str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT);

        // Extremely defensive: if collision, append random suffix.
        if (Customer::query()->where('member_code', $code)->where('id', '!=', $customer->id)->exists()) {
            $code .= '-' . random_int(10, 99);
        }

        $customer->update(['member_code' => $code]);
    }

    private function parseImportExcel(string $path): array
    {
        $sheet = IOFactory::load($path)->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        if (count($rows) < 2) {
            return [];
        }

        $headerRow = array_shift($rows);
        $headers = collect($headerRow)->map(fn ($value) => Str::snake(trim((string) $value)))->values()->all();

        return collect($rows)->map(function ($row, $index) use ($headers) {
            $values = array_values($row);
            $data = [];
            foreach ($headers as $headerIndex => $header) {
                $data[$header] = isset($values[$headerIndex]) ? trim((string) $values[$headerIndex]) : null;
            }
            $data['row_number'] = $index + 2;
            return $data;
        })->filter(fn ($row) => collect($row)->except('row_number')->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())->values()->all();
    }

    private function buildImportPreview(array $rows, callable $validator): array
    {
        $previewRows = [];
        $valid = 0;
        $invalid = 0;

        foreach ($rows as $row) {
            [$errors, $notes] = $validator($row);
            $status = empty($errors) ? 'valid' : 'error';
            $status === 'valid' ? $valid++ : $invalid++;

            $previewRows[] = [
                'row_number' => $row['row_number'],
                'status' => $status,
                'data' => collect($row)->except('row_number')->all(),
                'errors' => $errors,
                'notes' => $notes,
            ];
        }

        return [
            'previewed_at' => now()->format('d/m/Y H:i:s'),
            'rows' => $previewRows,
            'total' => count($previewRows),
            'valid' => $valid,
            'invalid' => $invalid,
            'has_errors' => $invalid > 0,
        ];
    }

    private function downloadSpreadsheetTemplate(string $title, array $headers, array $samples, string $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($title);

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        foreach ($samples as $rowIndex => $sample) {
            foreach ($sample as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValueExplicit($column . ($rowIndex + 2), (string) $value, DataType::TYPE_STRING);
            }
        }

        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }

    private function downloadSpreadsheet(Spreadsheet $spreadsheet, string $fileName)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'customer-export-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()
            ->download($tempPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    private function parseBoolean(mixed $value, bool $default = false): bool
    {
        $text = mb_strtolower(trim((string) $value));
        if ($text === '') {
            return $default;
        }

        return in_array($text, ['1', 'y', 'ya', 'yes', 'true', 'aktif'], true);
    }

    private function nullableString(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text !== '' ? $text : null;
    }
}

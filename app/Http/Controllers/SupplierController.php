<?php

namespace App\Http\Controllers;

use App\Models\ProductSupplier;
use App\Models\Supplier;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index');
    }

    public function importForm()
    {
        return view('masters.import', [
            'title' => 'Import Supplier',
            'backRoute' => route('suppliers.index'),
            'templateRoute' => route('suppliers.import.template'),
            'previewRoute' => route('suppliers.import.preview'),
            'storeRoute' => route('suppliers.import.store'),
            'preview' => session('supplier_import_preview'),
            'templateNote' => 'Kolom wajib: nama. Kode supplier harus unik jika diisi.',
            'columns' => [
                'name' => 'Nama',
                'code' => 'Kode',
                'phone' => 'Telepon',
                'email' => 'Email',
                'contact_person' => 'PIC',
                'address' => 'Alamat',
                'notes' => 'Catatan',
                'is_active' => 'Aktif',
            ],
        ]);
    }

    public function downloadImportTemplate()
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->route('suppliers.import')->with('error', 'Package Excel belum terinstall.');
        }

        $headers = ['name', 'code', 'phone', 'email', 'contact_person', 'address', 'notes', 'is_active'];
        $samples = [
            ['Sinar Sparepart Gadget', 'SUP-SSG', '081234567890', 'ssg@example.com', 'Budi', 'Jakarta', 'Supplier utama baterai', 'ya'],
            ['Mitra LCD Center', 'SUP-LCD', '081298765432', 'lcd@example.com', 'Andi', 'Bandung', 'Fokus LCD dan touchscreen', 'ya'],
        ];

        return $this->downloadSpreadsheetTemplate('Import Supplier', $headers, $samples, 'template-import-supplier.xlsx');
    }

    public function previewImport(Request $request)
    {
        if (! class_exists(IOFactory::class)) {
            return redirect()->route('suppliers.import')->with('error', 'Package Excel belum terinstall.');
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
                $code = Str::upper(trim((string) ($data['code'] ?? '')));
                $email = trim((string) ($data['email'] ?? ''));

                if ($name === '') {
                    $errors[] = 'Nama supplier wajib diisi.';
                } elseif (Supplier::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
                    $errors[] = 'Nama supplier sudah ada.';
                }

                if ($code !== '' && Supplier::whereRaw('UPPER(code) = ?', [$code])->exists()) {
                    $errors[] = 'Kode supplier sudah ada.';
                }

                if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Format email tidak valid.';
                }

                if ($email !== '' && Supplier::whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->exists()) {
                    $errors[] = 'Email supplier sudah ada.';
                }

                if (($data['is_active'] ?? '') === '') {
                    $notes[] = 'Status aktif kosong, default ke aktif.';
                }

                return [$errors, $notes];
            }
        );

        session(['supplier_import_preview' => $preview]);

        return redirect()->route('suppliers.import');
    }

    public function storeImport()
    {
        $preview = session('supplier_import_preview');

        if (! $preview || empty($preview['rows'])) {
            return redirect()->route('suppliers.import')->with('error', 'Preview import tidak ditemukan.');
        }

        if (! empty($preview['has_errors'])) {
            return redirect()->route('suppliers.import')->with('error', 'Import belum bisa diproses karena masih ada baris error.');
        }

        $count = 0;
        foreach ($preview['rows'] as $row) {
            $data = $row['data'];
            Supplier::create([
                'name' => trim((string) $data['name']),
                'code' => filled($data['code'] ?? null) ? Str::upper(trim((string) $data['code'])) : null,
                'slug' => $this->generateUniqueSlug(trim((string) $data['name'])),
                'phone' => $this->nullableString($data['phone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'contact_person' => $this->nullableString($data['contact_person'] ?? null),
                'address' => $this->nullableString($data['address'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => $this->parseBoolean($data['is_active'] ?? 'ya', true),
            ]);
            $count++;
        }

        session()->forget('supplier_import_preview');

        UserLog::log('IMPORT_SUPPLIERS', 'Imported suppliers from Excel template', null, null, ['count' => $count]);

        return redirect()->route('suppliers.index')->with('success', $count . ' supplier berhasil diimport.');
    }

    public function export(Request $request)
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->route('suppliers.index')->with('error', 'Package Excel belum terinstall.');
        }

        $validated = $request->validate([
            'scope' => ['nullable', Rule::in(['selected', 'all'])],
            'selected_ids' => 'nullable|string',
        ]);

        $query = Supplier::query()->orderBy('name');

        if (($validated['scope'] ?? 'all') === 'selected') {
            $ids = collect(explode(',', (string) ($validated['selected_ids'] ?? '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                return redirect()->route('suppliers.index')->with('error', 'Pilih minimal 1 supplier untuk export.');
            }

            $query->whereIn('id', $ids);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return redirect()->route('suppliers.index')->with('error', 'Tidak ada supplier untuk diexport.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Supplier');
        $headers = ['Nama', 'Kode', 'Telepon', 'Email', 'PIC', 'Alamat', 'Catatan', 'Status'];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;
            $values = [
                $row->name,
                $row->code,
                $row->phone,
                $row->email,
                $row->contact_person,
                $row->address,
                $row->notes,
                $row->is_active ? 'Aktif' : 'Nonaktif',
            ];

            foreach ($values as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValueExplicit($column . $rowNumber, (string) $value, DataType::TYPE_STRING);
            }
        }

        return $this->downloadSpreadsheet($spreadsheet, 'supplier-export.xlsx');
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'code' => 'nullable|string|max:50|unique:suppliers,code',
            'slug' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'code' => filled($validated['code'] ?? null) ? Str::upper($validated['code']) : null,
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_SUPPLIER', "Created supplier: {$supplier->name}", null, null, $supplier->only(['name', 'code', 'phone', 'email', 'is_active']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Supplier berhasil ditambahkan.',
                'supplier' => $supplier->only(['id', 'name', 'code', 'phone', 'email', 'contact_person', 'is_active']),
            ], 201);
        }

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('suppliers', 'name')->ignore($supplier->id)],
            'code' => ['nullable', 'string', 'max:50', Rule::unique('suppliers', 'code')->ignore($supplier->id)],
            'slug' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $supplier->only(['name', 'code', 'phone', 'email', 'contact_person', 'is_active']);

        $supplier->update([
            'name' => $validated['name'],
            'code' => filled($validated['code'] ?? null) ? Str::upper($validated['code']) : null,
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $supplier->id),
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'address' => $validated['address'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_SUPPLIER', "Updated supplier: {$supplier->name}", null, $oldValues, $supplier->fresh()->only(['name', 'code', 'phone', 'email', 'contact_person', 'is_active']));

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->productSuppliers()->exists()) {
            return redirect()->route('suppliers.index')->with('error', 'Supplier tidak dapat dihapus karena masih terhubung ke produk.');
        }

        UserLog::log('DELETE_SUPPLIER', "Deleted supplier: {$supplier->name}", null, $supplier->only(['name', 'code', 'phone', 'email', 'contact_person', 'is_active']));
        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    public function getData()
    {
        $rows = Supplier::query()
            ->withCount('productSuppliers')
            ->select(['id', 'name', 'code', 'phone', 'contact_person', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('select_checkbox', fn ($row) => '<input type="checkbox" class="form-check-input row-export-checkbox" value="' . e($row->id) . '">')
            ->addColumn('product_suppliers_count', fn ($row) => $row->product_suppliers_count ?? 0)
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.suppliers.edit') || auth()->user()->hasPermission('master.products.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('suppliers.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.suppliers.delete') || auth()->user()->hasPermission('master.products.delete')) {
                    $actions .= '<form action="' . route('suppliers.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus supplier ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }
                return $actions . '</div></div>';
            })
            ->rawColumns(['select_checkbox', 'status_badge', 'action'])
            ->make(true);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Supplier::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
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

        return collect($rows)
            ->map(function ($row, $index) use ($headers) {
                $values = array_values($row);
                $data = [];
                foreach ($headers as $headerIndex => $header) {
                    $data[$header] = isset($values[$headerIndex]) ? trim((string) $values[$headerIndex]) : null;
                }
                $data['row_number'] = $index + 2;
                return $data;
            })
            ->filter(fn ($row) => collect($row)->except('row_number')->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
            ->values()
            ->all();
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
        $tempPath = tempnam(sys_get_temp_dir(), 'supplier-export-') . '.xlsx';
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

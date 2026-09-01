<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerGroupController extends Controller
{
    public function index()
    {
        return view('customer-groups.index');
    }

    public function importForm()
    {
        return view('masters.import', [
            'title' => 'Import Customer Group',
            'backRoute' => route('customer-groups.index'),
            'templateRoute' => route('customer-groups.import.template'),
            'previewRoute' => route('customer-groups.import.preview'),
            'storeRoute' => route('customer-groups.import.store'),
            'preview' => session('customer_group_import_preview'),
            'templateNote' => 'Kolom wajib: name. Nama group tidak boleh duplikat.',
            'columns' => [
                'name' => 'Nama',
                'sort_order' => 'Urutan',
                'is_active' => 'Aktif',
            ],
        ]);
    }

    public function downloadImportTemplate()
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->route('customer-groups.import')->with('error', 'Package Excel belum terinstall.');
        }

        return $this->downloadSpreadsheetTemplate(
            'Import Customer Group',
            ['name', 'sort_order', 'is_active'],
            [
                ['Retail', '1', 'ya'],
                ['Member Gold', '2', 'ya'],
            ],
            'template-import-customer-group.xlsx'
        );
    }

    public function previewImport(Request $request)
    {
        if (! class_exists(IOFactory::class)) {
            return redirect()->route('customer-groups.import')->with('error', 'Package Excel belum terinstall.');
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
                $sortOrder = trim((string) ($data['sort_order'] ?? ''));

                if ($name === '') {
                    $errors[] = 'Nama group wajib diisi.';
                } elseif (CustomerGroup::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
                    $errors[] = 'Nama group sudah ada.';
                }

                if ($sortOrder !== '' && filter_var($sortOrder, FILTER_VALIDATE_INT) === false) {
                    $errors[] = 'Urutan harus berupa angka bulat.';
                }

                if (($data['is_active'] ?? '') === '') {
                    $notes[] = 'Status aktif kosong, default ke aktif.';
                }

                return [$errors, $notes];
            }
        );

        session(['customer_group_import_preview' => $preview]);

        return redirect()->route('customer-groups.import');
    }

    public function storeImport()
    {
        $preview = session('customer_group_import_preview');

        if (! $preview || empty($preview['rows'])) {
            return redirect()->route('customer-groups.import')->with('error', 'Preview import tidak ditemukan.');
        }

        if (! empty($preview['has_errors'])) {
            return redirect()->route('customer-groups.import')->with('error', 'Import belum bisa diproses karena masih ada baris error.');
        }

        $count = 0;
        foreach ($preview['rows'] as $row) {
            $data = $row['data'];
            CustomerGroup::create([
                'name' => trim((string) $data['name']),
                'slug' => $this->generateUniqueSlug(trim((string) $data['name'])),
                'sort_order' => (int) ($data['sort_order'] !== '' ? $data['sort_order'] : 0),
                'is_active' => $this->parseBoolean($data['is_active'] ?? 'ya', true),
            ]);
            $count++;
        }

        session()->forget('customer_group_import_preview');

        UserLog::log('IMPORT_CUSTOMER_GROUPS', 'Imported customer groups from Excel template', null, null, ['count' => $count]);

        return redirect()->route('customer-groups.index')->with('success', $count . ' customer group berhasil diimport.');
    }

    public function export(Request $request)
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()->route('customer-groups.index')->with('error', 'Package Excel belum terinstall.');
        }

        $validated = $request->validate([
            'scope' => ['nullable', Rule::in(['selected', 'all'])],
            'selected_ids' => 'nullable|string',
        ]);

        $query = CustomerGroup::query()->orderBy('sort_order')->orderBy('name');

        if (($validated['scope'] ?? 'all') === 'selected') {
            $ids = collect(explode(',', (string) ($validated['selected_ids'] ?? '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                return redirect()->route('customer-groups.index')->with('error', 'Pilih minimal 1 customer group untuk export.');
            }

            $query->whereIn('id', $ids);
        }

        $rows = $query->get();
        if ($rows->isEmpty()) {
            return redirect()->route('customer-groups.index')->with('error', 'Tidak ada customer group untuk diexport.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customer Group');
        $headers = ['Nama', 'Slug', 'Urutan', 'Status'];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        foreach ($rows as $rowIndex => $row) {
            $values = [$row->name, $row->slug, (string) $row->sort_order, $row->is_active ? 'Aktif' : 'Nonaktif'];
            foreach ($values as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $sheet->setCellValueExplicit($column . ($rowIndex + 2), (string) $value, DataType::TYPE_STRING);
            }
        }

        return $this->downloadSpreadsheet($spreadsheet, 'customer-group-export.xlsx');
    }

    public function create()
    {
        return view('customer-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customer_groups,name',
            'slug' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $group = CustomerGroup::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_CUSTOMER_GROUP', "Created customer group: {$group->name}", null, null, $group->only(['name', 'slug', 'sort_order', 'is_active']));
        return redirect()->route('customer-groups.index')->with('success', 'Customer group berhasil ditambahkan.');
    }

    public function edit(CustomerGroup $customerGroup)
    {
        return view('customer-groups.edit', compact('customerGroup'));
    }

    public function update(Request $request, CustomerGroup $customerGroup)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('customer_groups', 'name')->ignore($customerGroup->id)],
            'slug' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $customerGroup->only(['name', 'slug', 'sort_order', 'is_active']);
        $customerGroup->update([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $customerGroup->id),
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_CUSTOMER_GROUP', "Updated customer group: {$customerGroup->name}", null, $oldValues, $customerGroup->fresh()->only(['name', 'slug', 'sort_order', 'is_active']));
        return redirect()->route('customer-groups.index')->with('success', 'Customer group berhasil diperbarui.');
    }

    public function destroy(CustomerGroup $customerGroup)
    {
        if (\App\Models\ProductCustomerGroupPrice::where('customer_group_id', $customerGroup->id)->exists()) {
            return redirect()->route('customer-groups.index')->with('error', 'Customer group tidak dapat dihapus karena sudah dipakai pricing produk.');
        }

        UserLog::log('DELETE_CUSTOMER_GROUP', "Deleted customer group: {$customerGroup->name}", null, $customerGroup->only(['name', 'slug', 'sort_order', 'is_active']));
        $customerGroup->delete();

        return redirect()->route('customer-groups.index')->with('success', 'Customer group berhasil dihapus.');
    }

    public function getData()
    {
        $rows = CustomerGroup::query()->select(['id', 'name', 'slug', 'sort_order', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('select_checkbox', fn ($row) => '<input type="checkbox" class="form-check-input row-export-checkbox" value="' . e($row->id) . '">')
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.customer_groups.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('customer-groups.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.customer_groups.delete')) {
                    $actions .= '<form action="' . route('customer-groups.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus customer group ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
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
        while (CustomerGroup::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
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
        $tempPath = tempnam(sys_get_temp_dir(), 'customer-group-export-') . '.xlsx';
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
}

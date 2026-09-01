<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductCustomerGroupPrice;
use App\Models\ProductImage;
use App\Models\ProductMaker;
use App\Models\ProductPriceTier;
use App\Models\ProductQuality;
use App\Models\ProductStock;
use App\Models\ProductSupplier;
use App\Models\ProductType;
use App\Models\ProductUnit;
use App\Models\ProductVariant;
use App\Models\SubCategory;
use App\Models\Supplier;
use App\Models\TablePreference;
use App\Models\Unit;
use App\Models\UserLog;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductController extends Controller
{
    public function index()
    {
        $tablePreference = Schema::hasTable('table_preferences')
            ? TablePreference::where('user_id', auth()->id())->where('table_key', 'products')->first()
            : null;

        return view('products.index', [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'subCategories' => SubCategory::where('is_active', true)->orderBy('name')->get(['id', 'category_id', 'name']),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'productMakers' => ProductMaker::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'productTypes' => ProductType::where('is_active', true)->orderBy('name')->get(['id', 'brand_id', 'name']),
            'tablePreference' => $tablePreference,
        ]);
    }

    public function create()
    {
        return view('products.create', $this->getProductFormData());
    }

    public function importForm()
    {
        return view('products.import', [
            'preview' => session('product_import_preview'),
        ]);
    }

    public function downloadImportTemplate()
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()
                ->route('products.import')
                ->with('error', 'Package Excel belum terinstall. Jalankan composer require phpoffice/phpspreadsheet:^2.2 terlebih dahulu.');
        }

        $headers = [
            'product_code',
            'nama',
            'kategori',
            'sub_kategori',
            'brand',
            'merek',
            'tipe_hp',
            'supplier',
            'lokasi_default',
            'quality',
            'barcode',
            'sku_internal',
            'satuan_beli',
            'satuan_jual',
            'harga_beli',
            'harga_jual',
            'stok_awal',
            'stok_min',
            'stok_max',
            'rak',
            'aktif',
            'member_only',
        ];

        $samples = [
            [
                '',
                'Baterai Xiaomi Redmi Note 10',
                'Baterai',
                'Baterai Original Grade',
                'Xiaomi',
                'BPE',
                'Redmi Note 10|Redmi Note 10 Pro',
                'Sinar Sparepart Gadget',
                'Gudang',
                'Original',
                '899100000001',
                '',
                'PCS',
                'PCS',
                '65000',
                '95000',
                '10',
                '3',
                '50',
                'Rak B-01',
                'ya',
                'tidak',
            ],
            [
                '',
                'LCD Oppo A57',
                'LCD',
                'LCD Incell',
                'Oppo',
                'Vizz',
                'A57',
                'Mitra Sparepart HP',
                'Toko',
                'Aftermarket',
                '899100000002',
                '',
                'PCS',
                'PCS',
                '120000',
                '180000',
                '5',
                '2',
                '25',
                'Etalase L-02',
                'ya',
                'ya',
            ],
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Produk');

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

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:T1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFEAF1FF');

        $tempPath = tempnam(sys_get_temp_dir(), 'template-import-produk-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);

        return response()
            ->download($tempPath, 'template-import-produk.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function previewImport(Request $request)
    {
        if (! class_exists(IOFactory::class)) {
            return redirect()
                ->route('products.import')
                ->with('error', 'Package Excel belum terinstall. Jalankan composer require phpoffice/phpspreadsheet:^2.2 terlebih dahulu.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $rows = $this->parseProductImportExcel($validated['file']->getRealPath());
        $preview = $this->buildProductImportPreview($rows);

        session(['product_import_preview' => $preview]);

        return redirect()->route('products.import');
    }

    public function updateImportPreview(Request $request)
    {
        $currentPreview = session('product_import_preview');

        if (! $currentPreview || empty($currentPreview['rows'])) {
            return redirect()->route('products.import')->with('error', 'Preview import tidak ditemukan. Upload file terlebih dahulu.');
        }

        $validated = $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*.row_number' => 'required|integer|min:2',
            'rows.*.data' => 'required|array',
        ]);

        $rows = collect($validated['rows'])
            ->map(function (array $row) {
                return [
                    'row_number' => (int) $row['row_number'],
                    'data' => $this->normalizeProductImportRow($row['data'] ?? []),
                ];
            })
            ->values()
            ->all();

        $preview = $this->buildProductImportPreview($rows);
        session(['product_import_preview' => $preview]);

        return redirect()
            ->route('products.import')
            ->with('success', 'Preview import berhasil diperbarui.');
    }

    public function storeImport()
    {
        $preview = session('product_import_preview');

        if (! $preview || empty($preview['rows'])) {
            return redirect()->route('products.import')->with('error', 'Preview import tidak ditemukan. Upload file terlebih dahulu.');
        }

        if (! empty($preview['has_errors'])) {
            return redirect()->route('products.import')->with('error', 'Import belum bisa diproses karena masih ada baris error.');
        }

        $importedProducts = DB::transaction(function () use ($preview) {
            $products = [];

            foreach ($preview['rows'] as $row) {
                $products[] = $this->createProductFromImportRow($row['data']);
            }

            return $products;
        });

        session()->forget('product_import_preview');

        UserLog::log(
            'IMPORT_PRODUCTS',
            'Imported products from Excel template',
            null,
            null,
            ['count' => count($importedProducts)]
        );

        return redirect()
            ->route('products.index')
            ->with('success', count($importedProducts) . ' produk berhasil diimport.');
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        if (((float) ($validated['stock_global'] ?? 0) > 0 || (float) ($validated['damaged_stock'] ?? 0) > 0) && empty($validated['default_location_id'])) {
            return back()
                ->withErrors(['default_location_id' => 'Lokasi default wajib diisi jika stok awal diisi.'])
                ->withInput();
        }

        $product = DB::transaction(function () use ($request, $validated) {
            $product = Product::create($this->buildProductPayload($validated));

            $this->syncProductRelations($request, $product);
            $this->initializeOpeningStock($product, (float) ($validated['stock_global'] ?? 0), (float) ($validated['damaged_stock'] ?? 0));

            return $product->fresh([
                'category',
                'subCategory',
                'brand',
                'maker',
                'defaultRack',
                'productTypes',
                'barcodes',
                'units',
                'images',
                'priceTiers',
                'customerGroupPrices',
                'variants',
                'productSuppliers.supplier',
            ]);
        });

        UserLog::log(
            'CREATE_PRODUCT',
            "Created product: {$product->name} ({$product->product_code})",
            null,
            null,
            $this->loggableProductState($product)
        );

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $product->load([
            'productTypes',
            'barcodes',
            'units',
            'images',
            'priceTiers',
            'customerGroupPrices',
            'variants',
            'productSuppliers.supplier',
        ]);

        return view('products.edit', array_merge(
            $this->getProductFormData(),
            ['product' => $product]
        ));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product);
        $product->load([
            'productTypes',
            'barcodes',
            'units',
            'images',
            'priceTiers',
            'customerGroupPrices',
            'variants',
            'productSuppliers.supplier',
        ]);
        $oldValues = $this->loggableProductState($product);

        DB::transaction(function () use ($request, $validated, $product) {
            $product->update($this->buildProductPayload($validated, $product));
            $this->syncProductRelations($request, $product);
        });

        $product->refresh()->load([
            'category',
            'subCategory',
            'brand',
            'maker',
            'defaultRack',
            'productTypes',
            'barcodes',
            'units',
            'images',
            'priceTiers',
            'customerGroupPrices',
            'variants',
            'productSuppliers.supplier',
        ]);

        UserLog::log(
            'UPDATE_PRODUCT',
            "Updated product: {$product->name} ({$product->product_code})",
            null,
            $oldValues,
            $this->loggableProductState($product)
        );

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->load(['images']);
        $oldValues = $this->loggableProductState($product);

        DB::transaction(function () use ($product) {
            ProductStock::where('product_id', $product->id)->delete();

            foreach ($product->images as $image) {
                $this->deleteProductImage($image->image_path);
            }

            $product->delete();
        });

        UserLog::log(
            'DELETE_PRODUCT',
            "Deleted product: {$product->name} ({$product->product_code})",
            null,
            $oldValues
        );

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function getData(Request $request)
    {
        $products = Product::query()
            ->with(['category', 'subCategory', 'brand', 'maker', 'productTypes', 'barcodes', 'images', 'stocks.location', 'stocks.rack'])
            ->select([
                'id',
                'product_code',
                'category_id',
                'sub_category_id',
                'brand_id',
                'product_maker_id',
                'name',
                'sku',
                'selling_price',
                'stock_global',
                'sale_unit',
                'is_published',
                'is_active',
                'legacy_image_path',
            ]);

        $searchTerm = trim((string) $request->input('search.value', ''));

        $this->applyProductFilters($products, $request);

        return datatables()->of($products)
            ->addColumn('select_checkbox', fn ($product) => '<input type="checkbox" class="form-check-input product-export-checkbox" value="' . e($product->id) . '">')
            ->addColumn('image_preview', function ($product) {
                $url = $product->primaryImageUrl();

                if (! $url) {
                    return '<span class="badge bg-label-secondary">No Photo</span>';
                }

                return '<img src="' . $url . '" alt="' . e($product->name) . '" class="rounded" style="width:48px;height:48px;object-fit:cover;">';
            })
            ->addColumn('product_code_action', fn ($product) => $this->productQuickActionDropdown($product, e($product->product_code), 'font-monospace'))
            ->addColumn('name_action', fn ($product) => $this->productQuickActionDropdown($product, e($product->name), 'fw-semibold text-body'))
            ->addColumn('category_label', fn ($product) => collect([$product->category?->name, $product->subCategory?->name])->filter()->join(' / ') ?: '-')
            ->addColumn('brand_label', fn ($product) => $product->brand?->name ?: '-')
            ->addColumn('product_types_label', fn ($product) => $this->formatProductTypesLabel($product, $searchTerm))
            ->addColumn('maker_label', fn ($product) => $product->maker?->name ?: '-')
            ->addColumn('barcode_label', fn ($product) => $product->primaryBarcode() ?: '-')
            ->addColumn('selling_price_formatted', fn ($product) => 'Rp ' . number_format((float) $product->selling_price, 0, ',', '.'))
            ->addColumn('stock_label', fn ($product) => $product->stockSummary())
            ->addColumn('status_badge', function ($product) {
                return '<span class="badge bg-' . ($product->is_active ? 'success' : 'secondary') . '">' . ($product->is_active ? 'Aktif' : 'Nonaktif') . '</span>';
            })
            ->addColumn('action', function ($product) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                if (auth()->user()->hasPermission('master.products.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('products.edit', $product->id) . '">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>';
                }

                if (auth()->user()->hasPermission('master.product_stocks.view')) {
                    $actions .= '<a class="dropdown-item" href="' . route('products.stocks.index', $product->id) . '">
                        <i class="bx bx-layer me-1"></i> Stok Lokasi
                    </a>';
                }

                if (auth()->user()->hasPermission('master.products.delete')) {
                    $actions .= '<form action="' . route('products.destroy', $product->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus produk ini?\')">
                            <i class="bx bx-trash me-1"></i> Hapus
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['select_checkbox', 'image_preview', 'product_code_action', 'name_action', 'product_types_label', 'stock_label', 'status_badge', 'action'])
            ->make(true);
    }

    public function shareText(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer',
        ]);

        $orderedIds = collect($validated['product_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $products = Product::query()
            ->with(['brand:id,name', 'maker:id,name', 'productTypes:id,name'])
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');

        $lines = $orderedIds
            ->map(fn ($id) => $products->get($id))
            ->filter()
            ->map(fn (Product $product) => $this->formatProductShareLine($product))
            ->values();

        return response()->json([
            'text' => $lines->implode("\n"),
            'count' => $lines->count(),
        ]);
    }

    private function formatProductTypesLabel(Product $product, string $searchTerm, int $limit = 2): string
    {
        $items = $product->productTypes?->pluck('name')->filter()->values()->all() ?? [];

        if (count($items) === 0) {
            return '-';
        }

        $term = trim($searchTerm);

        // When searching, show full list (so the matched item is visible).
        if ($term !== '') {
            return $this->highlightText(e(implode(', ', $items)), $term);
        }

        if (count($items) <= $limit) {
            return e(implode(', ', $items));
        }

        $shown = array_slice($items, 0, $limit);
        $hidden = array_slice($items, $limit);
        $hiddenCount = count($hidden);
        $hiddenText = implode(', ', $hidden);

        return e(implode(', ', $shown)) .
            ' <span class="text-muted" title="' . e($hiddenText) . '">+' . $hiddenCount . '</span>';
    }

    private function formatProductShareLine(Product $product): string
    {
        $segments = [trim((string) $product->name)];
        $existing = strtoupper(implode(' ', $segments));

        $appendUnique = function (?string $value) use (&$segments, &$existing): void {
            $value = trim((string) $value);

            if ($value === '') {
                return;
            }

            $upper = strtoupper($value);
            if (str_contains($existing, $upper)) {
                return;
            }

            $segments[] = $value;
            $existing .= ' ' . $upper;
        };

        $appendUnique($product->brand?->name);

        $types = $product->productTypes
            ?->pluck('name')
            ->filter()
            ->unique(fn ($name) => strtoupper((string) $name))
            ->values()
            ->implode(' / ');
        $appendUnique($types);

        $appendUnique($product->maker?->name);
        $appendUnique($product->quality);

        return strtoupper(trim(implode(' ', array_filter($segments)))) .
            ' - Rp ' . number_format((float) $product->selling_price, 0, ',', '.');
    }

    private function highlightText(string $escapedText, string $term): string
    {
        $needle = preg_quote($term, '/');

        return preg_replace_callback(
            '/' . $needle . '/iu',
            fn ($m) => '<mark class="p-0 bg-warning bg-opacity-25">' . $m[0] . '</mark>',
            $escapedText
        ) ?? $escapedText;
    }

    public function export(Request $request)
    {
        if (! class_exists(Spreadsheet::class)) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Package Excel belum terinstall. Jalankan composer require phpoffice/phpspreadsheet:^2.2 terlebih dahulu.');
        }

        $validated = $request->validate([
            'scope' => ['required', Rule::in(['selected', 'all'])],
            'selected_product_ids' => 'nullable|string',
            'category_id' => 'nullable|array',
            'category_id.*' => 'integer',
            'sub_category_id' => 'nullable|array',
            'sub_category_id.*' => 'integer',
            'brand_id' => 'nullable|array',
            'brand_id.*' => 'integer',
            'product_maker_id' => 'nullable|array',
            'product_maker_id.*' => 'integer',
            'product_type_id' => 'nullable|array',
            'product_type_id.*' => 'integer',
        ]);

        $query = Product::query()
            ->with(['category', 'subCategory', 'brand', 'maker', 'productTypes', 'barcodes', 'stocks.location', 'productSuppliers.supplier'])
            ->orderBy('name');

        if ($validated['scope'] === 'selected') {
            $ids = collect(explode(',', (string) ($validated['selected_product_ids'] ?? '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                return redirect()->route('products.index')->with('error', 'Pilih minimal 1 produk untuk export.');
            }

            $query->whereIn('id', $ids);
        } else {
            $this->applyProductFilters($query, $request);
        }

        $products = $query->get();

        if ($products->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Tidak ada produk untuk diexport.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produk');

        $headers = [
            'Kode Produk',
            'Nama',
            'Kategori',
            'Sub Kategori',
            'Brand',
             'Merek',
             'Quality',
             'Tipe HP',
            'Supplier Utama',
            'Barcode Utama',
            'SKU Internal',
            'Satuan Beli',
            'Satuan Jual',
            'Harga Beli',
            'Harga Jual',
            'Stok Global',
            'Stok Minimum',
            'Stok Maksimum',
            'Lokasi Stok',
            'Rak',
             'Status',
             'Member Only',
         ];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        foreach ($products as $rowIndex => $product) {
            $rowNumber = $rowIndex + 2;
            $primarySupplier = $product->productSuppliers->firstWhere('is_primary', true) ?: $product->productSuppliers->first();
            $stockLocations = $product->stocks
                ->map(function ($stock) {
                    $quantity = rtrim(rtrim(number_format((float) $stock->quantity, 2, '.', ''), '0'), '.');

                    return ($stock->location?->name ?: '-') . ': ' . $quantity;
                })
                ->implode('; ');

            $values = [
                $product->product_code,
                $product->name,
                $product->category?->name,
                 $product->subCategory?->name,
                 $product->brand?->name,
                 $product->maker?->name,
                 $product->quality,
                 $product->productTypeNames(),
                $primarySupplier?->supplier?->name,
                $product->primaryBarcode(),
                $product->sku,
                $product->buy_unit,
                $product->sale_unit,
                $product->purchase_price,
                $product->selling_price,
                $product->stock_global,
                $product->stock_min,
                $product->stock_max,
                $stockLocations,
                 $product->rack_location,
                 $product->is_active ? 'Aktif' : 'Nonaktif',
                 $product->is_member_only ? 'Ya' : 'Tidak',
             ];

            foreach ($values as $columnIndex => $value) {
                $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
                $cell = $column . $rowNumber;

                if (in_array($columnIndex, [13, 14, 15, 16, 17], true) && $value !== null) {
                    $sheet->setCellValue($cell, (float) $value);
                    continue;
                }

                $sheet->setCellValueExplicit($cell, (string) ($value ?? ''), DataType::TYPE_STRING);
            }
        }

        $sheet->freezePane('A2');
        $sheet->getStyle('A1:V1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFEAF1FF');

        $tempPath = tempnam(sys_get_temp_dir(), 'export-produk-') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tempPath);
        $spreadsheet->disconnectWorksheets();

        return response()
            ->download($tempPath, 'export-produk-' . now()->format('Ymd-His') . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function printBarcodes(Request $request)
    {
        $validated = $request->validate([
            'scope' => ['required', Rule::in(['selected', 'all'])],
            'selected_product_ids' => 'nullable|string',
            'barcode_scope' => ['required', Rule::in(['primary', 'all'])],
            'size_mode' => ['required', Rule::in(['preset', 'custom'])],
            'size_preset' => ['nullable', Rule::in(['50x25', '58x30', '80x38'])],
            'custom_width_mm' => 'nullable|numeric|min:20|max:150',
            'custom_height_mm' => 'nullable|numeric|min:15|max:100',
            'category_id' => 'nullable|array',
            'category_id.*' => 'integer',
            'sub_category_id' => 'nullable|array',
            'sub_category_id.*' => 'integer',
            'brand_id' => 'nullable|array',
            'brand_id.*' => 'integer',
            'product_maker_id' => 'nullable|array',
            'product_maker_id.*' => 'integer',
            'product_type_id' => 'nullable|array',
            'product_type_id.*' => 'integer',
        ]);

        $query = Product::query()
            ->with(['barcodes', 'units'])
            ->orderBy('name');

        if ($validated['scope'] === 'selected') {
            $ids = collect(explode(',', (string) ($validated['selected_product_ids'] ?? '')))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                return redirect()->route('products.index')->with('error', 'Pilih minimal 1 produk untuk cetak barcode.');
            }

            $query->whereIn('id', $ids);
        } else {
            $this->applyProductFilters($query, $request);
        }

        $products = $query->get(['id', 'product_code', 'name', 'barcode']);
        $items = $this->collectBarcodePrintItems($products, $validated['barcode_scope']);

        if ($items->isEmpty()) {
            return redirect()->route('products.index')->with('error', 'Tidak ada barcode yang bisa dicetak dari produk yang dipilih.');
        }

        [$widthMm, $heightMm, $sizeLabel] = $this->resolveBarcodePrintSize($validated);

        return view('products.barcodes.print', [
            'items' => $items,
            'barcodeScope' => $validated['barcode_scope'],
            'widthMm' => $widthMm,
            'heightMm' => $heightMm,
            'sizeLabel' => $sizeLabel,
        ]);
    }

    private function productQuickActionDropdown(Product $product, string $label, string $labelClass = ''): string
    {
        $items = '';

        if (auth()->user()->hasPermission('master.products.edit')) {
            $items .= '<a class="dropdown-item" href="' . route('products.edit', $product->id) . '">
                <i class="bx bx-edit-alt me-1"></i> Edit
            </a>';
        }

        if (auth()->user()->hasPermission('master.product_stocks.view')) {
            $items .= '<a class="dropdown-item" href="' . route('products.stocks.index', $product->id) . '">
                <i class="bx bx-layer me-1"></i> Stok Lokasi
            </a>';
        }

        if ($items === '') {
            return '<span class="' . $labelClass . '">' . $label . '</span>';
        }

        return '<div class="dropdown d-inline-block product-cell-action">
            <a href="javascript:void(0);" class="' . $labelClass . ' text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                ' . $label . '
            </a>
            <div class="dropdown-menu">' . $items . '</div>
        </div>';
    }

    private function collectBarcodePrintItems($products, string $barcodeScope)
    {
        return $products->flatMap(function (Product $product) use ($barcodeScope) {
            if ($barcodeScope === 'primary') {
                $barcode = $product->primaryBarcode();

                if (! $barcode) {
                    return [];
                }

                return [[
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'barcode' => $barcode,
                    'source' => 'primary',
                ]];
            }

            $items = collect();
            $seen = [];

            foreach ($product->barcodes as $barcodeRow) {
                $barcode = trim((string) $barcodeRow->barcode);
                if ($barcode === '' || isset($seen[$barcode])) {
                    continue;
                }

                $seen[$barcode] = true;
                $items->push([
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'barcode' => $barcode,
                    'source' => $barcodeRow->label ?: 'barcode',
                ]);
            }

            foreach ($product->units as $unitRow) {
                $barcode = trim((string) ($unitRow->barcode ?? ''));
                if ($barcode === '' || isset($seen[$barcode])) {
                    continue;
                }

                $seen[$barcode] = true;
                $items->push([
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'barcode' => $barcode,
                    'source' => $unitRow->unit_name ?: 'satuan',
                ]);
            }

            if ($items->isEmpty() && $product->barcode) {
                $items->push([
                    'product_id' => $product->id,
                    'product_code' => $product->product_code,
                    'product_name' => $product->name,
                    'barcode' => $product->barcode,
                    'source' => 'barcode',
                ]);
            }

            return $items->values()->all();
        })->values();
    }

    private function resolveBarcodePrintSize(array $validated): array
    {
        $presets = [
            '50x25' => [50, 25, '50 x 25 mm'],
            '58x30' => [58, 30, '58 x 30 mm'],
            '80x38' => [80, 38, '80 x 38 mm'],
        ];

        if (($validated['size_mode'] ?? 'preset') === 'custom') {
            $width = (float) ($validated['custom_width_mm'] ?? 50);
            $height = (float) ($validated['custom_height_mm'] ?? 25);

            return [$width, $height, rtrim(rtrim(number_format($width, 2, '.', ''), '0'), '.') . ' x ' . rtrim(rtrim(number_format($height, 2, '.', ''), '0'), '.') . ' mm'];
        }

        $preset = $validated['size_preset'] ?? '50x25';

        return $presets[$preset] ?? $presets['50x25'];
    }

    private function applyProductFilters($query, Request $request): void
    {
        $categoryIds = array_filter((array) $request->input('category_id', []));
        $subCategoryIds = array_filter((array) $request->input('sub_category_id', []));
        $brandIds = array_filter((array) $request->input('brand_id', []));
        $makerIds = array_filter((array) $request->input('product_maker_id', []));
        $productTypeIds = array_filter((array) $request->input('product_type_id', []));

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        if ($subCategoryIds) {
            $query->whereIn('sub_category_id', $subCategoryIds);
        }

        if ($brandIds) {
            $query->whereIn('brand_id', $brandIds);
        }

        if ($makerIds) {
            $query->whereIn('product_maker_id', $makerIds);
        }

        if ($productTypeIds) {
            $query->whereHas('productTypes', fn ($filterQuery) => $filterQuery->whereIn('product_types.id', $productTypeIds));
        }
    }

    public function getSubCategories(Category $category)
    {
        return response()->json(
            SubCategory::where('category_id', $category->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function getProductTypes(Brand $brand)
    {
        return response()->json(
            ProductType::where('brand_id', $brand->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    private function parseProductImportExcel(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $headers = [];
        $rows = [];

        for ($columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++) {
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $headers[$columnIndex] = $this->normalizeImportHeader($sheet->getCell($column . '1')->getFormattedValue());
        }

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $values = [];

            for ($columnIndex = 1; $columnIndex <= $highestColumnIndex; $columnIndex++) {
                $column = Coordinate::stringFromColumnIndex($columnIndex);
                $values[$columnIndex] = trim((string) $sheet->getCell($column . $rowNumber)->getFormattedValue());
            }

            if (collect($values)->every(fn ($value) => trim((string) $value) === '')) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                if ($header === '') {
                    continue;
                }

                $row[$header] = trim((string) ($values[$index] ?? ''));
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'data' => $this->normalizeProductImportRow($row),
            ];
        }

        $spreadsheet->disconnectWorksheets();

        return $rows;
    }

    private function normalizeImportHeader(?string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

        $normalized = Str::of((string) $header)
            ->lower()
            ->replace([' ', '-', '.', '/'], '_')
            ->replaceMatches('/_+/', '_')
            ->trim('_')
            ->toString();

        return match ($normalized) {
            'kode_produk', 'kode_barang' => 'product_code',
            'nama_produk', 'nama_barang', 'product_name' => 'nama',
            'category' => 'kategori',
            'subcategory', 'sub_category' => 'sub_kategori',
            'merk', 'maker', 'product_maker', 'merek_produk' => 'merek',
            'type_hp', 'tipe', 'tipe_handphone' => 'tipe_hp',
            'vendor' => 'supplier',
            'lokasi', 'lokasi_stok' => 'lokasi_default',
            'sku' => 'sku_internal',
            'grade', 'kualitas' => 'quality',
            'unit_beli' => 'satuan_beli',
            'unit_jual' => 'satuan_jual',
            'harga_modal', 'harga_pokok', 'purchase_price' => 'harga_beli',
            'harga', 'selling_price' => 'harga_jual',
            'stock_awal', 'stok', 'stock' => 'stok_awal',
            'minimal_stok', 'minimum_stock' => 'stok_min',
            'maksimal_stok', 'maximum_stock' => 'stok_max',
            'rack_location' => 'rak',
            'status' => 'aktif',
            'member' => 'member_only',
            default => $normalized,
        };
    }

    private function normalizeProductImportRow(array $row): array
    {
        return [
            'product_code' => Str::upper($row['product_code'] ?? ''),
            'nama' => $row['nama'] ?? '',
            'kategori' => $row['kategori'] ?? '',
            'sub_kategori' => $row['sub_kategori'] ?? '',
            'brand' => $row['brand'] ?? '',
            'merek' => $row['merek'] ?? '',
            'tipe_hp' => $row['tipe_hp'] ?? '',
            'supplier' => $row['supplier'] ?? '',
            'lokasi_default' => $row['lokasi_default'] ?? '',
            'quality' => $row['quality'] ?? '',
            'barcode' => $row['barcode'] ?? '',
            'sku_internal' => Str::upper($row['sku_internal'] ?? ''),
            'satuan_beli' => $this->normalizeUnitName($row['satuan_beli'] ?? 'PCS') ?: 'PCS',
            'satuan_jual' => $this->normalizeUnitName($row['satuan_jual'] ?? 'PCS') ?: 'PCS',
            'harga_beli' => $this->normalizeImportNumber($row['harga_beli'] ?? null),
            'harga_jual' => $this->normalizeImportNumber($row['harga_jual'] ?? null),
            'stok_awal' => $this->normalizeImportNumber($row['stok_awal'] ?? null),
            'stok_min' => $this->normalizeImportNumber($row['stok_min'] ?? null),
            'stok_max' => $this->normalizeImportNumber($row['stok_max'] ?? null),
            'rak' => $row['rak'] ?? '',
            'aktif' => $this->normalizeImportBoolean($row['aktif'] ?? 'ya'),
            'member_only' => $this->normalizeImportBoolean($row['member_only'] ?? 'tidak'),
        ];
    }

    private function normalizeImportNumber($value): ?float
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(['Rp', 'rp', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeImportBoolean($value): bool
    {
        return in_array(Str::lower(trim((string) $value)), ['1', 'true', 'ya', 'yes', 'aktif', 'active'], true);
    }

    private function buildProductImportPreview(array $rows): array
    {
        $previewRows = [];
        $hasErrors = false;
        $seenCodes = [];
        $seenSkus = [];
        $seenBarcodes = [];

        foreach ($rows as $row) {
            $data = $row['data'];
            $errors = [];
            $notes = [];

            if ($data['nama'] === '') {
                $errors[] = 'Nama wajib diisi.';
            }

            if ($data['kategori'] === '') {
                $errors[] = 'Kategori wajib diisi.';
            } elseif (! $this->categoryExistsByName($data['kategori'])) {
                $notes[] = 'Kategori baru akan dibuat.';
            }

            if ($data['harga_jual'] === null) {
                $errors[] = 'Harga jual wajib angka.';
            }

            if (($data['stok_awal'] ?? 0) > 0 && $data['lokasi_default'] === '') {
                $errors[] = 'Lokasi default wajib diisi jika stok awal lebih dari 0.';
            }

            if ($data['product_code'] !== '') {
                if (Product::where('product_code', $data['product_code'])->exists()) {
                    $errors[] = 'Kode produk sudah dipakai.';
                }

                if (in_array($data['product_code'], $seenCodes, true)) {
                    $errors[] = 'Kode produk duplikat di file.';
                }

                $seenCodes[] = $data['product_code'];
            }

            $effectiveSku = $data['sku_internal'] !== '' ? $data['sku_internal'] : $data['product_code'];

            if ($effectiveSku !== '') {
                if (Product::where('sku', $effectiveSku)->exists()) {
                    $errors[] = 'SKU internal sudah dipakai.';
                }

                if (in_array($effectiveSku, $seenSkus, true)) {
                    $errors[] = 'SKU internal duplikat di file.';
                }

                $seenSkus[] = $effectiveSku;
            }

            if ($data['barcode'] !== '') {
                if (Product::where('barcode', $data['barcode'])->exists() || ProductBarcode::where('barcode', $data['barcode'])->exists()) {
                    $errors[] = 'Barcode sudah dipakai.';
                }

                if (in_array($data['barcode'], $seenBarcodes, true)) {
                    $errors[] = 'Barcode duplikat di file.';
                }

                $seenBarcodes[] = $data['barcode'];
            }

            if ($data['sub_kategori'] !== '' && $data['kategori'] !== '' && ! $this->subCategoryExistsByName($data['sub_kategori'], $data['kategori'])) {
                $notes[] = 'Sub kategori baru akan dibuat.';
            }

            if ($data['brand'] !== '' && ! Brand::where('name', $data['brand'])->exists()) {
                $notes[] = 'Brand baru akan dibuat.';
            }

            if ($data['merek'] !== '' && ! ProductMaker::where('name', $data['merek'])->exists()) {
                $notes[] = 'Merek baru akan dibuat.';
            }

            if ($data['tipe_hp'] !== '' && $data['brand'] === '') {
                $errors[] = 'Brand wajib diisi jika tipe HP diisi.';
            } elseif ($data['tipe_hp'] !== '' && ! $this->productTypesExistByNames($data['tipe_hp'], $data['brand'])) {
                $notes[] = 'Tipe HP baru akan dibuat jika belum ada.';
            }

            if ($data['supplier'] !== '' && ! Supplier::where('name', $data['supplier'])->exists()) {
                $notes[] = 'Supplier baru akan dibuat.';
            }

            if ($data['lokasi_default'] !== '' && ! Location::where('name', $data['lokasi_default'])->exists()) {
                $notes[] = 'Lokasi baru akan dibuat.';
            }

            if ($data['satuan_beli'] !== '' && ! Unit::where('name', $data['satuan_beli'])->exists()) {
                $notes[] = 'Satuan beli baru akan dibuat.';
            }

            if ($data['satuan_jual'] !== '' && ! Unit::where('name', $data['satuan_jual'])->exists()) {
                $notes[] = 'Satuan jual baru akan dibuat.';
            }

            if ($errors) {
                $hasErrors = true;
            }

            $previewRows[] = [
                'row_number' => $row['row_number'],
                'data' => $data,
                'errors' => $errors,
                'notes' => array_values(array_unique($notes)),
                'status' => $errors ? 'error' : 'valid',
            ];
        }

        return [
            'rows' => $previewRows,
            'total' => count($previewRows),
            'valid' => collect($previewRows)->where('status', 'valid')->count(),
            'invalid' => collect($previewRows)->where('status', 'error')->count(),
            'has_errors' => $hasErrors,
            'previewed_at' => now()->format('d M Y H:i'),
        ];
    }

    private function createProductFromImportRow(array $data): Product
    {
        $category = $this->resolveImportCategory($data['kategori']);
        $subCategory = $data['sub_kategori'] !== '' ? $this->resolveImportSubCategory($data['sub_kategori'], $category) : null;
        $brand = $data['brand'] !== '' ? $this->resolveImportBrand($data['brand']) : null;
        $maker = $data['merek'] !== '' ? $this->resolveImportMaker($data['merek']) : null;
        $location = $data['lokasi_default'] !== '' ? $this->resolveImportLocation($data['lokasi_default']) : null;
        $productTypes = $brand && $data['tipe_hp'] !== '' ? $this->resolveImportProductTypes($data['tipe_hp'], $brand) : collect();
        $supplier = $data['supplier'] !== '' ? $this->resolveImportSupplier($data['supplier']) : null;
        $productCode = $data['product_code'] !== ''
            ? Str::upper($data['product_code'])
            : $this->generateProductCode();
        $sku = $data['sku_internal'] !== ''
            ? Str::upper($data['sku_internal'])
            : $productCode;

        $productPayload = [
            'product_code' => $productCode,
            'category_id' => $category->id,
            'sub_category_id' => $subCategory?->id,
            'brand_id' => $brand?->id,
            'product_maker_id' => $maker?->id,
            'default_location_id' => $location?->id,
            'name' => $data['nama'],
            'slug' => $this->generateUniqueSlug($data['nama']),
            'sku' => $sku,
            'purchase_price' => $data['harga_beli'],
            'selling_price' => $data['harga_jual'],
            'buy_unit' => $data['satuan_beli'],
            'sale_unit' => $data['satuan_jual'],
            'default_conversion_qty' => 1,
            'stock_global' => $data['stok_awal'] ?? 0,
            'stock_min' => $data['stok_min'],
            'stock_max' => $data['stok_max'],
            'rack_location' => $data['rak'] ?: null,
            'unit' => $data['satuan_jual'],
            'is_active' => $data['aktif'],
            'is_published' => false,
            'damaged_stock' => 0,
        ];

        if (Schema::hasColumn('products', 'quality')) {
            $productPayload['quality'] = $data['quality'] !== '' ? $data['quality'] : null;
        }

        if (Schema::hasColumn('products', 'is_member_only')) {
            $productPayload['is_member_only'] = $data['member_only'] ?? false;
        }

        $product = Product::create($productPayload);

        if ($productTypes->isNotEmpty()) {
            $product->productTypes()->sync($productTypes->pluck('id')->all());
        }

        $this->ensureUnitMaster($data['satuan_beli']);
        $this->ensureUnitMaster($data['satuan_jual']);

        ProductUnit::create([
            'product_id' => $product->id,
            'level' => 1,
            'unit_name' => $data['satuan_jual'],
            'conversion_qty' => 1,
            'price_toko' => $data['harga_jual'],
            'barcode' => $data['barcode'] ?: null,
            'barcode_label' => $data['satuan_jual'],
        ]);

        if ($data['barcode'] !== '') {
            ProductBarcode::create([
                'product_id' => $product->id,
                'barcode' => $data['barcode'],
                'label' => 'Barcode utama',
                'unit_level' => 1,
                'is_primary' => true,
            ]);

            $product->barcode = $data['barcode'];
            $product->save();
        }

        if ($supplier) {
            ProductSupplier::create([
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'last_purchase_price' => $data['harga_beli'],
                'is_primary' => true,
            ]);
        }

        $this->initializeOpeningStock($product, (float) ($data['stok_awal'] ?? 0), 0);

        return $product;
    }

    private function categoryExistsByName(string $name): bool
    {
        return Category::where('name', $name)->exists();
    }

    private function subCategoryExistsByName(string $name, string $categoryName): bool
    {
        return SubCategory::where('name', $name)
            ->whereHas('category', fn ($query) => $query->where('name', $categoryName))
            ->exists();
    }

    private function productTypesExistByNames(string $names, string $brandName): bool
    {
        $brand = Brand::where('name', $brandName)->first();

        if (! $brand) {
            return false;
        }

        foreach ($this->splitImportList($names) as $name) {
            if (! ProductType::where('brand_id', $brand->id)->where('name', $name)->exists()) {
                return false;
            }
        }

        return true;
    }

    private function resolveImportCategory(string $name): Category
    {
        return Category::firstOrCreate(
            ['name' => $name],
            ['slug' => $this->uniqueSlugForModel(Category::class, $name), 'is_active' => true]
        );
    }

    private function resolveImportSubCategory(string $name, Category $category): SubCategory
    {
        return SubCategory::firstOrCreate(
            ['category_id' => $category->id, 'name' => $name],
            ['slug' => $this->uniqueSlugForModel(SubCategory::class, $name), 'is_active' => true]
        );
    }

    private function resolveImportBrand(string $name): Brand
    {
        return Brand::firstOrCreate(
            ['name' => $name],
            ['slug' => $this->uniqueSlugForModel(Brand::class, $name), 'is_active' => true]
        );
    }

    private function resolveImportMaker(string $name): ProductMaker
    {
        return ProductMaker::firstOrCreate(
            ['name' => $name],
            ['slug' => $this->uniqueSlugForModel(ProductMaker::class, $name), 'is_active' => true]
        );
    }

    private function resolveImportLocation(string $name): Location
    {
        return Location::firstOrCreate(
            ['name' => $name],
            ['code' => Str::upper(Str::limit(Str::slug($name, ''), 20, '')), 'is_active' => true]
        );
    }

    private function resolveImportSupplier(string $name): Supplier
    {
        return Supplier::firstOrCreate(
            ['name' => $name],
            ['slug' => $this->generateUniqueSupplierSlug($name), 'is_active' => true]
        );
    }

    private function resolveImportProductTypes(string $names, Brand $brand)
    {
        return collect($this->splitImportList($names))
            ->map(fn ($name) => ProductType::firstOrCreate(
                ['brand_id' => $brand->id, 'name' => $name],
                ['slug' => $this->uniqueSlugForModel(ProductType::class, $name), 'is_active' => true]
            ));
    }

    private function splitImportList(string $value): array
    {
        return collect(preg_split('/[|,;]/', $value))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function uniqueSlugForModel(string $modelClass, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function getProductFormData(): array
    {
        return [
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'subCategories' => SubCategory::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
            'productMakers' => ProductMaker::where('is_active', true)->orderBy('name')->get(),
            'productQualities' => Schema::hasTable('product_qualities')
                ? ProductQuality::where('is_active', true)->orderBy('name')->get()
                : collect(),
            'productTypes' => ProductType::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::with(['racks' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'customerGroups' => CustomerGroup::where('is_active', true)->orderBy('sort_order')->get(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'unitMasters' => Unit::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $this->normalizeStockIntegerInputs($request);

        $validated = $request->validate([
            'product_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'product_code')->ignore($product?->id),
            ],
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'product_maker_id' => 'nullable|exists:product_makers,id',
            'quality' => 'nullable|string|max:100',
            'default_location_id' => 'nullable|exists:locations,id',
            'default_location_rack_id' => 'nullable|exists:location_racks,id',
            'name' => 'required|string|max:255',
            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($product?->id),
            ],
            'description' => 'nullable|string',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'selling_price_margin_percent' => 'nullable|numeric',
            'expired_date' => 'nullable|date',
            'has_serial_number' => 'nullable|boolean',
            'buy_unit' => 'nullable|string|max:50',
            'sale_unit' => 'nullable|string|max:50',
            'default_conversion_qty' => 'nullable|numeric|min:0.01',
            'stock_global' => 'nullable|integer|min:0',
            'stock_min' => 'nullable|integer|min:0',
            'stock_max' => 'nullable|integer|min:0',
            'damaged_stock' => 'nullable|integer|min:0',
            'discount_value' => 'nullable|numeric|min:0',
            'member_point' => 'nullable|numeric|min:0',
            'staff_point' => 'nullable|numeric|min:0',
            'sales_commission' => 'nullable|numeric|min:0',
            'last_purchase_date' => 'nullable|date',
            'rack_location' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string',
            'is_open_price' => 'nullable|boolean',
            'allow_discount_override' => 'nullable|boolean',
            'sync_sell_price_to_branch' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_member_only' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'product_type_ids' => 'nullable|array',
            'product_type_ids.*' => 'exists:product_types,id',
            'barcodes' => 'nullable|array',
            'barcodes.*.barcode' => 'nullable|string|max:100',
            'barcodes.*.label' => 'nullable|string|max:255',
            'barcodes.*.unit_level' => 'nullable|integer|min:1|max:9',
            'barcodes.*.is_primary' => 'nullable|boolean',
            'units' => 'nullable|array',
            'units.*.level' => 'nullable|integer|min:1|max:9',
            'units.*.unit_name' => 'nullable|string|max:50',
            'units.*.conversion_qty' => 'nullable|numeric|min:0.01',
            'units.*.price_toko' => 'nullable|numeric|min:0',
            'units.*.margin_toko' => 'nullable|numeric',
            'units.*.price_partai' => 'nullable|numeric|min:0',
            'units.*.margin_partai' => 'nullable|numeric',
            'units.*.price_cabang' => 'nullable|numeric|min:0',
            'units.*.margin_cabang' => 'nullable|numeric',
            'units.*.price_lain' => 'nullable|numeric|min:0',
            'units.*.margin_lain' => 'nullable|numeric',
            'units.*.barcode' => 'nullable|string|max:100',
            'units.*.barcode_label' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:2',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_captions' => 'nullable|array|max:2',
            'image_captions.*' => 'nullable|string|max:255',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'nullable|boolean',
            'price_tiers' => 'nullable|array',
            'price_tiers.*.unit_level' => 'nullable|integer|min:1|max:9',
            'price_tiers.*.min_qty' => 'nullable|integer|min:1',
            'price_tiers.*.price' => 'nullable|numeric|min:0',
            'group_prices' => 'nullable|array',
            'group_prices.*.customer_group_id' => 'nullable|exists:customer_groups,id',
            'group_prices.*.toko_price' => 'nullable|numeric|min:0',
            'group_prices.*.partai_price' => 'nullable|numeric|min:0',
            'variants' => 'nullable|array',
            'variants.*.size' => 'nullable|string|max:100',
            'variants.*.color' => 'nullable|string|max:100',
            'variants.*.stock' => 'nullable|numeric|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.notes' => 'nullable|string|max:500',
            'suppliers' => 'nullable|array',
            'suppliers.*.supplier_id' => 'nullable|exists:suppliers,id',
            'suppliers.*.new_supplier_name' => 'nullable|string|max:255',
            'suppliers.*.new_supplier_phone' => 'nullable|string|max:50',
            'suppliers.*.new_supplier_contact_person' => 'nullable|string|max:255',
            'suppliers.*.supplier_product_code' => 'nullable|string|max:100',
            'suppliers.*.last_purchase_price' => 'nullable|numeric|min:0',
            'suppliers.*.is_primary' => 'nullable|boolean',
            'suppliers.*.notes' => 'nullable|string|max:500',
        ]);

        $locationId = isset($validated['default_location_id']) ? (int) $validated['default_location_id'] : null;
        $rackId = isset($validated['default_location_rack_id']) ? (int) $validated['default_location_rack_id'] : null;

        if ($rackId && ! $locationId) {
            throw ValidationException::withMessages([
                'default_location_id' => 'Lokasi default wajib dipilih jika rak default diisi.',
            ]);
        }

        if ($rackId && $locationId) {
            $rackMatches = LocationRack::query()
                ->where('location_id', $locationId)
                ->whereKey($rackId)
                ->exists();

            if (! $rackMatches) {
                throw ValidationException::withMessages([
                    'default_location_rack_id' => 'Rak tidak sesuai dengan lokasi default yang dipilih.',
                ]);
            }
        }

        return $validated;
    }

    private function normalizeStockIntegerInputs(Request $request): void
    {
        foreach (['stock_global', 'stock_min', 'stock_max', 'damaged_stock'] as $key) {
            if (! $request->has($key)) {
                continue;
            }

            $raw = $request->input($key);
            $normalized = $this->normalizeIntegerInput($raw);

            if ($normalized !== $raw) {
                $request->merge([$key => $normalized]);
            }
        }
    }

    /**
     * Normalize common integer inputs coming from browser locale / old values.
     * Examples:
     * - "2.00" -> "2"
     * - "2,00" -> "2"
     * - "1.000" / "1,000" -> "1000"
     *
     * For non-integer decimals (ex: "2.5") we keep the original value so validation fails.
     */
    private function normalizeIntegerInput(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return ((int) $value === $value) ? (int) $value : $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        // Thousand separators: 1.000 / 1,000 / 1.000.000
        if (preg_match('/^\\d{1,3}([\\.,]\\d{3})+$/', $trimmed) === 1) {
            return (string) ((int) str_replace([',', '.'], '', $trimmed));
        }

        // Integers with trailing zeros decimals: 2.00 / 2,000 (as decimals)
        if (preg_match('/^(\\d+)([\\.,](0+))?$/', $trimmed, $matches) === 1) {
            // Only normalize when decimal part exists and is all zeros, or no decimal part.
            return (string) ((int) $matches[1]);
        }

        return $value;
    }

    private function buildProductPayload(array $validated, ?Product $product = null): array
    {
        $productCode = $product?->product_code ?: ($validated['product_code'] ?? null);
        $buyUnit = $this->normalizeUnitName($validated['buy_unit'] ?? null);
        $saleUnit = $this->normalizeUnitName($validated['sale_unit'] ?? null);

        if (! $productCode) {
            $productCode = $product?->product_code ?: $this->generateProductCode();
        }

        $sku = $product?->sku ?: trim((string) ($validated['sku'] ?? ''));

        if ($sku === '') {
            $sku = $product?->sku ?: $productCode;
        }

        $payload = [
            'product_code' => Str::upper($productCode),
            'category_id' => $validated['category_id'],
            'sub_category_id' => $validated['sub_category_id'] ?? null,
            'brand_id' => $validated['brand_id'] ?? null,
            'product_maker_id' => $validated['product_maker_id'] ?? null,
            'default_location_id' => $validated['default_location_id'] ?? null,
            'default_location_rack_id' => $validated['default_location_rack_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name'], $product?->id),
            'sku' => Str::upper($sku),
            'description' => $validated['description'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'selling_price' => $validated['selling_price'],
            'selling_price_margin_percent' => $validated['selling_price_margin_percent'] ?? null,
            'expired_date' => $validated['expired_date'] ?? null,
            'has_serial_number' => (bool) ($validated['has_serial_number'] ?? false),
            'buy_unit' => $buyUnit,
            'sale_unit' => $saleUnit,
            'default_conversion_qty' => $validated['default_conversion_qty'] ?? 1,
            'stock_global' => $validated['stock_global'] ?? 0,
            'stock_min' => $validated['stock_min'] ?? null,
            'stock_max' => $validated['stock_max'] ?? null,
            'damaged_stock' => $validated['damaged_stock'] ?? 0,
            'discount_value' => $validated['discount_value'] ?? null,
            'member_point' => $validated['member_point'] ?? null,
            'staff_point' => $validated['staff_point'] ?? null,
            'sales_commission' => $validated['sales_commission'] ?? null,
            'last_purchase_date' => $validated['last_purchase_date'] ?? null,
            'rack_location' => $validated['rack_location'] ?? null,
            'additional_notes' => $validated['additional_notes'] ?? null,
            'is_open_price' => (bool) ($validated['is_open_price'] ?? false),
            'allow_discount_override' => (bool) ($validated['allow_discount_override'] ?? false),
            'sync_sell_price_to_branch' => (bool) ($validated['sync_sell_price_to_branch'] ?? false),
            'unit' => $saleUnit ?? ($buyUnit ?? 'PCS'),
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];

        if (Schema::hasColumn('products', 'quality')) {
            $payload['quality'] = $validated['quality'] ?? null;
        }

        if (Schema::hasColumn('products', 'is_member_only')) {
            $payload['is_member_only'] = (bool) ($validated['is_member_only'] ?? false);
        }

        return $payload;
    }

    private function syncProductRelations(Request $request, Product $product): void
    {
        $product->productTypes()->sync($request->input('product_type_ids', []));

        $this->ensureUnitMaster($product->buy_unit);
        $this->ensureUnitMaster($product->sale_unit);
        $this->syncBarcodes($product, $request->input('barcodes', []));
        $this->syncUnits($product, $request->input('units', []));
        $this->syncImages($request, $product);
        $this->syncPriceTiers($product, $request->input('price_tiers', []));
        $this->syncCustomerGroupPrices($product, $request->input('group_prices', []));
        $this->syncVariants($product, $request->input('variants', []));
        $this->syncSuppliers($product, $request->input('suppliers', []));

        $product->refresh()->load(['barcodes', 'units', 'images', 'productSuppliers.supplier']);
        $primaryBarcode = $product->primaryBarcode();
        $product->barcode = $primaryBarcode;
        $product->legacy_image_path = $product->images->first()?->image_path;
        $product->save();
    }

    private function initializeOpeningStock(Product $product, float $stockGlobal, float $damagedStock): void
    {
        if (($stockGlobal <= 0 && $damagedStock <= 0) || ! $product->default_location_id) {
            return;
        }

        $product->loadMissing(['defaultLocation', 'defaultRack']);

        if (! $product->defaultLocation) {
            return;
        }

        $ledger = app(StockLedgerService::class);

        $openingQuantity = $stockGlobal + $damagedStock;

        if ($openingQuantity > 0) {
            $ledger->applyMovement(
                $product,
                $product->defaultLocation,
                StockLedgerService::TYPE_OPENING,
                $openingQuantity,
                'Saldo awal dari create produk',
                null,
                null,
                $product->defaultRack
            );
        }

        if ($damagedStock > 0) {
            $ledger->applyMovement(
                $product,
                $product->defaultLocation,
                StockLedgerService::TYPE_DAMAGED_IN,
                $damagedStock,
                'Stok rusak awal dari create produk',
                null,
                null,
                $product->defaultRack
            );
        }
    }

    private function syncBarcodes(Product $product, array $rows): void
    {
        $product->barcodes()->delete();

        $inserted = [];

        foreach ($rows as $index => $row) {
            $barcode = trim((string) ($row['barcode'] ?? ''));

            if ($barcode === '') {
                continue;
            }

            $inserted[] = ProductBarcode::create([
                'product_id' => $product->id,
                'barcode' => $barcode,
                'label' => $row['label'] ?? null,
                'unit_level' => $row['unit_level'] ?? 1,
                'is_primary' => (bool) ($row['is_primary'] ?? false),
            ]);
        }

        if ($inserted && ! collect($inserted)->contains(fn ($barcode) => $barcode->is_primary)) {
            $inserted[0]->update(['is_primary' => true]);
        }
    }

    private function syncUnits(Product $product, array $rows): void
    {
        $product->units()->delete();

        foreach ($rows as $row) {
            $unitName = $this->normalizeUnitName($row['unit_name'] ?? null);

            if ($unitName === null) {
                continue;
            }

            $this->ensureUnitMaster($unitName);

            ProductUnit::create([
                'product_id' => $product->id,
                'level' => $row['level'] ?? 1,
                'unit_name' => $unitName,
                'conversion_qty' => $row['conversion_qty'] ?? 1,
                'price_toko' => $row['price_toko'] ?? null,
                'margin_toko' => $row['margin_toko'] ?? null,
                'price_partai' => $row['price_partai'] ?? null,
                'margin_partai' => $row['margin_partai'] ?? null,
                'price_cabang' => $row['price_cabang'] ?? null,
                'margin_cabang' => $row['margin_cabang'] ?? null,
                'price_lain' => $row['price_lain'] ?? null,
                'margin_lain' => $row['margin_lain'] ?? null,
                'barcode' => $row['barcode'] ?? null,
                'barcode_label' => $row['barcode_label'] ?? null,
            ]);
        }

        if ($product->units()->count() === 0 && $product->sale_unit) {
            $this->ensureUnitMaster($product->sale_unit);

            ProductUnit::create([
                'product_id' => $product->id,
                'level' => 1,
                'unit_name' => $product->sale_unit,
                'conversion_qty' => $product->default_conversion_qty ?? 1,
                'price_toko' => $product->selling_price,
            ]);
        }
    }

    private function normalizeUnitName(?string $unitName): ?string
    {
        $unitName = trim((string) $unitName);

        return $unitName === '' ? null : Str::upper($unitName);
    }

    private function ensureUnitMaster(?string $unitName): void
    {
        $unitName = $this->normalizeUnitName($unitName);

        if ($unitName === null) {
            return;
        }

        Unit::firstOrCreate(
            ['name' => $unitName],
            ['code' => Str::limit($unitName, 20, ''), 'is_active' => true]
        );
    }

    private function syncImages(Request $request, Product $product): void
    {
        $existing = $product->images()->orderBy('sort_order')->get()->values();
        $captions = $request->input('image_captions', []);
        $remove = $request->input('remove_images', []);

        for ($slot = 0; $slot < 2; $slot++) {
            $existingImage = $existing->get($slot);

            if (! empty($remove[$slot]) && $existingImage) {
                $this->deleteProductImage($existingImage->image_path);
                $existingImage->delete();
                $existingImage = null;
            }

            if ($request->hasFile("images.$slot")) {
                if ($existingImage) {
                    $this->deleteProductImage($existingImage->image_path);
                    $existingImage->delete();
                }

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $request->file("images.$slot")->store('products', 'public'),
                    'caption' => $captions[$slot] ?? null,
                    'sort_order' => $slot + 1,
                ]);

                continue;
            }

            if ($existingImage) {
                $existingImage->update([
                    'caption' => $captions[$slot] ?? $existingImage->caption,
                    'sort_order' => $slot + 1,
                ]);
            }
        }
    }

    private function syncPriceTiers(Product $product, array $rows): void
    {
        $product->priceTiers()->delete();

        foreach ($rows as $row) {
            if (blank($row['min_qty'] ?? null) || blank($row['price'] ?? null)) {
                continue;
            }

            ProductPriceTier::create([
                'product_id' => $product->id,
                'unit_level' => $row['unit_level'] ?? 1,
                'min_qty' => $row['min_qty'],
                'price' => $row['price'],
            ]);
        }
    }

    private function syncCustomerGroupPrices(Product $product, array $rows): void
    {
        $product->customerGroupPrices()->delete();

        foreach ($rows as $row) {
            if (blank($row['customer_group_id'] ?? null)) {
                continue;
            }

            if (! blank($row['toko_price'] ?? null)) {
                ProductCustomerGroupPrice::create([
                    'product_id' => $product->id,
                    'customer_group_id' => $row['customer_group_id'],
                    'channel' => 'toko',
                    'price' => $row['toko_price'],
                ]);
            }

            if (! blank($row['partai_price'] ?? null)) {
                ProductCustomerGroupPrice::create([
                    'product_id' => $product->id,
                    'customer_group_id' => $row['customer_group_id'],
                    'channel' => 'partai',
                    'price' => $row['partai_price'],
                ]);
            }
        }
    }

    private function syncVariants(Product $product, array $rows): void
    {
        $product->variants()->delete();

        foreach ($rows as $row) {
            if (blank($row['size'] ?? null) && blank($row['color'] ?? null)) {
                continue;
            }

            ProductVariant::create([
                'product_id' => $product->id,
                'size' => $row['size'] ?? null,
                'color' => $row['color'] ?? null,
                'stock' => $row['stock'] ?? 0,
                'price' => $row['price'] ?? null,
                'notes' => $row['notes'] ?? null,
            ]);
        }
    }

    private function syncSuppliers(Product $product, array $rows): void
    {
        $product->productSuppliers()->delete();

        $inserted = [];

        foreach ($rows as $row) {
            $supplierId = $row['supplier_id'] ?? null;

            if (blank($supplierId) && filled($row['new_supplier_name'] ?? null)) {
                $supplierName = trim((string) $row['new_supplier_name']);
                $supplier = Supplier::firstOrCreate(
                    ['name' => $supplierName],
                    [
                        'slug' => $this->generateUniqueSupplierSlug($supplierName),
                        'phone' => $row['new_supplier_phone'] ?? null,
                        'contact_person' => $row['new_supplier_contact_person'] ?? null,
                        'is_active' => true,
                    ]
                );

                $supplierId = $supplier->id;
            }

            if (blank($supplierId)) {
                continue;
            }

            $inserted[] = ProductSupplier::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'supplier_id' => $supplierId,
                ],
                [
                    'supplier_product_code' => $row['supplier_product_code'] ?? null,
                    'last_purchase_price' => $row['last_purchase_price'] ?? null,
                    'is_primary' => (bool) ($row['is_primary'] ?? false),
                    'notes' => $row['notes'] ?? null,
                ]
            );
        }

        if ($inserted && ! collect($inserted)->contains(fn ($supplier) => $supplier->is_primary)) {
            $inserted[0]->update(['is_primary' => true]);
        }
    }

    private function loggableProductState(Product $product): array
    {
        return [
            'product_code' => $product->product_code,
            'name' => $product->name,
            'category' => $product->category?->name,
            'sub_category' => $product->subCategory?->name,
            'brand' => $product->brand?->name,
            'maker' => $product->maker?->name,
            'quality' => $product->quality,
            'types' => $product->productTypes->pluck('name')->toArray(),
            'suppliers' => $product->productSuppliers->map(fn ($item) => [
                'supplier' => $item->supplier?->name,
                'supplier_product_code' => $item->supplier_product_code,
                'last_purchase_price' => $item->last_purchase_price,
                'is_primary' => $item->is_primary,
            ])->values()->all(),
            'sku' => $product->sku,
            'barcode' => $product->primaryBarcode(),
            'sale_unit' => $product->sale_unit,
            'buy_unit' => $product->buy_unit,
            'selling_price' => $product->selling_price,
            'stock_global' => $product->stock_global,
            'is_published' => $product->is_published,
            'is_member_only' => $product->is_member_only,
            'is_active' => $product->is_active,
        ];
    }

    private function deleteProductImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function generateProductCode(): string
    {
        do {
            $code = 'PRD-' . now()->format('ymdHisv') . '-' . random_int(1000, 9999);
        } while (
            Product::where('product_code', $code)->exists()
            || Product::where('sku', $code)->exists()
        );

        return $code;
    }

    private function generateUniqueSupplierSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Supplier::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Product::withTrashed()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}

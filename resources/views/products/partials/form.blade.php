@php
    $barcodes = old(
        'barcodes',
        isset($product)
            ? $product->barcodes
                ->map(
                    fn($item) => [
                        'barcode' => $item->barcode,
                        'label' => $item->label,
                        'unit_level' => $item->unit_level,
                        'is_primary' => $item->is_primary,
                    ],
                )
                ->toArray()
            : [['barcode' => '', 'label' => '', 'unit_level' => 1, 'is_primary' => true]],
    );
    $mainBarcode = $barcodes[0] ?? [
        'barcode' => '',
        'label' => 'Barcode Utama',
        'unit_level' => 1,
        'is_primary' => true,
    ];
    $additionalBarcodes = array_slice($barcodes, 1);
    $units = old(
        'units',
        isset($product)
            ? $product->units
                ->map(
                    fn($item) => [
                        'level' => $item->level,
                        'unit_name' => $item->unit_name,
                        'conversion_qty' => $item->conversion_qty,
                        'price_toko' => $item->price_toko,
                        'margin_toko' => $item->margin_toko,
                        'price_partai' => $item->price_partai,
                        'margin_partai' => $item->margin_partai,
                        'price_cabang' => $item->price_cabang,
                        'margin_cabang' => $item->margin_cabang,
                        'price_lain' => $item->price_lain,
                        'margin_lain' => $item->margin_lain,
                        'barcode' => $item->barcode,
                        'barcode_label' => $item->barcode_label,
                    ],
                )
                ->toArray()
            : [
                [
                    'level' => 1,
                    'unit_name' => old('sale_unit', $product?->sale_unit ?? 'PCS'),
                    'conversion_qty' => old('default_conversion_qty', $product?->default_conversion_qty ?? 1),
                    'price_toko' => old('selling_price', $product?->selling_price),
                    'margin_toko' => '',
                    'price_partai' => '',
                    'margin_partai' => '',
                    'price_cabang' => '',
                    'margin_cabang' => '',
                    'price_lain' => '',
                    'margin_lain' => '',
                    'barcode' => '',
                    'barcode_label' => '',
                ],
            ],
    );
    $priceTiers = old(
        'price_tiers',
        isset($product)
            ? $product->priceTiers
                ->map(
                    fn($item) => [
                        'unit_level' => $item->unit_level,
                        'min_qty' => $item->min_qty,
                        'price' => $item->price,
                    ],
                )
                ->toArray()
            : [['unit_level' => 1, 'min_qty' => 1, 'price' => '']],
    );
    $groupPrices = old(
        'group_prices',
        isset($product)
            ? $customerGroups
                ->map(function ($group) use ($product) {
                    return [
                        'customer_group_id' => $group->id,
                        'toko_price' => optional(
                            $product->customerGroupPrices->first(
                                fn($row) => $row->customer_group_id === $group->id && $row->channel === 'toko',
                            ),
                        )->price,
                        'partai_price' => optional(
                            $product->customerGroupPrices->first(
                                fn($row) => $row->customer_group_id === $group->id && $row->channel === 'partai',
                            ),
                        )->price,
                    ];
                })
                ->toArray()
            : $customerGroups
                ->map(
                    fn($group) => [
                        'customer_group_id' => $group->id,
                        'toko_price' => '',
                        'partai_price' => '',
                    ],
                )
                ->toArray(),
    );
    $variants = old(
        'variants',
        isset($product)
            ? $product->variants
                ->map(
                    fn($item) => [
                        'size' => $item->size,
                        'color' => $item->color,
                        'stock' => $item->stock,
                        'price' => $item->price,
                        'notes' => $item->notes,
                    ],
                )
                ->toArray()
            : [['size' => '', 'color' => '', 'stock' => '', 'price' => '', 'notes' => '']],
    );
    $productSuppliers = old(
        'suppliers',
        isset($product)
            ? $product->productSuppliers
                ->map(
                    fn($item) => [
                        'supplier_id' => $item->supplier_id,
                        'new_supplier_name' => '',
                        'new_supplier_phone' => '',
                        'new_supplier_contact_person' => '',
                        'supplier_product_code' => $item->supplier_product_code,
                        'last_purchase_price' => $item->last_purchase_price,
                        'is_primary' => $item->is_primary,
                        'notes' => $item->notes,
                    ],
                )
                ->toArray()
            : [
                [
                    'supplier_id' => '',
                    'supplier_product_code' => '',
                    'last_purchase_price' => '',
                    'is_primary' => true,
                    'notes' => '',
                ],
            ],
    );
    $images = $product?->images->sortBy('sort_order')->values() ?? collect();
    $isExistingProduct = isset($product) && filled($product->id);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Form belum tersimpan. Periksa field yang berwarna merah.</div>
        <div class="small">{!! implode('<br>', array_slice($errors->all(), 0, 6)) !!}</div>
    </div>
@endif

<input type="hidden" name="_active_tab" id="active_tab" value="{{ old('_active_tab', '') }}">

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-standard" type="button"
            role="tab">Standard</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-advanced" type="button"
            role="tab">Advance</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-member" type="button" role="tab">Member,
            Pelanggan, Sales</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-tier" type="button" role="tab">Harga
            Bertingkat</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-variant" type="button" role="tab">Stok
            by Ukuran & Warna</button>
    </li>
</ul>

<div class="tab-content p-0">
    <div class="tab-pane fade show active" id="tab-standard" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="mb-0">Identitas Barang</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="product_code">Kode Barang</label>
                                <input type="text" id="product_code" name="product_code"
                                    class="form-control @error('product_code') is-invalid @enderror"
                                    value="{{ old('product_code', $product?->product_code) }}"
                                    placeholder="Kosongkan untuk auto generate" @readonly($isExistingProduct)>
                                @if ($isExistingProduct)
                                    <div class="form-text">Kode barang tidak bisa diubah setelah produk dibuat.</div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sku">SKU Internal</label>
                                <input type="text" id="sku" name="sku"
                                    class="form-control @error('sku') is-invalid @enderror"
                                    value="{{ old('sku', $product?->sku) }}" @readonly($isExistingProduct)>
                                <div class="form-text">
                                    {{ $isExistingProduct ? 'SKU tidak bisa diubah setelah produk dibuat.' : 'Opsional. Jika kosong, sistem akan mengikuti kode barang.' }}
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label mb-0" for="main_barcode">Barcode Utama</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-main-barcode-row">
                                        <i class="bx bx-plus"></i> Barcode
                                    </button>
                                </div>
                                <input type="hidden" name="barcodes[0][label]"
                                    value="{{ $mainBarcode['label'] ?? 'Barcode Utama' }}">
                                <input type="hidden" name="barcodes[0][unit_level]" value="1">
                                <input type="hidden" name="barcodes[0][is_primary]" value="1">
                                <input type="text" id="main_barcode" name="barcodes[0][barcode]"
                                    class="form-control js-scanner-input @error('barcodes.0.barcode') is-invalid @enderror"
                                    value="{{ old('barcodes.0.barcode', $mainBarcode['barcode'] ?? '') }}"
                                    autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                                @error('barcodes.0.barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="main-additional-barcode-rows" class="mt-2">
                                    @foreach ($additionalBarcodes as $barcodeIndex => $barcodeRow)
                                        @php $rowIndex = $barcodeIndex + 1; @endphp
                                        <div class="input-group mb-2 main-additional-barcode-row">
                                            <input type="hidden" name="barcodes[{{ $rowIndex }}][label]" value="{{ $barcodeRow['label'] ?? 'Barcode Tambahan' }}">
                                            <input type="hidden" name="barcodes[{{ $rowIndex }}][unit_level]" value="{{ $barcodeRow['unit_level'] ?? 1 }}">
                                            <input type="hidden" name="barcodes[{{ $rowIndex }}][is_primary]" value="0">
                                            <input type="text" name="barcodes[{{ $rowIndex }}][barcode]" class="form-control js-scanner-input" value="{{ $barcodeRow['barcode'] ?? '' }}" placeholder="Barcode tambahan" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                                            <button type="button" class="btn btn-outline-danger" data-remove-main-barcode>&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="name">Nama Barang <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $product?->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="category_id">Kategori <span
                                        class="text-danger">*</span></label>
                                <select id="category_id" name="category_id"
                                    class="form-select js-select2-single @error('category_id') is-invalid @enderror"
                                    required>
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ (string) old('category_id', $product?->category_id) === (string) $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="sub_category_id">Sub Kategori</label>
                                <select id="sub_category_id" name="sub_category_id"
                                    class="form-select js-select2-single @error('sub_category_id') is-invalid @enderror">
                                    <option value="">Pilih sub kategori</option>
                                    @foreach ($subCategories as $subCategory)
                                        <option value="{{ $subCategory->id }}"
                                            data-category-id="{{ $subCategory->category_id }}"
                                            {{ (string) old('sub_category_id', $product?->sub_category_id) === (string) $subCategory->id ? 'selected' : '' }}>
                                            {{ $subCategory->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="brand_id">Brand</label>
                                <select id="brand_id" name="brand_id"
                                    class="form-select js-select2-single @error('brand_id') is-invalid @enderror">
                                    <option value="">Pilih brand</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}"
                                            {{ (string) old('brand_id', $product?->brand_id) === (string) $brand->id ? 'selected' : '' }}>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="product_type_ids">Tipe HP</label>
                                <select id="product_type_ids" name="product_type_ids[]"
                                    class="form-select js-select2-multiple" multiple>
                                    @foreach ($productTypes as $type)
                                        <option value="{{ $type->id }}" data-brand-id="{{ $type->brand_id }}"
                                            {{ in_array($type->id, old('product_type_ids', $product?->productTypes->pluck('id')->toArray() ?? [])) ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="product_maker_id">Merek</label>
                                <select id="product_maker_id" name="product_maker_id"
                                    class="form-select js-select2-single @error('product_maker_id') is-invalid @enderror">
                                    <option value="">Pilih merek</option>
                                    @foreach ($productMakers as $maker)
                                        <option value="{{ $maker->id }}"
                                            {{ (string) old('product_maker_id', $product?->product_maker_id) === (string) $maker->id ? 'selected' : '' }}>
                                            {{ $maker->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="quality">Quality</label>
                                <div class="d-flex gap-2">
                                    <select id="quality" name="quality"
                                        class="form-select js-quality-select @error('quality') is-invalid @enderror">
                                        <option value="">Pilih quality</option>
                                        @foreach ($productQualities as $quality)
                                            <option value="{{ $quality->name }}"
                                                {{ (string) old('quality', $product?->quality) === (string) $quality->name ? 'selected' : '' }}>
                                                {{ $quality->name }}
                                            </option>
                                        @endforeach
                                        @if (filled(old('quality', $product?->quality)) && ! collect($productQualities)->contains(fn ($item) => $item->name === old('quality', $product?->quality)))
                                            <option value="{{ old('quality', $product?->quality) }}" selected>
                                                {{ old('quality', $product?->quality) }}
                                            </option>
                                        @endif
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary flex-shrink-0"
                                        id="open-quality-master-modal">+</button>
                                </div>
                                @error('quality')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="expired_date">Expired Date</label>
                                <input type="date" id="expired_date" name="expired_date" class="form-control"
                                    value="{{ old('expired_date', optional($product?->expired_date)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="default_location_id">Lokasi Default</label>
                                <div class="d-flex gap-2">
                                    <select id="default_location_id" name="default_location_id" class="form-select js-location-select">
                                        <option value="">Pilih lokasi</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}"
                                                {{ (string) old('default_location_id', $product?->default_location_id) === (string) $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary flex-shrink-0" id="open-location-master-modal">+</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="default_location_rack_id">Rak Default</label>
                                <select id="default_location_rack_id" name="default_location_rack_id" class="form-select js-select2-single">
                                    <option value="">Pilih rak</option>
                                    @foreach ($locations as $location)
                                        @foreach ($location->racks as $rack)
                                            <option value="{{ $rack->id }}" data-location-id="{{ $location->id }}"
                                                {{ (string) old('default_location_rack_id', $product?->default_location_rack_id) === (string) $rack->id ? 'selected' : '' }}>
                                                {{ $location->name }} - {{ $rack->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                                <div class="form-text">Rak mengikuti lokasi default, contoh Toko - Rak 1.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="last_purchase_date_standard">Tanggal Beli</label>
                                <input type="date" id="last_purchase_date_standard" name="last_purchase_date" class="form-control"
                                    value="{{ old('last_purchase_date', optional($product?->last_purchase_date)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12">
                                <div class="d-flex flex-column flex-md-row gap-3 gap-md-4">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="has_serial_number"
                                            name="has_serial_number" value="1"
                                            {{ old('has_serial_number', $product?->has_serial_number) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_serial_number">Mempunyai Serial Number</label>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="is_published"
                                            name="is_published" value="1"
                                            {{ old('is_published', $product?->is_published) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_published">Tampilkan di website / report</label>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="is_member_only"
                                            name="is_member_only" value="1"
                                            {{ old('is_member_only', $product?->is_member_only) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_member_only">Tampilkan untuk member saja</label>
                                    </div>
                                </div>
                                <div class="form-text">Serial number untuk barang seperti pulsa, voucher, atau unit yang dijual per serial.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border shadow-none mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Supplier Barang</h6>
                            <small class="text-muted">Pilih supplier barang. Gunakan tombol + jika supplier belum
                                ada.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $supplierRow = $productSuppliers[0] ?? [
                                'supplier_id' => '',
                                'supplier_product_code' => '',
                                'last_purchase_price' => '',
                                'is_primary' => true,
                                'notes' => '',
                            ];
                        @endphp
                        <div class="supplier-row">
                            <label class="form-label">Supplier</label>
                            <div class="d-flex gap-2">
                                <select name="suppliers[0][supplier_id]" class="form-select js-supplier-select">
                                    <option value="">Pilih supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}"
                                            {{ (string) ($supplierRow['supplier_id'] ?? '') === (string) $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-secondary flex-shrink-0" data-open-supplier-master-modal>+</button>
                            </div>
                            <input type="hidden" name="suppliers[0][is_primary]" value="1">
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-5">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="mb-0">Harga, Stok, dan Gambar</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Satuan Beli</label>
                                @php $buyUnitValue = old('buy_unit', $product?->buy_unit); @endphp
                                <div class="d-flex gap-2">
                                    <select name="buy_unit" class="form-select js-unit-select">
                                        @if (!blank($buyUnitValue))
                                            <option value="{{ $buyUnitValue }}" selected>{{ $buyUnitValue }}</option>
                                        @endif
                                        @foreach ($unitMasters as $unitMaster)
                                            @continue($buyUnitValue === $unitMaster->name)
                                            <option value="{{ $unitMaster->name }}">{{ $unitMaster->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary flex-shrink-0"
                                        data-open-unit-master-modal>+</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Satuan Jual</label>
                                @php $saleUnitValue = old('sale_unit', $product?->sale_unit); @endphp
                                <div class="d-flex gap-2">
                                    <select name="sale_unit" class="form-select js-unit-select">
                                        @if (!blank($saleUnitValue))
                                            <option value="{{ $saleUnitValue }}" selected>{{ $saleUnitValue }}</option>
                                        @endif
                                        @foreach ($unitMasters as $unitMaster)
                                            @continue($saleUnitValue === $unitMaster->name)
                                            <option value="{{ $unitMaster->name }}">{{ $unitMaster->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-secondary flex-shrink-0"
                                        data-open-unit-master-modal>+</button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Isi Default</label>
                                <input type="number" step="0.01" min="0.01" name="default_conversion_qty"
                                    class="form-control"
                                    value="{{ old('default_conversion_qty', $product?->default_conversion_qty ?? 1) }}">
                                <div class="form-text">Konversi default dari satuan beli ke satuan jual.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Harga Beli</label>
                                <input type="number" step="0.01" min="0" name="purchase_price" id="purchase_price"
                                    class="form-control"
                                    value="{{ old('purchase_price', $product?->purchase_price) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual by Persentase</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="selling_price_margin_percent" id="selling_price_margin_percent"
                                        class="form-control" value="{{ old('selling_price_margin_percent', $product?->selling_price_margin_percent) }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">Dihitung dari harga beli.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga Jual Manual <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="selling_price" id="selling_price"
                                    class="form-control" value="{{ old('selling_price', $product?->selling_price) }}"
                                    required>
                                <div class="form-text">Manual dan persentase saling mengikuti.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stok Global</label>
                                <input type="number" step="1" min="0" name="stock_global"
                                    class="form-control"
                                    value="{{ old('stock_global', $product?->stock_global !== null ? (int) $product->stock_global : 0) }}" @readonly($isExistingProduct)>
                                @if ($isExistingProduct)
                                    <div class="form-text">Total stok dihitung dari stok per lokasi.</div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stok Rusak</label>
                                <input type="number" step="1" min="0" name="damaged_stock"
                                    class="form-control"
                                    value="{{ old('damaged_stock', $product?->damaged_stock !== null ? (int) $product->damaged_stock : 0) }}"
                                    @readonly($isExistingProduct)>
                                @if ($isExistingProduct)
                                    <div class="form-text">Ubah stok rusak dari halaman stok lokasi.</div>
                                @endif
                            </div>
                        </div>

                        @for ($slot = 0; $slot < 2; $slot++)
                            @php $existingImage = $images->get($slot); @endphp
                            <div class="border rounded p-3 mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Gambar {{ $slot + 1 }}</h6>
                                    @if ($existingImage)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1"
                                                name="remove_images[{{ $slot }}]"
                                                id="remove_image_{{ $slot }}">
                                            <label class="form-check-label"
                                                for="remove_image_{{ $slot }}">Hapus</label>
                                        </div>
                                    @endif
                                </div>
                                <input type="file" name="images[{{ $slot }}]"
                                    accept=".jpg,.jpeg,.png,.webp" class="form-control mb-2 product-image-input"
                                    data-preview-target="preview-{{ $slot }}">
                                <input type="text" name="image_captions[{{ $slot }}]"
                                    class="form-control mb-3" placeholder="Keterangan gambar"
                                    value="{{ old("image_captions.$slot", $existingImage?->caption) }}">
                                <div class="border rounded bg-light p-3 text-center">
                                    <img id="preview-{{ $slot }}"
                                        src="{{ $existingImage?->image_path ? asset('storage/' . $existingImage->image_path) : 'data:image/svg+xml;utf8,<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;300&quot; height=&quot;220&quot; viewBox=&quot;0 0 300 220&quot;><rect width=&quot;300&quot; height=&quot;220&quot; fill=&quot;%23eef2f7&quot;/><text x=&quot;150&quot; y=&quot;116&quot; font-size=&quot;22&quot; text-anchor=&quot;middle&quot; fill=&quot;%2394a3b8&quot; font-family=&quot;Arial&quot;>NO IMAGE</text></svg>' }}"
                                        alt="Preview image {{ $slot + 1 }}"
                                        style="width:100%; max-height:220px; object-fit:contain;">
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-advanced" role="tabpanel">
        <div class="card border shadow-none mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">Detail Harga Barang per Satuan</h6>
                    <small class="text-muted">Setiap satuan bisa punya barcode masing-masing.</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="unit">Tambah
                    Satuan</button>
            </div>
            <div class="card-body">
                <div id="unit-rows" class="d-grid gap-3">
                    @foreach ($units as $index => $row)
                        <div class="border rounded p-3 unit-row">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-semibold">Satuan Level {{ $row['level'] ?? $index + 1 }}</div>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-remove-row>&times;</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2"><label class="form-label">Level</label><input type="number"
                                        min="1" max="9" name="units[{{ $index }}][level]"
                                        class="form-control" value="{{ $row['level'] ?? 1 }}"></div>
                                <div class="col-md-4">
                                    <label class="form-label">Satuan</label>
                                    <div class="d-flex gap-2">
                                        <select name="units[{{ $index }}][unit_name]"
                                            class="form-select js-unit-select">
                                            @if (!blank($row['unit_name'] ?? null))
                                                <option value="{{ $row['unit_name'] }}" selected>
                                                    {{ $row['unit_name'] }}</option>
                                            @endif
                                            @foreach ($unitMasters as $unitMaster)
                                                <option value="{{ $unitMaster->name }}"
                                                    {{ ($row['unit_name'] ?? '') === $unitMaster->name ? 'selected' : '' }}>
                                                    {{ $unitMaster->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary flex-shrink-0"
                                            data-open-unit-master-modal>+</button>
                                    </div>
                                </div>
                                <div class="col-md-3"><label class="form-label">Isi / Konversi</label><input
                                        type="number" step="0.01" min="0.01"
                                        name="units[{{ $index }}][conversion_qty]" class="form-control"
                                        value="{{ $row['conversion_qty'] ?? 1 }}"></div>
                                <div class="col-md-3"><label class="form-label">Barcode Satuan</label><input
                                        type="text" name="units[{{ $index }}][barcode]"
                                        class="form-control js-scanner-input" value="{{ $row['barcode'] ?? '' }}"
                                        autocomplete="off" autocapitalize="off" autocorrect="off"
                                        spellcheck="false"></div>
                                <div class="col-md-3"><label class="form-label">Harga Toko</label><input
                                        type="number" step="0.01" min="0"
                                        name="units[{{ $index }}][price_toko]" class="form-control"
                                        value="{{ $row['price_toko'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Margin Toko</label><input
                                        type="number" step="0.01" name="units[{{ $index }}][margin_toko]"
                                        class="form-control" value="{{ $row['margin_toko'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Harga Partai</label><input
                                        type="number" step="0.01" min="0"
                                        name="units[{{ $index }}][price_partai]" class="form-control"
                                        value="{{ $row['price_partai'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Margin Partai</label><input
                                        type="number" step="0.01"
                                        name="units[{{ $index }}][margin_partai]" class="form-control"
                                        value="{{ $row['margin_partai'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Harga Cabang</label><input
                                        type="number" step="0.01" min="0"
                                        name="units[{{ $index }}][price_cabang]" class="form-control"
                                        value="{{ $row['price_cabang'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Margin Cabang</label><input
                                        type="number" step="0.01"
                                        name="units[{{ $index }}][margin_cabang]" class="form-control"
                                        value="{{ $row['margin_cabang'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Harga Lain</label><input
                                        type="number" step="0.01" min="0"
                                        name="units[{{ $index }}][price_lain]" class="form-control"
                                        value="{{ $row['price_lain'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Margin Lain</label><input
                                        type="number" step="0.01" name="units[{{ $index }}][margin_lain]"
                                        class="form-control" value="{{ $row['margin_lain'] ?? '' }}"></div>
                                <div class="col-md-6"><label class="form-label">Label Barcode</label><input
                                        type="text" name="units[{{ $index }}][barcode_label]"
                                        class="form-control" value="{{ $row['barcode_label'] ?? '' }}"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card border shadow-none">
            <div class="card-header">
                <h6 class="mb-0">Setting Tambahan</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Stok Min</label><input type="number"
                            step="1" min="0" name="stock_min" class="form-control"
                            value="{{ old('stock_min', $product?->stock_min !== null ? (int) $product->stock_min : '') }}"></div>
                    <div class="col-md-6"><label class="form-label">Stok Max</label><input type="number"
                            step="1" min="0" name="stock_max" class="form-control"
                            value="{{ old('stock_max', $product?->stock_max !== null ? (int) $product->stock_max : '') }}"></div>
                    <div class="col-12"><label class="form-label">Keterangan Tambahan</label>
                        <textarea name="additional_notes" rows="4" class="form-control">{{ old('additional_notes', $product?->additional_notes) }}</textarea>
                    </div>
                    <div class="col-12">
                        <input type="hidden" name="is_open_price" value="0">
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox"
                                name="is_open_price" id="is_open_price" value="1"
                                {{ old('is_open_price', $product?->is_open_price) ? 'checked' : '' }}><label
                                class="form-check-label" for="is_open_price">Open Price [Harga Jual]</label></div>
                        <input type="hidden" name="allow_discount_override" value="0">
                        <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox"
                                name="allow_discount_override" id="allow_discount_override" value="1"
                                {{ old('allow_discount_override', $product?->allow_discount_override) ? 'checked' : '' }}><label
                                class="form-check-label" for="allow_discount_override">Open Price [Diskon
                                Jual]</label></div>
                        <input type="hidden" name="sync_sell_price_to_branch" value="0">
                        <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox"
                                name="sync_sell_price_to_branch" id="sync_sell_price_to_branch" value="1"
                                {{ old('sync_sell_price_to_branch', $product?->sync_sell_price_to_branch) ? 'checked' : '' }}><label
                                class="form-check-label" for="sync_sell_price_to_branch">Sinkron harga jual ke
                                cabang</label></div>
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch mt-2"><input class="form-check-input" type="checkbox"
                                name="is_active" id="is_active" value="1"
                                {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}><label
                                class="form-check-label" for="is_active">Produk aktif</label></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="tab-pane fade" id="tab-member" role="tabpanel">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="mb-0">Diskon dan Point</h6>
                    </div>
                    <div class="card-body row g-3">
                        <div class="col-12"><label class="form-label">Diskon Barang</label><input type="number"
                                step="0.01" min="0" name="discount_value" class="form-control"
                                value="{{ old('discount_value', $product?->discount_value) }}"></div>
                        <div class="col-12"><label class="form-label">Point Member</label><input type="number"
                                step="0.01" min="0" name="member_point" class="form-control"
                                value="{{ old('member_point', $product?->member_point) }}"></div>
                        <div class="col-12"><label class="form-label">Point Karyawan</label><input type="number"
                                step="0.01" min="0" name="staff_point" class="form-control"
                                value="{{ old('staff_point', $product?->staff_point) }}"></div>
                        <div class="col-12"><label class="form-label">Komisi Sales</label><input type="number"
                                step="0.01" min="0" name="sales_commission" class="form-control"
                                value="{{ old('sales_commission', $product?->sales_commission) }}"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border shadow-none">
                    <div class="card-header">
                        <h6 class="mb-0">Harga Jual Per Pelanggan</h6>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Group</th>
                                    <th>Harga Penjualan Toko</th>
                                    <th>Harga Penjualan Partai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupPrices as $index => $row)
                                    @php $group = $customerGroups->firstWhere('id', $row['customer_group_id']); @endphp
                                    <tr>
                                        <td>
                                            {{ $group?->name ?? 'Group' }}
                                            <input type="hidden"
                                                name="group_prices[{{ $index }}][customer_group_id]"
                                                value="{{ $row['customer_group_id'] }}">
                                        </td>
                                        <td><input type="number" step="0.01" min="0" class="form-control"
                                                name="group_prices[{{ $index }}][toko_price]"
                                                value="{{ $row['toko_price'] }}"></td>
                                        <td><input type="number" step="0.01" min="0" class="form-control"
                                                name="group_prices[{{ $index }}][partai_price]"
                                                value="{{ $row['partai_price'] }}"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-tier" role="tabpanel">
        <div class="card border shadow-none">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Harga Bertingkat</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="tier">Tambah Row</button>
            </div>
            <div class="card-body">
                <div id="tier-rows" class="d-grid gap-3">
                    @foreach ($priceTiers as $index => $row)
                        <div class="border rounded p-3 tier-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3"><label class="form-label">Level Satuan</label><input
                                        type="number" min="1" max="9"
                                        name="price_tiers[{{ $index }}][unit_level]" class="form-control"
                                        value="{{ $row['unit_level'] ?? 1 }}"></div>
                                <div class="col-md-4"><label class="form-label">Minimal Qty</label><input
                                        type="number" min="1"
                                        name="price_tiers[{{ $index }}][min_qty]" class="form-control"
                                        value="{{ $row['min_qty'] ?? 1 }}"></div>
                                <div class="col-md-4"><label class="form-label">Harga</label><input type="number"
                                        step="0.01" min="0"
                                        name="price_tiers[{{ $index }}][price]" class="form-control"
                                        value="{{ $row['price'] ?? '' }}"></div>
                                <div class="col-md-1 text-end"><button type="button"
                                        class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-variant" role="tabpanel">
        <div class="card border shadow-none">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Stok Berdasarkan Ukuran dan Warna</h6>
                <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="variant">Tambah
                    Variasi</button>
            </div>
            <div class="card-body">
                <div class="alert alert-label-secondary">Jika variasi dipakai, stok dan harga bisa dipisah per
                    ukuran/warna seperti pola aplikasi lama.</div>
                <div id="variant-rows" class="d-grid gap-3">
                    @foreach ($variants as $index => $row)
                        <div class="border rounded p-3 variant-row">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2"><label class="form-label">Ukuran</label><input type="text"
                                        name="variants[{{ $index }}][size]" class="form-control"
                                        value="{{ $row['size'] ?? '' }}"></div>
                                <div class="col-md-2"><label class="form-label">Warna</label><input type="text"
                                        name="variants[{{ $index }}][color]" class="form-control"
                                        value="{{ $row['color'] ?? '' }}"></div>
                                <div class="col-md-2"><label class="form-label">Stok</label><input type="number"
                                        step="0.01" min="0" name="variants[{{ $index }}][stock]"
                                        class="form-control" value="{{ $row['stock'] ?? '' }}"></div>
                                <div class="col-md-2"><label class="form-label">Harga</label><input type="number"
                                        step="0.01" min="0" name="variants[{{ $index }}][price]"
                                        class="form-control" value="{{ $row['price'] ?? '' }}"></div>
                                <div class="col-md-3"><label class="form-label">Catatan</label><input type="text"
                                        name="variants[{{ $index }}][notes]" class="form-control"
                                        value="{{ $row['notes'] ?? '' }}"></div>
                                <div class="col-md-1 text-end"><button type="button"
                                        class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="locationMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="location-master-error"></div>
                <div class="mb-3">
                    <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                    <input type="text" id="location_master_name" class="form-control" placeholder="Contoh: Gudang 2">
                </div>
                <div class="mb-3">
                    <label class="form-label">Kode Lokasi <span class="text-danger">*</span></label>
                    <input type="text" id="location_master_code" class="form-control" placeholder="Contoh: GDG2">
                </div>
                <div class="mb-3">
                    <label class="form-label">Rak</label>
                    <textarea id="location_master_racks" rows="4" class="form-control" placeholder="Contoh:&#10;Rak 1&#10;Rak 2"></textarea>
                    <div class="form-text">Isi satu rak per baris.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-location-master">Simpan Lokasi</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qualityMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Quality</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="quality-master-error"></div>
                <div class="mb-3">
                    <label class="form-label" for="quality_master_name">Nama Quality <span class="text-danger">*</span></label>
                    <input type="text" id="quality_master_name" class="form-control" placeholder="Contoh: Original, OEM, Grade A">
                </div>
                <div class="mb-0">
                    <label class="form-label" for="quality_master_code">Kode</label>
                    <input type="text" id="quality_master_code" class="form-control" placeholder="Opsional">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-quality-master">Simpan Quality</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="unitRowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Satuan Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="unit_modal_level">Level</label>
                        <input type="number" min="1" max="9" id="unit_modal_level"
                            class="form-control" value="1">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="unit_modal_name">Satuan</label>
                        <div class="d-flex gap-2">
                            <select id="unit_modal_name" class="form-select js-unit-select">
                                <option value="">Pilih / ketik satuan</option>
                                @foreach ($unitMasters as $unitMaster)
                                    <option value="{{ $unitMaster->name }}">{{ $unitMaster->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-secondary flex-shrink-0"
                                data-open-unit-master-modal>+</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_conversion">Isi / Konversi</label>
                        <input type="number" step="0.01" min="0.01" id="unit_modal_conversion"
                            class="form-control" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_barcode">Barcode Satuan</label>
                        <input type="text" id="unit_modal_barcode" class="form-control js-scanner-input"
                            autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_price_toko">Harga Toko</label>
                        <input type="number" step="0.01" min="0" id="unit_modal_price_toko"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_margin_toko">Margin Toko</label>
                        <input type="number" step="0.01" id="unit_modal_margin_toko" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_price_partai">Harga Partai</label>
                        <input type="number" step="0.01" min="0" id="unit_modal_price_partai"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_margin_partai">Margin Partai</label>
                        <input type="number" step="0.01" id="unit_modal_margin_partai" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_price_cabang">Harga Cabang</label>
                        <input type="number" step="0.01" min="0" id="unit_modal_price_cabang"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_margin_cabang">Margin Cabang</label>
                        <input type="number" step="0.01" id="unit_modal_margin_cabang" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_price_lain">Harga Lain</label>
                        <input type="number" step="0.01" min="0" id="unit_modal_price_lain"
                            class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_margin_lain">Margin Lain</label>
                        <input type="number" step="0.01" id="unit_modal_margin_lain" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="unit_modal_barcode_label">Label Barcode</label>
                        <input type="text" id="unit_modal_barcode_label" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-unit-row">Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="unitMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Master Satuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label" for="unit_master_name">Nama Satuan <span
                            class="text-danger">*</span></label>
                    <input type="text" id="unit_master_name" class="form-control"
                        placeholder="Contoh: PCS, LUSIN, BOX">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="unit_master_code">Kode</label>
                    <input type="text" id="unit_master_code" class="form-control"
                        placeholder="Kosongkan jika sama dengan nama">
                </div>
                <div class="alert alert-danger d-none mb-0" id="unit-master-error"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-unit-master">Simpan Satuan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" for="supplier_master_name">Nama Supplier <span
                                class="text-danger">*</span></label>
                        <input type="text" id="supplier_master_name" class="form-control"
                            placeholder="Contoh: UTE Parts Jakarta">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="supplier_master_code">Kode</label>
                        <input type="text" id="supplier_master_code" class="form-control" placeholder="Opsional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="supplier_master_phone">Telepon</label>
                        <input type="text" id="supplier_master_phone" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="supplier_master_email">Email</label>
                        <input type="email" id="supplier_master_email" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="supplier_master_contact_person">PIC</label>
                        <input type="text" id="supplier_master_contact_person" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="supplier_master_address">Alamat</label>
                        <textarea id="supplier_master_address" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="supplier_master_notes">Catatan</label>
                        <textarea id="supplier_master_notes" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-danger d-none mb-0" id="supplier-master-error"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-supplier-master">Simpan Supplier</button>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
    <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="bx bx-x me-1"></i> Batal</a>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .nav-tabs .nav-link {
            font-weight: 600;
        }

        .select2-container .select2-selection--single {
            min-height: calc(2.25rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container .select2-selection--multiple {
            min-height: calc(2.25rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5rem;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
            right: 0.5rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #f2f3f5;
            border: 0;
            border-radius: 999px;
            padding: 0.2rem 1.35rem 0.2rem 0.55rem;
            position: relative;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            right: 0.45rem;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            margin: 0;
            padding: 0;
            color: #8592a3;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus {
            background: transparent;
            color: #566a7f;
        }

        .select2-dropdown {
            border-color: #d9dee3;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subCategorySelect = document.getElementById('sub_category_id');
            const categorySelect = document.getElementById('category_id');
            const brandSelect = document.getElementById('brand_id');
            const typeSelect = document.getElementById('product_type_ids');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const unitRowModalEl = document.getElementById('unitRowModal');
            const unitMasterModalEl = document.getElementById('unitMasterModal');
            const supplierMasterModalEl = document.getElementById('supplierMasterModal');
            const locationMasterModalEl = document.getElementById('locationMasterModal');
            const qualityMasterModalEl = document.getElementById('qualityMasterModal');
            const unitRowModal = unitRowModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(
                unitRowModalEl) : null;
            const unitMasterModal = unitMasterModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(
                unitMasterModalEl) : null;
            const supplierMasterModal = supplierMasterModalEl && typeof bootstrap !== 'undefined' ? new bootstrap
                .Modal(supplierMasterModalEl) : null;
            const locationMasterModal = locationMasterModalEl && typeof bootstrap !== 'undefined' ? new bootstrap
                .Modal(locationMasterModalEl) : null;
            const qualityMasterModal = qualityMasterModalEl && typeof bootstrap !== 'undefined' ? new bootstrap
                .Modal(qualityMasterModalEl) : null;
            let activeUnitSelect = null;
            let activeSupplierSelect = null;
            let activeQualitySelect = null;

            function initSelect2(scope = document) {
                if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
                    return;
                }

                $(scope).find('.js-select2-single').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    allowClear: true
                });

                $(scope).find('.js-select2-multiple').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    placeholder: 'Pilih tipe HP'
                });

                $(scope).find('.js-unit-select').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    tags: true,
                    placeholder: 'Pilih / ketik satuan'
                });

                $(scope).find('.js-supplier-select').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih supplier'
                });

                $(scope).find('.js-location-select').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih lokasi'
                });

                $(scope).find('.js-quality-select').not('.select2-hidden-accessible').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'Pilih quality'
                });
            }

            function filterSubCategories() {
                if (!subCategorySelect || !categorySelect) return;
                const categoryId = categorySelect.value;
                Array.from(subCategorySelect.options).forEach(option => {
                    if (!option.value) return option.hidden = false;
                    option.hidden = categoryId && option.dataset.categoryId !== categoryId;
                });
                if (subCategorySelect.selectedOptions[0]?.hidden) {
                    subCategorySelect.value = '';
                }
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $('#sub_category_id').trigger('change.select2');
                }
            }

            function filterProductTypes() {
                if (!brandSelect || !typeSelect) return;
                const brandId = brandSelect.value;
                Array.from(typeSelect.options).forEach(option => {
                    option.hidden = brandId && option.dataset.brandId !== brandId;
                });
                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $('#product_type_ids').trigger('change.select2');
                }
            }

            initSelect2();
            filterSubCategories();
            filterProductTypes();
            categorySelect?.addEventListener('change', filterSubCategories);
            brandSelect?.addEventListener('change', filterProductTypes);

            const purchasePriceInput = document.getElementById('purchase_price');
            const sellingPriceInput = document.getElementById('selling_price');
            const marginPercentInput = document.getElementById('selling_price_margin_percent');
            let syncingPrice = false;

            function parseNumber(value) {
                const number = parseFloat(String(value || '').replace(',', '.'));
                return Number.isFinite(number) ? number : 0;
            }

            function formatDecimal(value) {
                if (!Number.isFinite(value)) return '';
                return (Math.round(value * 100) / 100).toString();
            }

            function syncSellingPriceFromPercent() {
                if (syncingPrice) return;
                const purchasePrice = parseNumber(purchasePriceInput?.value);
                const marginPercent = parseNumber(marginPercentInput?.value);

                if (purchasePrice <= 0 || !marginPercentInput?.value) return;

                syncingPrice = true;
                sellingPriceInput.value = formatDecimal(purchasePrice + (purchasePrice * marginPercent / 100));
                syncingPrice = false;
            }

            function syncPercentFromSellingPrice() {
                if (syncingPrice) return;
                const purchasePrice = parseNumber(purchasePriceInput?.value);
                const sellingPrice = parseNumber(sellingPriceInput?.value);

                if (purchasePrice <= 0 || !sellingPriceInput?.value) return;

                syncingPrice = true;
                marginPercentInput.value = formatDecimal(((sellingPrice - purchasePrice) / purchasePrice) * 100);
                syncingPrice = false;
            }

            purchasePriceInput?.addEventListener('input', function() {
                if (marginPercentInput?.value) {
                    syncSellingPriceFromPercent();
                    return;
                }

                syncPercentFromSellingPrice();
            });
            marginPercentInput?.addEventListener('input', syncSellingPriceFromPercent);
            sellingPriceInput?.addEventListener('input', syncPercentFromSellingPrice);

            const mainBarcodeRows = document.getElementById('main-additional-barcode-rows');
            document.getElementById('add-main-barcode-row')?.addEventListener('click', function() {
                const index = document.querySelectorAll('[name^="barcodes["][name$="[barcode]"]').length;
                const wrapper = document.createElement('div');
                wrapper.className = 'input-group mb-2 main-additional-barcode-row';
                wrapper.innerHTML = `
                    <input type="hidden" name="barcodes[${index}][label]" value="Barcode Tambahan">
                    <input type="hidden" name="barcodes[${index}][unit_level]" value="1">
                    <input type="hidden" name="barcodes[${index}][is_primary]" value="0">
                    <input type="text" name="barcodes[${index}][barcode]" class="form-control js-scanner-input" placeholder="Barcode tambahan" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                    <button type="button" class="btn btn-outline-danger" data-remove-main-barcode>&times;</button>
                `;
                mainBarcodeRows?.appendChild(wrapper);
                bindScannerInputs(wrapper);
            });

            document.addEventListener('click', function(event) {
                if (event.target.matches('[data-remove-main-barcode]')) {
                    event.target.closest('.main-additional-barcode-row')?.remove();
                }
            });

            function bindScannerInputs(scope) {
                scope.querySelectorAll('.js-scanner-input').forEach(input => {
                    input.addEventListener('focus', function() {
                        input.select();
                    });
                });
            }

            bindScannerInputs(document);

            document.querySelectorAll('.product-image-input').forEach(input => {
                input.addEventListener('change', function(event) {
                    const targetId = input.dataset.previewTarget;
                    const preview = document.getElementById(targetId);
                    const [file] = event.target.files;
                    if (!preview || !file) return;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            });

            function bindRemoveButtons(scope) {
                scope.querySelectorAll('[data-remove-row]').forEach(button => {
                    button.onclick = function() {
                        const row = button.closest('.unit-row, .tier-row, .variant-row, .supplier-row');
                        if (!row) return;
                        if (row.classList.contains('supplier-row') || row.parentElement.children
                            .length > 1) {
                            row.remove();
                        }
                    };
                });
            }

            bindRemoveButtons(document);

            const templates = {
                unit: () => `
                    <div class="border rounded p-3 unit-row">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="fw-semibold">Satuan Level __LEVEL__</div>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-2"><label class="form-label">Level</label><input type="number" min="1" max="9" class="form-control" name="__NAME__[level]" value="__LEVEL__"></div>
                            <div class="col-md-4"><label class="form-label">Satuan</label><div class="d-flex gap-2"><select class="form-select js-unit-select" name="__NAME__[unit_name]"><option value="">Pilih / ketik satuan</option>@foreach ($unitMasters as $unitMaster)<option value="{{ $unitMaster->name }}">{{ $unitMaster->name }}</option>@endforeach</select><button type="button" class="btn btn-outline-secondary flex-shrink-0" data-open-unit-master-modal>+</button></div></div>
                            <div class="col-md-3"><label class="form-label">Isi / Konversi</label><input type="number" step="0.01" min="0.01" class="form-control" name="__NAME__[conversion_qty]" value="__CONVERSION_QTY__"></div>
                            <div class="col-md-3"><label class="form-label">Barcode Satuan</label><input type="text" class="form-control js-scanner-input" name="__NAME__[barcode]" value="__BARCODE__" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false"></div>
                            <div class="col-md-3"><label class="form-label">Harga Toko</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[price_toko]" value="__PRICE_TOKO__"></div>
                            <div class="col-md-3"><label class="form-label">Margin Toko</label><input type="number" step="0.01" class="form-control" name="__NAME__[margin_toko]" value="__MARGIN_TOKO__"></div>
                            <div class="col-md-3"><label class="form-label">Harga Partai</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[price_partai]" value="__PRICE_PARTAI__"></div>
                            <div class="col-md-3"><label class="form-label">Margin Partai</label><input type="number" step="0.01" class="form-control" name="__NAME__[margin_partai]" value="__MARGIN_PARTAI__"></div>
                            <div class="col-md-3"><label class="form-label">Harga Cabang</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[price_cabang]" value="__PRICE_CABANG__"></div>
                            <div class="col-md-3"><label class="form-label">Margin Cabang</label><input type="number" step="0.01" class="form-control" name="__NAME__[margin_cabang]" value="__MARGIN_CABANG__"></div>
                            <div class="col-md-3"><label class="form-label">Harga Lain</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[price_lain]" value="__PRICE_LAIN__"></div>
                            <div class="col-md-3"><label class="form-label">Margin Lain</label><input type="number" step="0.01" class="form-control" name="__NAME__[margin_lain]" value="__MARGIN_LAIN__"></div>
                            <div class="col-md-6"><label class="form-label">Label Barcode</label><input type="text" class="form-control" name="__NAME__[barcode_label]" value="__BARCODE_LABEL__"></div>
                        </div>
                    </div>`,
                tier: () => `
                    <div class="border rounded p-3 tier-row">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3"><label class="form-label">Level Satuan</label><input type="number" min="1" max="9" class="form-control" name="__NAME__[unit_level]" value="1"></div>
                            <div class="col-md-4"><label class="form-label">Minimal Qty</label><input type="number" min="1" class="form-control" name="__NAME__[min_qty]" value="1"></div>
                            <div class="col-md-4"><label class="form-label">Harga</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[price]"></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button></div>
                        </div>
                    </div>`,
                variant: () => `
                    <div class="border rounded p-3 variant-row">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-2"><label class="form-label">Ukuran</label><input type="text" class="form-control" name="__NAME__[size]"></div>
                            <div class="col-md-2"><label class="form-label">Warna</label><input type="text" class="form-control" name="__NAME__[color]"></div>
                            <div class="col-md-2"><label class="form-label">Stok</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[stock]"></div>
                            <div class="col-md-2"><label class="form-label">Harga</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[price]"></div>
                            <div class="col-md-3"><label class="form-label">Catatan</label><input type="text" class="form-control" name="__NAME__[notes]"></div>
                            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>&times;</button></div>
                        </div>
                    </div>`,
                supplier: () => `
                    <div class="border rounded p-3 supplier-row">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4"><label class="form-label">Supplier</label><div class="d-flex gap-2"><select class="form-select js-supplier-select" name="__NAME__[supplier_id]"><option value="">Pilih supplier</option>@foreach ($suppliers as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>@endforeach</select><button type="button" class="btn btn-outline-secondary flex-shrink-0" data-open-supplier-master-modal>+</button></div></div>
                            <div class="col-md-3"><label class="form-label">Kode di Supplier</label><input type="text" class="form-control" name="__NAME__[supplier_product_code]"></div>
                            <div class="col-md-3"><label class="form-label">Harga Beli</label><input type="number" step="0.01" min="0" class="form-control" name="__NAME__[last_purchase_price]"></div>
                            <div class="col-md-1"><div class="form-check"><input class="form-check-input" type="checkbox" name="__NAME__[is_primary]" value="1"><label class="form-check-label">Utama</label></div></div>
                            <div class="col-md-4"><label class="form-label">Catatan</label><input type="text" class="form-control" name="__NAME__[notes]"></div>
                            <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-outline-danger w-100" data-remove-row>&times;</button></div>
                        </div>
                    </div>`
            };

            function escapeAttribute(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('"', '&quot;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;');
            }

            function addRow(type, values = {}) {
                const container = document.getElementById(`${type}-rows`);
                if (!container) return;
                const index = container.children.length;
                const namePrefix = {
                    unit: `units[${index}]`,
                    tier: `price_tiers[${index}]`,
                    variant: `variants[${index}]`,
                    supplier: `suppliers[${index}]`
                } [type];
                const wrapper = document.createElement('div');
                wrapper.innerHTML = templates[type]()
                    .replaceAll('__NAME__', namePrefix)
                    .replaceAll('__LEVEL__', escapeAttribute(values.level ?? Math.max(index + 1, 1)))
                    .replaceAll('__CONVERSION_QTY__', escapeAttribute(values.conversion_qty ?? 1))
                    .replaceAll('__BARCODE__', escapeAttribute(values.barcode ?? ''))
                    .replaceAll('__PRICE_TOKO__', escapeAttribute(values.price_toko ?? ''))
                    .replaceAll('__MARGIN_TOKO__', escapeAttribute(values.margin_toko ?? ''))
                    .replaceAll('__PRICE_PARTAI__', escapeAttribute(values.price_partai ?? ''))
                    .replaceAll('__MARGIN_PARTAI__', escapeAttribute(values.margin_partai ?? ''))
                    .replaceAll('__PRICE_CABANG__', escapeAttribute(values.price_cabang ?? ''))
                    .replaceAll('__MARGIN_CABANG__', escapeAttribute(values.margin_cabang ?? ''))
                    .replaceAll('__PRICE_LAIN__', escapeAttribute(values.price_lain ?? ''))
                    .replaceAll('__MARGIN_LAIN__', escapeAttribute(values.margin_lain ?? ''))
                    .replaceAll('__BARCODE_LABEL__', escapeAttribute(values.barcode_label ?? ''))
                    .trim();
                const node = wrapper.firstElementChild;
                container.appendChild(node);
                initSelect2(node);
                if (type === 'unit' && values.unit_name) {
                    setUnitSelectValue(node.querySelector('.js-unit-select'), values.unit_name);
                }
                bindScannerInputs(node);
                bindRemoveButtons(node);
                bindUnitMasterButtons(node);
                bindSupplierMasterButtons(node);
            }

            document.querySelectorAll('[data-add-row]').forEach(button => {
                button.addEventListener('click', function() {
                    if (button.dataset.addRow === 'unit') {
                        openUnitRowModal();
                        return;
                    }
                    addRow(button.dataset.addRow);
                });
            });

            function getModalValue(id) {
                return document.getElementById(id)?.value ?? '';
            }

            function openUnitRowModal() {
                if (!unitRowModal) return;
                const nextLevel = document.getElementById('unit-rows')?.children.length + 1 || 1;
                document.getElementById('unit_modal_level').value = nextLevel;
                document.getElementById('unit_modal_conversion').value = 1;
                [
                    'unit_modal_barcode',
                    'unit_modal_price_toko',
                    'unit_modal_margin_toko',
                    'unit_modal_price_partai',
                    'unit_modal_margin_partai',
                    'unit_modal_price_cabang',
                    'unit_modal_margin_cabang',
                    'unit_modal_price_lain',
                    'unit_modal_margin_lain',
                    'unit_modal_barcode_label'
                ].forEach(id => document.getElementById(id).value = '');
                $('#unit_modal_name').val('').trigger('change');
                unitRowModal.show();
            }

            document.getElementById('save-unit-row')?.addEventListener('click', function() {
                const unitName = getModalValue('unit_modal_name');
                if (!unitName) {
                    document.getElementById('unit_modal_name')?.focus();
                    return;
                }

                addRow('unit', {
                    level: getModalValue('unit_modal_level') || 1,
                    unit_name: unitName,
                    conversion_qty: getModalValue('unit_modal_conversion') || 1,
                    barcode: getModalValue('unit_modal_barcode'),
                    price_toko: getModalValue('unit_modal_price_toko'),
                    margin_toko: getModalValue('unit_modal_margin_toko'),
                    price_partai: getModalValue('unit_modal_price_partai'),
                    margin_partai: getModalValue('unit_modal_margin_partai'),
                    price_cabang: getModalValue('unit_modal_price_cabang'),
                    margin_cabang: getModalValue('unit_modal_margin_cabang'),
                    price_lain: getModalValue('unit_modal_price_lain'),
                    margin_lain: getModalValue('unit_modal_margin_lain'),
                    barcode_label: getModalValue('unit_modal_barcode_label')
                });

                unitRowModal?.hide();
            });

            function bindUnitMasterButtons(scope) {
                scope.querySelectorAll('[data-open-unit-master-modal]').forEach(button => {
                    button.onclick = function() {
                        activeUnitSelect = button.closest('.d-flex')?.querySelector(
                            '.js-unit-select') || null;
                        document.getElementById('unit_master_name').value = '';
                        document.getElementById('unit_master_code').value = '';
                        document.getElementById('unit-master-error').classList.add('d-none');
                        unitMasterModal?.show();
                    };
                });
            }

            bindUnitMasterButtons(document);

            function bindSupplierMasterButtons(scope) {
                scope.querySelectorAll('[data-open-supplier-master-modal]').forEach(button => {
                    button.onclick = function() {
                        activeSupplierSelect = button.closest('.d-flex')?.querySelector(
                            '.js-supplier-select') || null;
                        [
                            'supplier_master_name',
                            'supplier_master_code',
                            'supplier_master_phone',
                            'supplier_master_email',
                            'supplier_master_contact_person',
                            'supplier_master_address',
                            'supplier_master_notes'
                        ].forEach(id => document.getElementById(id).value = '');
                        document.getElementById('supplier-master-error').classList.add('d-none');
                        supplierMasterModal?.show();
                    };
                });
            }

            bindSupplierMasterButtons(document);

            document.getElementById('open-quality-master-modal')?.addEventListener('click', function() {
                activeQualitySelect = document.getElementById('quality');
                document.getElementById('quality_master_name').value = '';
                document.getElementById('quality_master_code').value = '';
                document.getElementById('quality-master-error').classList.add('d-none');
                qualityMasterModal?.show();
            });

            function setUnitSelectValue(select, value) {
                if (!select || !value) return;

                const normalizedValue = String(value).toUpperCase();
                if (![...select.options].some(option => option.value === normalizedValue)) {
                    select.add(new Option(normalizedValue, normalizedValue, true, true));
                }

                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(select).val(normalizedValue).trigger('change');
                    return;
                }

                select.value = normalizedValue;
            }

            function addUnitOptionToAllSelects(unitName) {
                document.querySelectorAll('.js-unit-select').forEach(select => {
                    if (![...select.options].some(option => option.value === unitName)) {
                        select.add(new Option(unitName, unitName, false, false));
                    }
                });
            }

            document.getElementById('save-unit-master')?.addEventListener('click', async function() {
                const button = this;
                const errorBox = document.getElementById('unit-master-error');
                const name = document.getElementById('unit_master_name').value.trim();
                const code = document.getElementById('unit_master_code').value.trim();

                if (!name) {
                    errorBox.textContent = 'Nama satuan wajib diisi.';
                    errorBox.classList.remove('d-none');
                    return;
                }

                button.disabled = true;
                errorBox.classList.add('d-none');

                try {
                    const response = await fetch('{{ route('units.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name,
                            code,
                            is_active: 1
                        })
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        throw new Error(errors.name?.[0] || errors.code?.[0] || payload.message ||
                            'Satuan gagal disimpan.');
                    }

                    const unitName = payload.unit.name;
                    addUnitOptionToAllSelects(unitName);
                    setUnitSelectValue(activeUnitSelect, unitName);
                    unitMasterModal?.hide();
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('d-none');
                } finally {
                    button.disabled = false;
                }
            });

            function setSupplierSelectValue(select, supplier) {
                if (!select || !supplier?.id) return;

                const value = String(supplier.id);
                if (![...select.options].some(option => option.value === value)) {
                    select.add(new Option(supplier.name, value, true, true));
                }

                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(select).val(value).trigger('change');
                    return;
                }

                select.value = value;
            }

            function addSupplierOptionToAllSelects(supplier) {
                if (!supplier?.id) return;

                const value = String(supplier.id);
                document.querySelectorAll('.js-supplier-select').forEach(select => {
                    if (![...select.options].some(option => option.value === value)) {
                        select.add(new Option(supplier.name, value, false, false));
                    }
                });
            }

            function setQualitySelectValue(select, quality) {
                if (!select || !quality?.name) return;

                const value = String(quality.name);
                if (![...select.options].some(option => option.value === value)) {
                    select.add(new Option(quality.name, value, true, true));
                }

                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(select).val(value).trigger('change');
                    return;
                }

                select.value = value;
            }

            function addQualityOptionToAllSelects(quality) {
                if (!quality?.name) return;

                const value = String(quality.name);
                document.querySelectorAll('.js-quality-select').forEach(select => {
                    if (![...select.options].some(option => option.value === value)) {
                        select.add(new Option(quality.name, value, false, false));
                    }
                });
            }

            document.getElementById('save-supplier-master')?.addEventListener('click', async function() {
                const button = this;
                const errorBox = document.getElementById('supplier-master-error');
                const name = document.getElementById('supplier_master_name').value.trim();

                if (!name) {
                    errorBox.textContent = 'Nama supplier wajib diisi.';
                    errorBox.classList.remove('d-none');
                    return;
                }

                button.disabled = true;
                errorBox.classList.add('d-none');

                try {
                    const response = await fetch('{{ route('suppliers.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name,
                            code: document.getElementById('supplier_master_code').value
                                .trim(),
                            phone: document.getElementById('supplier_master_phone')
                                .value.trim(),
                            email: document.getElementById('supplier_master_email')
                                .value.trim(),
                            contact_person: document.getElementById(
                                'supplier_master_contact_person').value.trim(),
                            address: document.getElementById('supplier_master_address')
                                .value.trim(),
                            notes: document.getElementById('supplier_master_notes')
                                .value.trim(),
                            is_active: 1
                        })
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        throw new Error(errors.name?.[0] || errors.code?.[0] || errors.email?.[0] ||
                            payload.message || 'Supplier gagal disimpan.');
                    }

                    addSupplierOptionToAllSelects(payload.supplier);
                    setSupplierSelectValue(activeSupplierSelect, payload.supplier);
                    supplierMasterModal?.hide();
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('d-none');
                } finally {
                    button.disabled = false;
                }
            });

            document.getElementById('save-quality-master')?.addEventListener('click', async function() {
                const button = this;
                const errorBox = document.getElementById('quality-master-error');
                const name = document.getElementById('quality_master_name').value.trim();
                const code = document.getElementById('quality_master_code').value.trim();

                if (!name) {
                    errorBox.textContent = 'Nama quality wajib diisi.';
                    errorBox.classList.remove('d-none');
                    return;
                }

                button.disabled = true;
                errorBox.classList.add('d-none');

                try {
                    const response = await fetch('{{ route('product-qualities.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name,
                            code,
                            is_active: 1
                        })
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        throw new Error(errors.name?.[0] || errors.code?.[0] || payload.message ||
                            'Quality gagal disimpan.');
                    }

                    addQualityOptionToAllSelects(payload.quality);
                    setQualitySelectValue(activeQualitySelect, payload.quality);
                    qualityMasterModal?.hide();
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('d-none');
                } finally {
                    button.disabled = false;
                }
            });

            document.getElementById('open-location-master-modal')?.addEventListener('click', function() {
                document.getElementById('location_master_name').value = '';
                document.getElementById('location_master_code').value = '';
                document.getElementById('location_master_racks').value = '';
                document.getElementById('location-master-error').classList.add('d-none');
                locationMasterModal?.show();
            });

            const rackSelectEl = document.getElementById('default_location_rack_id');
            let rackOptionCache = [];

            function rebuildRackCache() {
                if (!rackSelectEl) return;
                rackOptionCache = [...rackSelectEl.options]
                    .filter(option => option.value)
                    .map(option => ({
                        value: String(option.value),
                        text: option.text,
                        locationId: String(option.dataset.locationId || ''),
                    }));
            }

            function renderRackOptionsForLocation(locationId) {
                if (!rackSelectEl) return;

                const prev = String(rackSelectEl.value || '');
                const items = rackOptionCache.filter(item => locationId === '' || item.locationId === locationId);

                // Rebuild options so Select2 doesn't show hidden/mismatched racks.
                rackSelectEl.options.length = 0;
                rackSelectEl.add(new Option('Pilih rak', '', false, false));
                items.forEach(item => {
                    const opt = new Option(item.text, item.value, false, false);
                    opt.dataset.locationId = item.locationId;
                    rackSelectEl.add(opt);
                });

                rackSelectEl.value = items.some(item => item.value === prev) ? prev : '';

                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(rackSelectEl).trigger('change');
                }
            }

            function filterDefaultRackOptions() {
                const locationId = String(document.getElementById('default_location_id')?.value || '');
                if (!rackSelectEl) return;
                if (rackOptionCache.length === 0) rebuildRackCache();
                renderRackOptionsForLocation(locationId);
            }

            function addLocationOption(location) {
                if (!location?.id) return;

                const select = document.getElementById('default_location_id');
                const rackSelect = document.getElementById('default_location_rack_id');
                const value = String(location.id);

                if (![...select.options].some(option => option.value === value)) {
                    select.add(new Option(location.name, value, true, true));
                }

                if (rackSelect && Array.isArray(location.racks)) {
                    location.racks.forEach(rack => {
                        const rackValue = String(rack.id);

                        if (![...rackSelect.options].some(option => option.value === rackValue)) {
                            const option = new Option(`${location.name} - ${rack.name}`, rackValue, false, false);
                            option.dataset.locationId = value;
                            rackSelect.add(option);
                            rackOptionCache.push({
                                value: rackValue,
                                text: option.text,
                                locationId: value,
                            });
                        }
                    });
                }

                if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                    $(select).val(value).trigger('change');
                    filterDefaultRackOptions();
                    return;
                }

                select.value = value;
                filterDefaultRackOptions();
            }

            $('#default_location_id').on('change', filterDefaultRackOptions);
            rebuildRackCache();
            filterDefaultRackOptions();

            document.getElementById('save-location-master')?.addEventListener('click', async function() {
                const button = this;
                const errorBox = document.getElementById('location-master-error');
                const name = document.getElementById('location_master_name').value.trim();
                const code = document.getElementById('location_master_code').value.trim();
                const racks = document.getElementById('location_master_racks').value
                    .split(/\r?\n|,/)
                    .map(item => item.trim())
                    .filter(Boolean);

                if (!name || !code) {
                    errorBox.textContent = 'Nama dan kode lokasi wajib diisi.';
                    errorBox.classList.remove('d-none');
                    return;
                }

                button.disabled = true;
                errorBox.classList.add('d-none');

                try {
                    const response = await fetch('{{ route('locations.store') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name,
                            code,
                            racks,
                            is_active: 1
                        })
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        const errors = payload.errors || {};
                        throw new Error(errors.name?.[0] || errors.code?.[0] || payload.message ||
                            'Lokasi gagal disimpan.');
                    }

                    addLocationOption(payload.location);
                    locationMasterModal?.hide();
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('d-none');
                } finally {
                    button.disabled = false;
                }
            });

            // --- Tabs: keep active tab on validation errors and focus invalid inputs ---
            const activeTabInput = document.getElementById('active_tab');
            const tabButtons = Array.from(document.querySelectorAll('button[data-bs-toggle="tab"]'));

            function showTabByTarget(target) {
                if (!target) return false;
                const btn = tabButtons.find(b => b.getAttribute('data-bs-target') === target);
                if (!btn || typeof bootstrap === 'undefined' || !bootstrap.Tab) return false;
                bootstrap.Tab.getOrCreateInstance(btn).show();
                return true;
            }

            function setActiveTab(target) {
                if (activeTabInput) activeTabInput.value = target || '';
                try {
                    localStorage.setItem('uteparts-product-form-tab', target || '');
                } catch (e) {}
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('shown.bs.tab', () => setActiveTab(btn.getAttribute('data-bs-target')));
            });

            // On load: if there is an invalid field, open its tab; else restore previous tab.
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                const pane = firstInvalid.closest('.tab-pane');
                if (pane?.id) {
                    showTabByTarget('#' + pane.id);
                }
            } else {
                const target = (activeTabInput?.value || '').trim() ||
                    (() => {
                        try { return (localStorage.getItem('uteparts-product-form-tab') || '').trim(); } catch (e) { return ''; }
                    })();
                if (target) showTabByTarget(target);
            }

            // HTML5 validation across hidden tab panes can be confusing; open the tab of the first invalid control.
            const formEl = document.querySelector('form');
            formEl?.addEventListener('submit', function(e) {
                // Let browser mark invalids, then redirect user to the right tab.
                const invalid = formEl.querySelector(':invalid');
                if (!invalid) return;
                e.preventDefault();
                const pane = invalid.closest('.tab-pane');
                if (pane?.id) {
                    showTabByTarget('#' + pane.id);
                    setActiveTab('#' + pane.id);
                }
                try { invalid.focus({ preventScroll: false }); } catch (err) {}
            });
        });
    </script>
@endpush

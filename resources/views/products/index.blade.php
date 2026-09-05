@extends('layouts.sneat')

@section('title', 'Produk')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $masterBarangLinks = [
                [
                    'label' => 'Kategori',
                    'route' => 'categories.index',
                    'permission' => 'master.categories.view',
                    'icon' => 'bx-category',
                    'class' => 'master-shortcut-blue',
                ],
                [
                    'label' => 'Sub Kategori',
                    'route' => 'sub-categories.index',
                    'permission' => 'master.sub_categories.view',
                    'icon' => 'bx-sitemap',
                    'class' => 'master-shortcut-cyan',
                ],
                [
                    'label' => 'Brand',
                    'route' => 'brands.index',
                    'permission' => 'master.brands.view',
                    'icon' => 'bx-purchase-tag',
                    'class' => 'master-shortcut-orange',
                ],
                [
                    'label' => 'Merek',
                    'route' => 'product-makers.index',
                    'permission' => 'master.products.view',
                    'icon' => 'bx-certification',
                    'class' => 'master-shortcut-yellow',
                ],
                [
                    'label' => 'Tipe HP',
                    'route' => 'product-types.index',
                    'permission' => 'master.product_types.view',
                    'icon' => 'bx-mobile',
                    'class' => 'master-shortcut-green',
                ],
                [
                    'label' => 'Cabang',
                    'route' => 'branches.index',
                    'permission' => 'master.branches.view',
                    'icon' => 'bx-store-alt',
                    'class' => 'master-shortcut-pink',
                ],
                [
                    'label' => 'Lokasi',
                    'route' => 'locations.index',
                    'permission' => 'master.locations.view',
                    'icon' => 'bx-map',
                    'class' => 'master-shortcut-red',
                ],
                [
                    'label' => 'Satuan',
                    'route' => 'units.index',
                    'permission' => 'master.products.view',
                    'icon' => 'bx-ruler',
                    'class' => 'master-shortcut-purple',
                ],
            ];
        @endphp

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Master Pendukung Barang</h5>
            </div>
            <div class="card-body master-shortcut-card-body">
                <div class="master-shortcut-row">
                    @foreach ($masterBarangLinks as $link)
                        @if (auth()->user()->hasPermission($link['permission']))
                            <a href="{{ route($link['route']) }}" class="master-shortcut {{ $link['class'] }}">
                                <span class="master-shortcut-icon">
                                    <i class="bx {{ $link['icon'] }}"></i>
                                </span>
                                <span class="master-shortcut-title">{{ $link['label'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="mb-3">
                    <h5 class="mb-0">Master Data Barang</h5>
                </div>
                <div class="d-flex flex-wrap gap-2 product-toolbar-actions">

                    <form id="product-barcode-print-form" action="{{ route('products.barcodes.print') }}" method="POST"
                        class="d-inline" target="_blank">
                        @csrf
                        <input type="hidden" name="scope" id="product-barcode-print-scope" value="selected">
                        <input type="hidden" name="selected_product_ids" id="barcode-print-selected-product-ids"
                            value="">
                        <input type="hidden" name="barcode_scope" id="barcode-print-barcode-scope" value="primary">
                        <input type="hidden" name="size_mode" id="barcode-print-size-mode" value="preset">
                        <input type="hidden" name="size_preset" id="barcode-print-size-preset" value="50x25">
                        <input type="hidden" name="custom_width_mm" id="barcode-print-custom-width" value="">
                        <input type="hidden" name="custom_height_mm" id="barcode-print-custom-height" value="">
                        <div id="product-barcode-print-filters"></div>
                        <button type="button" id="open-barcode-print-modal"
                            class="btn btn-outline-secondary btn-sm product-toolbar-btn">
                            <i class="bx bx-barcode me-1"></i> Cetak Barcode
                        </button>
                    </form>
                    <button type="button" id="share-selected-products"
                        class="btn btn-outline-primary btn-sm product-toolbar-btn">
                        <i class="bx bx-share-alt me-1"></i> Share Produk
                    </button>
                    <form id="product-export-form" action="{{ route('products.export') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="scope" id="product-export-scope" value="all">
                        <input type="hidden" name="selected_product_ids" id="selected-product-ids" value="">
                        <div id="product-export-filters"></div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm product-toolbar-btn dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-download me-1"></i> Export Produk
                                <span id="selected-products-count" class="badge bg-white text-primary ms-1">0</span>
                            </button>
                            <div class="dropdown-menu">
                                <button type="button" id="export-all-products" class="dropdown-item">
                                    <i class="bx bx-list-ul me-1"></i> Export Semua
                                </button>
                                <button type="button" id="export-selected-products" class="dropdown-item">
                                    <i class="bx bx-check-square me-1"></i> Export Pilihan
                                </button>
                            </div>
                        </div>
                    </form>
                    @if (auth()->user()->hasPermission('master.products.create'))
                        <a href="{{ route('products.import') }}"
                            class="btn btn-primary btn-sm product-toolbar-btn">
                            <i class="bx bx-upload me-1"></i> Import Produk
                        </a>
                        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm product-toolbar-btn">
                            <i class="bx bx-plus me-1"></i> Tambah Produk
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 mb-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h6 class="mb-0">Filter Produk</h6>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-self-md-start">
                            <button type="button" id="reset-product-filter" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-reset me-1"></i> Reset Filter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#productTableSettingsModal">
                                <i class="bx bx-slider-alt me-1"></i> Pengaturan Tabel
                            </button>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4 col-xl">
                            <label class="form-label" for="filter_category_id">Kategori</label>
                            <select id="filter_category_id" class="form-select product-filter" multiple
                                data-placeholder="Semua Kategori">
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label class="form-label" for="filter_sub_category_id">Sub Kategori</label>
                            <select id="filter_sub_category_id" class="form-select product-filter" multiple
                                data-placeholder="Semua Sub Kategori">
                                @foreach ($subCategories as $subCategory)
                                    <option value="{{ $subCategory->id }}"
                                        data-category-id="{{ $subCategory->category_id }}">{{ $subCategory->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label class="form-label" for="filter_brand_id">Brand</label>
                            <select id="filter_brand_id" class="form-select product-filter" multiple
                                data-placeholder="Semua Brand">
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label class="form-label" for="filter_product_maker_id">Merek</label>
                            <select id="filter_product_maker_id" class="form-select product-filter" multiple
                                data-placeholder="Semua Merek">
                                @foreach ($productMakers as $maker)
                                    <option value="{{ $maker->id }}">{{ $maker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-xl">
                            <label class="form-label" for="filter_product_type_id">Tipe HP</label>
                            <select id="filter_product_type_id" class="form-select product-filter" multiple
                                data-placeholder="Semua Tipe HP">
                                @foreach ($productTypes as $type)
                                    <option value="{{ $type->id }}" data-brand-id="{{ $type->brand_id }}">
                                        {{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive text-nowrap">
                    <table id="products-table" class="table table-striped">
                        <thead>
                            <tr id="products-table-head"></tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="productTableSettingsModal" tabindex="-1"
            aria-labelledby="productTableSettingsLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="productTableSettingsLabel">Pengaturan Tabel Produk</h5>
                            <small class="text-muted">Drag kolom untuk mengubah urutan, centang untuk menampilkan.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="product-table-column-list" class="product-column-list"></div>
                        <small id="product-table-settings-status" class="text-muted d-block mt-3"></small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="reset-product-table-settings" class="btn btn-outline-secondary">
                            Reset Tampilan
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" id="save-product-table-settings" class="btn btn-primary">
                            Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="shareProductsModal" tabindex="-1" aria-labelledby="shareProductsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="shareProductsModalLabel">Share Produk</h5>
                            <small class="text-muted">Bagikan link ini ke customer member.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Link Share</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="shareProductsUrl" readonly>
                            <button type="button" class="btn btn-outline-primary" id="copyShareProductsUrl"
                                title="Copy link">
                                <i class="bx bx-copy"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            Link akan membuka halaman website dan menampilkan produk yang dipilih saja.
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Teks Produk</label>
                            <div class="input-group">
                                <textarea class="form-control" id="shareProductsText" rows="5" readonly
                                    placeholder="Memuat teks produk..."></textarea>
                                <button type="button" class="btn btn-outline-primary" id="copyShareProductsText"
                                    title="Copy teks produk">
                                    <i class="bx bx-copy"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Format copy: nama produk, brand, tipe, merek/quality, dan harga.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <a href="#" target="_blank" class="btn btn-primary" id="openShareProductsUrl">
                            <i class="bx bx-link-external me-1"></i> Buka Link
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="barcodePrintModal" tabindex="-1" aria-labelledby="barcodePrintModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title" id="barcodePrintModalLabel">Cetak Barcode Produk</h5>
                            <small class="text-muted">Isi label: barcode, kode produk, dan nama produk.</small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="barcode_print_scope_select">Scope Produk</label>
                                <select id="barcode_print_scope_select" class="form-select">
                                    <option value="selected">Produk Terpilih</option>
                                    <option value="all">Semua Produk (ikut filter aktif)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="barcode_print_barcode_scope_select">Barcode yang
                                    Dicetak</label>
                                <select id="barcode_print_barcode_scope_select" class="form-select">
                                    <option value="primary">Barcode Utama Saja</option>
                                    <option value="all">Semua Barcode / Satuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="barcode_print_size_mode_select">Ukuran Label</label>
                                <select id="barcode_print_size_mode_select" class="form-select">
                                    <option value="preset">Preset</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="barcode_print_preset_wrap">
                                <label class="form-label" for="barcode_print_size_preset_select">Preset</label>
                                <select id="barcode_print_size_preset_select" class="form-select">
                                    <option value="50x25">50 x 25 mm</option>
                                    <option value="58x30">58 x 30 mm</option>
                                    <option value="80x38">80 x 38 mm</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-none" id="barcode_print_custom_width_wrap">
                                <label class="form-label" for="barcode_print_custom_width_input">Lebar (mm)</label>
                                <input type="number" min="20" max="150" step="0.1"
                                    id="barcode_print_custom_width_input" class="form-control" value="50">
                            </div>
                            <div class="col-md-6 d-none" id="barcode_print_custom_height_wrap">
                                <label class="form-label" for="barcode_print_custom_height_input">Tinggi (mm)</label>
                                <input type="number" min="15" max="100" step="0.1"
                                    id="barcode_print_custom_height_input" class="form-control" value="25">
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small" id="barcodePrintSelectedHint">
                            Pilih produk terlebih dahulu jika ingin cetak produk terpilih.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="submit-barcode-print">
                            <i class="bx bx-printer me-1"></i> Cetak
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/colreorder/2.1.2/css/colReorder.dataTables.min.css" rel="stylesheet">
    <style>
        .product-toolbar-actions {
            align-items: center;
            gap: 0.5rem !important;
        }

        .product-toolbar-actions .btn,
        .product-toolbar-actions .btn-group {
            flex: 0 0 auto;
            white-space: nowrap;
        }

        .product-toolbar-btn {
            min-height: 34px;
            padding: 0.45rem 0.85rem;
            border-radius: 0.6rem;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .product-toolbar-actions .btn-group .btn {
            min-height: 34px;
        }

        .product-toolbar-actions .badge {
            font-size: 0.76rem;
            min-width: 1.55rem;
        }

        @media (max-width: 767.98px) {
            .product-toolbar-actions {
                gap: 0.45rem !important;
            }

            .product-toolbar-btn,
            .product-toolbar-actions .btn-group .btn {
                width: auto;
                padding: 0.42rem 0.72rem;
                font-size: 0.84rem;
            }
        }

        .select2-container .select2-selection--single {
            min-height: calc(2.25rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5rem;
            padding-left: 0;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2.25rem + 2px);
            right: 0.5rem;
        }

        .select2-dropdown {
            border-color: #d9dee3;
        }

        .select2-container .select2-selection--multiple {
            min-height: calc(2.25rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.2rem 2rem 0.2rem 0.5rem;
            position: relative;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #f2f3f5;
            border: 0;
            border-radius: 999px;
            padding: 0.12rem 1.35rem 0.12rem 0.55rem;
            font-size: 0.82rem;
            margin-top: 0.18rem;
            position: relative;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__clear {
            position: absolute;
            right: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            margin: 0;
            font-size: 1.1rem;
            line-height: 1;
            color: #8592a3;
            z-index: 2;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            margin-top: 0.25rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            right: 0.45rem;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            color: #8592a3;
            font-size: 1rem;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus {
            background: transparent;
            color: #566a7f;
        }

        .master-shortcut-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 8px;
            align-items: stretch;
            overflow-x: auto;
            overflow-y: visible;
            padding: 6px 2px 10px;
        }

        .master-shortcut-card-body {
            overflow: visible;
            padding-top: 0.9rem;
        }

        .master-shortcut {
            --shortcut-bg: #eef2ff;
            --shortcut-color: #4f46e5;
            --shortcut-border: rgba(79, 70, 229, 0.18);
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            flex: 1 0 0;
            min-width: 106px;
            min-height: 38px;
            gap: 5px;
            color: var(--shortcut-color);
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.9), transparent 42%),
                var(--shortcut-bg);
            border: 1px solid var(--shortcut-border);
            border-radius: 10px;
            padding: 5px 8px;
            text-decoration: none;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .master-shortcut:hover {
            color: var(--shortcut-color);
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--shortcut-color) 38%, transparent);
            box-shadow: 0 8px 18px rgba(35, 38, 59, 0.12);
        }

        .master-shortcut-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 7px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, 0.78);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7);
        }

        .master-shortcut-icon i {
            font-size: 0.82rem;
        }

        .master-shortcut-title {
            color: #20242c;
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }

        @media (max-width: 991.98px) {
            .master-shortcut {
                flex: 0 0 106px;
            }
        }

        .master-shortcut-blue {
            --shortcut-bg: #eaf1ff;
            --shortcut-color: #2563eb;
            --shortcut-border: rgba(37, 99, 235, 0.18);
        }

        .master-shortcut-cyan {
            --shortcut-bg: #e6fbff;
            --shortcut-color: #0891b2;
            --shortcut-border: rgba(8, 145, 178, 0.18);
        }

        .master-shortcut-orange {
            --shortcut-bg: #fff3df;
            --shortcut-color: #f97316;
            --shortcut-border: rgba(249, 115, 22, 0.2);
        }

        .master-shortcut-green {
            --shortcut-bg: #e9fbef;
            --shortcut-color: #16a34a;
            --shortcut-border: rgba(22, 163, 74, 0.18);
        }

        .master-shortcut-yellow {
            --shortcut-bg: #fff9db;
            --shortcut-color: #ca8a04;
            --shortcut-border: rgba(202, 138, 4, 0.2);
        }

        .master-shortcut-red {
            --shortcut-bg: #fff0f0;
            --shortcut-color: #dc2626;
            --shortcut-border: rgba(220, 38, 38, 0.18);
        }

        .master-shortcut-purple {
            --shortcut-bg: #f5edff;
            --shortcut-color: #7c3aed;
            --shortcut-border: rgba(124, 58, 237, 0.18);
        }

        .product-cell-action .dropdown-toggle::after {
            margin-left: 0.35rem;
            vertical-align: 0.12em;
            opacity: 0.45;
        }

        .product-cell-action .dropdown-toggle:hover {
            color: var(--bs-primary) !important;
        }

        #products-table thead th {
            cursor: grab;
        }

        #products-table thead th.dt-orderable-none {
            cursor: grab;
        }

        .product-column-list {
            display: grid;
            gap: 0.55rem;
        }

        .product-column-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid #d9dee3;
            border-radius: 0.65rem;
            padding: 0.65rem 0.75rem;
            background: #fff;
            cursor: grab;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
        }

        .product-column-item:hover {
            border-color: rgba(var(--bs-primary-rgb), 0.45);
            box-shadow: 0 0.25rem 0.75rem rgba(67, 89, 113, 0.12);
        }

        .product-column-item.is-dragging {
            opacity: 0.65;
            transform: scale(0.99);
        }

        .product-column-drag {
            display: inline-flex;
            width: 1.6rem;
            height: 1.6rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.45rem;
            color: #697a8d;
            background: #f5f5f9;
            flex-shrink: 0;
        }

        .product-column-title {
            flex: 1;
            font-weight: 600;
            color: #384551;
        }

        .product-column-item .form-check-input {
            margin: 0;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/colreorder/2.1.2/js/dataTables.colReorder.min.js"></script>
    <script>
        $(function() {
            $('.product-filter').select2({
                width: '100%',
                allowClear: true,
                placeholder: function() {
                    return $(this).data('placeholder');
                }
            });

            const selectedProductIds = new Set();
            const savedTablePreference = @json($tablePreference?->preferences ?? null);
            const tablePreferenceUrl = '{{ route('table-preferences.store', 'products') }}';
            const websiteProductsUrl = '{{ route('website.products.index') }}';
            const productShareTextUrl = '{{ route('products.share-text') }}';
            let table = null;
            let activeColumns = [];
            let initialColumns = [];

            const productColumns = [{
                    key: 'select_checkbox',
                    title: '<input type="checkbox" id="select-products-page" class="form-check-input">',
                    settingTitle: 'Pilih Produk',
                    data: 'select_checkbox',
                    name: 'select_checkbox',
                    orderable: false,
                    searchable: false,
                    width: '42px'
                },
                {
                    key: 'image_preview',
                    title: 'Foto',
                    settingTitle: 'Foto',
                    data: 'image_preview',
                    name: 'legacy_image_path',
                    orderable: false,
                    searchable: false
                },
                {
                    key: 'product_code_action',
                    title: 'Kode',
                    settingTitle: 'Kode',
                    data: 'product_code_action',
                    name: 'product_code'
                },
                {
                    key: 'name_action',
                    title: 'Nama',
                    settingTitle: 'Nama',
                    data: 'name_action',
                    name: 'name'
                },
                {
                    key: 'category_label',
                    title: 'Kategori',
                    settingTitle: 'Kategori',
                    data: 'category_label',
                    name: 'category_id',
                    orderable: false
                },
                {
                    key: 'brand_label',
                    title: 'Brand',
                    settingTitle: 'Brand',
                    data: 'brand_label',
                    name: 'brand_id',
                    orderable: false,
                    searchable: false
                },
                {
                    key: 'product_types_label',
                    title: 'Tipe HP',
                    settingTitle: 'Tipe HP',
                    data: 'product_types_label',
                    name: 'product_types',
                    orderable: false,
                    searchable: false
                },
                {
                    key: 'maker_label',
                    title: 'Merek',
                    settingTitle: 'Merek',
                    data: 'maker_label',
                    name: 'product_maker_id',
                    orderable: false
                },
                {
                    key: 'barcode_label',
                    title: 'Barcode',
                    settingTitle: 'Barcode',
                    data: 'barcode_label',
                    name: 'barcode',
                    orderable: false
                },
                {
                    key: 'selling_price_formatted',
                    title: 'Harga Jual',
                    settingTitle: 'Harga Jual',
                    data: 'selling_price_formatted',
                    name: 'selling_price'
                },
                {
                    key: 'stock_label',
                    title: 'Stok',
                    settingTitle: 'Stok',
                    data: 'stock_label',
                    name: 'stock_global'
                },
                {
                    key: 'status_badge',
                    title: 'Status',
                    settingTitle: 'Status',
                    data: 'status_badge',
                    name: 'is_active',
                    orderable: false,
                    searchable: false
                },
                {
                    key: 'action',
                    title: 'Aksi',
                    settingTitle: 'Aksi',
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ];

            let currentTablePreference = normalizeTablePreference(savedTablePreference);

            function normalizeTablePreference(preference) {
                const defaultOrder = productColumns.map(column => column.key);
                const knownKeys = new Set(defaultOrder);
                const order = Array.isArray(preference?.column_order) ?
                    preference.column_order.filter(key => knownKeys.has(key)) : [];

                defaultOrder.forEach(key => {
                    if (!order.includes(key)) {
                        order.push(key);
                    }
                });

                return {
                    column_order: order,
                    column_visibility: preference?.column_visibility || {}
                };
            }

            function getOrderedColumns() {
                return currentTablePreference.column_order
                    .map(key => productColumns.find(column => column.key === key))
                    .filter(Boolean);
            }

            function isColumnVisible(column) {
                return currentTablePreference.column_visibility[column.key] !== false;
            }

            function renderTableHead(columns = getOrderedColumns()) {
                const head = $('#products-table-head');
                head.empty();

                columns.forEach(column => {
                    const th = $('<th>').html(column.title);

                    if (column.width) {
                        th.css('width', column.width);
                    }

                    head.append(th);
                });
            }

            function buildDataTableColumns(columns = getOrderedColumns()) {
                return columns.map(column => ({
                    data: column.data,
                    name: column.name,
                    orderable: column.orderable ?? true,
                    searchable: column.searchable ?? true,
                    visible: isColumnVisible(column)
                }));
            }

            let isRebuildingTable = false;

            function initProductTable() {
                isRebuildingTable = true;

                if ($.fn.DataTable.isDataTable('#products-table')) {
                    table.destroy();
                    $('#products-table tbody').remove();
                }

                activeColumns = getOrderedColumns();
                initialColumns = [...activeColumns];
                renderTableHead(activeColumns);

                const tableConfig = {
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('products.data') }}',
                        data: function(data) {
                            data.category_id = $('#filter_category_id').val();
                            data.sub_category_id = $('#filter_sub_category_id').val();
                            data.brand_id = $('#filter_brand_id').val();
                            data.product_maker_id = $('#filter_product_maker_id').val();
                            data.product_type_id = $('#filter_product_type_id').val();
                        }
                    },
                    columns: buildDataTableColumns(activeColumns),
                    drawCallback: function() {
                        $('.product-export-checkbox').each(function() {
                            $(this).prop('checked', selectedProductIds.has(String($(this).val())));
                        });
                        syncSelectAllCheckbox();
                    },
                    dom: 'lBfrtip',
                    buttons: ['copy', 'csv', 'pdf', 'print', 'colvis']
                };

                if (isColReorderAvailable()) {
                    tableConfig.colReorder = true;
                }

                table = $('#products-table').DataTable(tableConfig);

                table.on('column-visibility.dt', function() {
                    if (isRebuildingTable) {
                        return;
                    }

                    syncVisibilityFromDataTable();
                    scheduleAutoSaveTablePreference();
                });

                table.on('column-reorder.dt columns-reordered.dt', function() {
                    if (isRebuildingTable) {
                        return;
                    }

                    syncOrderFromDataTable();
                    syncVisibilityFromDataTable();
                    scheduleAutoSaveTablePreference();
                });

                setTimeout(function() {
                    isRebuildingTable = false;
                }, 0);
            }

            function syncVisibilityFromDataTable() {
                const visibility = {};

                // When ColReorder is enabled, DataTables column indexes are "original",
                // while activeColumns represents "current" order. We must map correctly.
                if (table?.colReorder && typeof table.colReorder.order === 'function') {
                    const order = table.colReorder.order(); // current position -> original index

                    activeColumns.forEach((column, currentPos) => {
                        const originalIndex = order[currentPos];
                        if (!column || originalIndex === undefined) return;
                        visibility[column.key] = table.column(originalIndex).visible();
                    });
                } else {
                    table.columns().every(function(index) {
                        const column = activeColumns[index];

                        if (column) {
                            visibility[column.key] = this.visible();
                        }
                    });
                }

                currentTablePreference.column_visibility = visibility;
            }

            function syncOrderFromDataTable() {
                if (!table?.colReorder || typeof table.colReorder.order !== 'function') {
                    return;
                }

                const currentOrder = table.colReorder.order();
                const reorderedColumns = currentOrder
                    .map(index => initialColumns[index])
                    .filter(Boolean);

                if (reorderedColumns.length !== activeColumns.length) {
                    return;
                }

                activeColumns = reorderedColumns;
                currentTablePreference.column_order = activeColumns.map(column => column.key);
            }

            function isColReorderAvailable() {
                return Boolean($.fn.dataTable?.ColReorder || window.DataTable?.ColReorder);
            }

            function saveTablePreference(showStatus = true) {
                if (showStatus) {
                    $('#product-table-settings-status').text('Menyimpan pengaturan...');
                }

                return $.ajax({
                    url: tablePreferenceUrl,
                    method: 'PUT',
                    data: JSON.stringify({
                        preferences: currentTablePreference
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    }
                }).done(function(response) {
                    if (response.preferences) {
                        currentTablePreference = normalizeTablePreference(response.preferences);
                    }

                    if (showStatus) {
                        $('#product-table-settings-status').text('Pengaturan tersimpan.');
                    }
                }).fail(function(xhr) {
                    const message = xhr.responseJSON?.message ||
                        'Pengaturan belum tersimpan. Cek migration table_preferences.';

                    if (showStatus) {
                        $('#product-table-settings-status').text(message);
                        return;
                    }

                    // Autosave failures should not be silent during development.
                    if (window.console) {
                        console.warn('Gagal autosave pengaturan tabel produk:', message);
                    }
                });
            }

            let autoSaveTimer = null;
            let autoSaveRequest = null;
            let manualSaveInProgress = false;

            function scheduleAutoSaveTablePreference() {
                if (manualSaveInProgress) {
                    return;
                }

                clearTimeout(autoSaveTimer);

                autoSaveTimer = setTimeout(function() {
                    if (!table) return;

                    if (autoSaveRequest && typeof autoSaveRequest.abort === 'function') {
                        autoSaveRequest.abort();
                    }

                    // Ensure preference is consistent with the current DataTables state.
                    syncOrderFromDataTable();
                    syncVisibilityFromDataTable();

                    autoSaveRequest = saveTablePreference(false);
                }, 450);
            }

            function renderColumnSettings() {
                const wrapper = $('#product-table-column-list');
                wrapper.empty();

                getOrderedColumns().forEach(column => {
                    wrapper.append(`
                        <div class="product-column-item" draggable="true" data-key="${column.key}">
                            <span class="product-column-drag"><i class="bx bx-grid-vertical"></i></span>
                            <span class="product-column-title">${column.settingTitle}</span>
                            <input type="checkbox" class="form-check-input product-column-visible" ${isColumnVisible(column) ? 'checked' : ''}>
                        </div>
                    `);
                });
            }

            initProductTable();

            function updateSelectedCount() {
                $('#selected-products-count').text(selectedProductIds.size);
                $('#selected-product-ids').val(Array.from(selectedProductIds).join(','));
            }

            function syncSelectAllCheckbox() {
                const checkboxes = $('.product-export-checkbox');
                const checked = checkboxes.filter(':checked').length;
                $('#select-products-page')
                    .prop('checked', checkboxes.length > 0 && checked === checkboxes.length)
                    .prop('indeterminate', checked > 0 && checked < checkboxes.length);
            }

            function appendProductFilters(wrapperSelector) {
                const wrapper = $(wrapperSelector);
                wrapper.empty();

                const filters = {
                    category_id: $('#filter_category_id').val() || [],
                    sub_category_id: $('#filter_sub_category_id').val() || [],
                    brand_id: $('#filter_brand_id').val() || [],
                    product_maker_id: $('#filter_product_maker_id').val() || [],
                    product_type_id: $('#filter_product_type_id').val() || []
                };

                Object.entries(filters).forEach(([name, values]) => {
                    values.forEach(value => {
                        wrapper.append(`<input type="hidden" name="${name}[]" value="${value}">`);
                    });
                });
            }

            function appendExportFilters() {
                appendProductFilters('#product-export-filters');
            }

            function appendBarcodePrintFilters() {
                appendProductFilters('#product-barcode-print-filters');
            }

            $('#products-table').on('change', '.product-export-checkbox', function() {
                const id = String($(this).val());

                if ($(this).is(':checked')) {
                    selectedProductIds.add(id);
                } else {
                    selectedProductIds.delete(id);
                }

                updateSelectedCount();
                syncSelectAllCheckbox();
            });

            $('#products-table').on('change', '#select-products-page', function() {
                const checked = $(this).is(':checked');

                $('.product-export-checkbox').each(function() {
                    const id = String($(this).val());
                    $(this).prop('checked', checked);

                    if (checked) {
                        selectedProductIds.add(id);
                    } else {
                        selectedProductIds.delete(id);
                    }
                });

                updateSelectedCount();
                syncSelectAllCheckbox();
            });

            $('#export-selected-products').on('click', function() {
                if (selectedProductIds.size === 0) {
                    alert('Pilih minimal 1 produk untuk export.');
                    return;
                }

                $('#product-export-scope').val('selected');
                updateSelectedCount();
                appendExportFilters();
                $('#product-export-form').trigger('submit');
            });

            $('#export-all-products').on('click', function() {
                $('#product-export-scope').val('all');
                appendExportFilters();
                $('#product-export-form').trigger('submit');
            });

            const barcodePrintModalEl = document.getElementById('barcodePrintModal');
            const barcodePrintModal = barcodePrintModalEl && typeof bootstrap !== 'undefined' ? bootstrap.Modal
                .getOrCreateInstance(barcodePrintModalEl) : null;
            const barcodePrintScopeSelect = document.getElementById('barcode_print_scope_select');
            const barcodePrintHint = document.getElementById('barcodePrintSelectedHint');
            const barcodePrintSizeModeSelect = document.getElementById('barcode_print_size_mode_select');
            const barcodePrintPresetWrap = document.getElementById('barcode_print_preset_wrap');
            const barcodePrintCustomWidthWrap = document.getElementById('barcode_print_custom_width_wrap');
            const barcodePrintCustomHeightWrap = document.getElementById('barcode_print_custom_height_wrap');

            function updateBarcodePrintScopeUI() {
                if (!barcodePrintScopeSelect || !barcodePrintHint) return;

                if (barcodePrintScopeSelect.value === 'selected') {
                    barcodePrintHint.classList.toggle('alert-warning', selectedProductIds.size === 0);
                    barcodePrintHint.classList.toggle('alert-info', selectedProductIds.size > 0);
                    barcodePrintHint.textContent = selectedProductIds.size === 0 ?
                        'Pilih produk terlebih dahulu jika ingin cetak produk terpilih.' :
                        `${selectedProductIds.size} produk terpilih siap dicetak.`;
                    return;
                }

                barcodePrintHint.classList.remove('alert-warning');
                barcodePrintHint.classList.add('alert-info');
                barcodePrintHint.textContent = 'Scope semua produk akan mengikuti filter yang sedang aktif.';
            }

            function updateBarcodePrintSizeUI() {
                const isCustom = barcodePrintSizeModeSelect?.value === 'custom';
                barcodePrintPresetWrap?.classList.toggle('d-none', isCustom);
                barcodePrintCustomWidthWrap?.classList.toggle('d-none', !isCustom);
                barcodePrintCustomHeightWrap?.classList.toggle('d-none', !isCustom);
            }

            $('#open-barcode-print-modal').on('click', function() {
                updateSelectedCount();
                updateBarcodePrintScopeUI();
                updateBarcodePrintSizeUI();
                barcodePrintModal?.show();
            });

            barcodePrintScopeSelect?.addEventListener('change', updateBarcodePrintScopeUI);
            barcodePrintSizeModeSelect?.addEventListener('change', updateBarcodePrintSizeUI);

            $('#submit-barcode-print').on('click', function() {
                const scope = $('#barcode_print_scope_select').val();

                if (scope === 'selected' && selectedProductIds.size === 0) {
                    alert('Pilih minimal 1 produk untuk cetak barcode.');
                    return;
                }

                $('#product-barcode-print-scope').val(scope);
                $('#barcode-print-selected-product-ids').val(Array.from(selectedProductIds).join(','));
                $('#barcode-print-barcode-scope').val($('#barcode_print_barcode_scope_select').val());
                $('#barcode-print-size-mode').val($('#barcode_print_size_mode_select').val());
                $('#barcode-print-size-preset').val($('#barcode_print_size_preset_select').val());
                $('#barcode-print-custom-width').val($('#barcode_print_custom_width_input').val());
                $('#barcode-print-custom-height').val($('#barcode_print_custom_height_input').val());
                appendBarcodePrintFilters();
                $('#product-barcode-print-form').trigger('submit');
                barcodePrintModal?.hide();
            });

            $('#share-selected-products').on('click', function() {
                if (selectedProductIds.size === 0) {
                    alert('Pilih minimal 1 produk untuk dibagikan ke customer member.');
                    return;
                }

                const selectedIds = Array.from(selectedProductIds);
                const payload = btoa(Array.from(selectedProductIds).join(','))
                    .replace(/\+/g, '-')
                    .replace(/\//g, '_')
                    .replace(/=+$/, '');
                const url = `${websiteProductsUrl}?share=${encodeURIComponent(payload)}`;
                const modalEl = document.getElementById('shareProductsModal');
                const urlInput = document.getElementById('shareProductsUrl');
                const textInput = document.getElementById('shareProductsText');
                const openBtn = document.getElementById('openShareProductsUrl');
                const copyBtn = document.getElementById('copyShareProductsUrl');
                const copyTextBtn = document.getElementById('copyShareProductsText');

                if (urlInput) urlInput.value = url;
                if (textInput) textInput.value = 'Memuat teks produk...';
                if (openBtn) openBtn.href = url;
                if (copyBtn) {
                    copyBtn.classList.remove('btn-success');
                    copyBtn.classList.add('btn-outline-primary');
                    copyBtn.innerHTML = '<i class="bx bx-copy"></i>';
                    copyBtn.dataset.copied = '0';
                }
                if (copyTextBtn) {
                    copyTextBtn.classList.remove('btn-success');
                    copyTextBtn.classList.add('btn-outline-primary');
                    copyTextBtn.innerHTML = '<i class="bx bx-copy"></i>';
                    copyTextBtn.dataset.copied = '0';
                }

                bootstrap.Modal.getOrCreateInstance(modalEl).show();

                fetch(productShareTextUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify({
                        product_ids: selectedIds
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Gagal membuat teks produk.');
                        }

                        return response.json();
                    })
                    .then(payload => {
                        if (textInput) {
                            textInput.value = payload.text || '';
                        }
                    })
                    .catch(() => {
                        if (textInput) {
                            textInput.value = 'Gagal memuat teks produk. Coba ulangi share produk.';
                        }
                    });
            });

            (function initShareCopy() {
                const copyBtn = document.getElementById('copyShareProductsUrl');
                const urlInput = document.getElementById('shareProductsUrl');
                const copyTextBtn = document.getElementById('copyShareProductsText');
                const textInput = document.getElementById('shareProductsText');

                async function doCopy(text, fallbackInput) {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(text);
                        return;
                    }
                    fallbackInput.focus();
                    fallbackInput.select();
                    document.execCommand('copy');
                }

                function markCopied(button) {
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-success');
                    button.innerHTML = '<i class="bx bx-check"></i>';
                    button.dataset.copied = '1';
                }

                copyBtn?.addEventListener('click', async function() {
                    const url = (urlInput.value || '').trim();
                    if (!url) return;
                    try {
                        await doCopy(url, urlInput);
                        markCopied(copyBtn);
                    } catch (err) {
                        alert('Gagal menyalin link. Silakan salin manual.');
                    }
                });

                copyTextBtn?.addEventListener('click', async function() {
                    const text = (textInput.value || '').trim();
                    if (!text || text === 'Memuat teks produk...') return;
                    try {
                        await doCopy(text, textInput);
                        markCopied(copyTextBtn);
                    } catch (err) {
                        alert('Gagal menyalin teks produk. Silakan salin manual.');
                    }
                });
            })();

            function filterDependentOptions(parentSelector, childSelector, dataKey) {
                const parentValue = $(parentSelector).val();
                const parentValues = Array.isArray(parentValue) ? parentValue.map(String) : (parentValue ? [String(
                    parentValue)] : []);
                const child = $(childSelector);

                child.find('option').each(function() {
                    const option = $(this);
                    const optionValue = option.val();

                    option.prop('hidden', parentValues.length > 0 && !parentValues.includes(String(option
                        .data(dataKey))));
                });

                const selectedValues = child.val() || [];
                const validValues = selectedValues.filter(value => !child.find(`option[value="${value}"]`).prop(
                    'hidden'));

                if (selectedValues.length !== validValues.length) {
                    child.val(validValues);
                }

                child.trigger('change.select2');
            }

            $('#filter_category_id').on('change', function() {
                filterDependentOptions('#filter_category_id', '#filter_sub_category_id', 'category-id');
                table.ajax.reload();
            });

            $('#filter_brand_id').on('change', function() {
                filterDependentOptions('#filter_brand_id', '#filter_product_type_id', 'brand-id');
                table.ajax.reload();
            });

            $('#filter_sub_category_id, #filter_product_maker_id, #filter_product_type_id').on(
                'change select2:clear select2:unselect',
                function() {
                    table.ajax.reload();
                });

            $('#filter_category_id, #filter_brand_id').on('select2:clear select2:unselect', function() {
                if (this.id === 'filter_category_id') {
                    filterDependentOptions('#filter_category_id', '#filter_sub_category_id', 'category-id');
                }

                if (this.id === 'filter_brand_id') {
                    filterDependentOptions('#filter_brand_id', '#filter_product_type_id', 'brand-id');
                }

                table.ajax.reload();
            });

            $('#reset-product-filter').on('click', function() {
                $('.product-filter').val(null).trigger('change.select2');
                filterDependentOptions('#filter_category_id', '#filter_sub_category_id', 'category-id');
                filterDependentOptions('#filter_brand_id', '#filter_product_type_id', 'brand-id');
                table.ajax.reload();
            });

            $('#productTableSettingsModal').on('show.bs.modal', function() {
                // Keep the settings list consistent with the current DataTables state
                // (important when columns have been reordered).
                if (table) {
                    syncOrderFromDataTable();
                    syncVisibilityFromDataTable();
                }
                renderColumnSettings();
                $('#product-table-settings-status').text('');
            });

            let tableSettingsDirty = false;

            function syncPreferenceFromSettingsList() {
                const columnOrder = [];
                const visibility = {};

                $('#product-table-column-list .product-column-item').each(function() {
                    const key = $(this).data('key');
                    columnOrder.push(key);
                    visibility[key] = $(this).find('.product-column-visible').is(':checked');
                });

                currentTablePreference = normalizeTablePreference({
                    column_order: columnOrder,
                    column_visibility: visibility
                });
            }

            let draggedColumnItem = null;

            $('#product-table-column-list')
                .on('dragstart', '.product-column-item', function(event) {
                    draggedColumnItem = this;
                    $(this).addClass('is-dragging');
                    event.originalEvent.dataTransfer.effectAllowed = 'move';
                })
                .on('dragend', '.product-column-item', function() {
                    $(this).removeClass('is-dragging');
                    draggedColumnItem = null;
                    tableSettingsDirty = true;
                    $('#product-table-settings-status').text('Perubahan akan otomatis tersimpan.');
                })
                .on('dragover', '.product-column-item', function(event) {
                    event.preventDefault();

                    if (!draggedColumnItem || draggedColumnItem === this) {
                        return;
                    }

                    const rect = this.getBoundingClientRect();
                    const shouldPlaceAfter = event.originalEvent.clientY > rect.top + rect.height / 2;

                    if (shouldPlaceAfter) {
                        $(this).after(draggedColumnItem);
                    } else {
                        $(this).before(draggedColumnItem);
                    }

                    tableSettingsDirty = true;
                });

            $('#product-table-column-list').on('change', '.product-column-visible', function() {
                tableSettingsDirty = true;
                $('#product-table-settings-status').text('Perubahan akan otomatis tersimpan.');
            });

            $('#save-product-table-settings').on('click', function() {
                manualSaveInProgress = true;
                clearTimeout(autoSaveTimer);
                if (autoSaveRequest && typeof autoSaveRequest.abort === 'function') {
                    autoSaveRequest.abort();
                }
                autoSaveRequest = null;

                syncPreferenceFromSettingsList();

                initProductTable();
                saveTablePreference(true).done(function() {
                    const modal = bootstrap.Modal.getInstance(document.getElementById(
                        'productTableSettingsModal'));
                    tableSettingsDirty = false; // prevent double-apply on hidden
                    modal?.hide();
                }).always(function() {
                    manualSaveInProgress = false;
                });
            });

            $('#productTableSettingsModal').on('hidden.bs.modal', function() {
                if (manualSaveInProgress) {
                    return;
                }

                if (!tableSettingsDirty) {
                    return;
                }

                syncPreferenceFromSettingsList();
                initProductTable();
                scheduleAutoSaveTablePreference();
                tableSettingsDirty = false;
            });

            $('#reset-product-table-settings').on('click', function() {
                manualSaveInProgress = true;
                clearTimeout(autoSaveTimer);
                if (autoSaveRequest && typeof autoSaveRequest.abort === 'function') {
                    autoSaveRequest.abort();
                }
                autoSaveRequest = null;

                currentTablePreference = normalizeTablePreference({
                    column_order: productColumns.map(column => column.key),
                    column_visibility: {}
                });

                renderColumnSettings();
                initProductTable();
                saveTablePreference(true).always(function() {
                    manualSaveInProgress = false;
                });
            });
        });
    </script>
@endpush

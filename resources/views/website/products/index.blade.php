@extends('website.layout')

@php
    $viewMode = request('view', 'grid');
    $viewMode = in_array($viewMode, ['grid', 'list'], true) ? $viewMode : 'grid';
    $selectedBrandIds = array_map('strval', (array) request()->input('brand_id', []));
    $selectedMakerIds = array_map('strval', (array) request()->input('maker_id', []));
    $selectedProductTypeIds = array_map('strval', (array) request()->input('product_type_id', []));
    $baseResetParams = [];
    if (request('share')) {
        $baseResetParams['share'] = request('share');
    }
    if ($viewMode) {
        $baseResetParams['view'] = $viewMode;
    }
@endphp

@push('styles')
    <style>
        .catalog-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .view-toggle .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .filter-card .form-label {
            font-size: 12px;
            font-weight: 700;
            color: rgba(56, 69, 81, 0.8);
            margin-bottom: .25rem;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            font-size: 13px;
            min-height: 38px;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }

        .filter-card .select2-container .select2-selection--single {
            min-height: 38px;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.55rem;
        }

        .filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .filter-card .select2-container .select2-selection--multiple {
            min-height: 38px;
            height: 38px;
            padding: 0.15rem 0.45rem;
            overflow-y: auto;
        }

        .filter-card .select2-container--default .select2-selection--single .select2-selection__placeholder,
        .filter-card .select2-container--default .select2-selection--multiple .select2-search__field::placeholder {
            color: #8a94a6;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
            min-height: 100%;
            align-items: center;
            align-content: center;
            padding: 0;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-search--inline {
            display: flex;
            align-items: center;
            margin: 0;
            line-height: 1;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-search__field {
            margin-top: 0 !important;
            height: 22px;
            line-height: 22px;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin-top: 0;
            margin-bottom: 0;
        }

        .product-thumb {
            height: 120px;
        }

        .product-card .card-body {
            padding: .75rem !important;
        }

        .product-card h6 {
            font-size: 14px;
            line-height: 1.25;
        }

        .product-card .small {
            font-size: 12px;
        }

        .catalog-list-table th,
        .catalog-list-table td {
            vertical-align: middle;
        }

        .catalog-list-table td {
            white-space: normal;
        }

        @media (max-width: 575.98px) {
            .container-xxl {
                padding-left: .75rem;
                padding-right: .75rem;
            }

            .product-thumb {
                height: 96px;
            }

            .product-card:hover {
                transform: none;
            }

            .filter-card .card-body {
                padding: .75rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl py-3 py-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
            <div>
                <h1 class="fw-bold mb-2">Data Barang {{ $appCompanyName ?? 'UTE Parts' }}</h1>
                <p class="text-muted mb-0">
                    Cari sparepart HP berdasarkan kategori, brand, tipe HP, dan merek produksi.
                    @if ($isSharedCatalog)
                        <span class="badge bg-label-primary ms-1">Katalog pilihan</span>
                    @endif
                </p>
            </div>
            {{-- <div class="align-self-lg-end">
                @if (empty($customer))
                    <span class="badge bg-label-warning">Harga hanya untuk customer member</span>
                @else
                    <span class="badge bg-label-success">Harga member aktif</span>
                @endif
            </div> --}}
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" id="catalogFilterForm" action="{{ route('website.products.index') }}" class="row g-2 align-items-end">
                    @if (request('share'))
                        <input type="hidden" name="share" value="{{ request('share') }}">
                    @endif
                    <input type="hidden" name="view" id="viewModeInput" value="{{ $viewMode }}">

                    <div class="col-12 col-lg-4">
                        <label class="form-label">Cari</label>
                        <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                            placeholder="Nama / kode / barcode">
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select form-select-sm js-catalog-select-single"
                            data-placeholder="Semua Kategori">
                            <option value="">Semua</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Sub Kategori</label>
                        <select name="sub_category_id" class="form-select form-select-sm js-catalog-select-single"
                            data-placeholder="Semua Sub Kategori">
                            <option value="">Semua</option>
                            @foreach ($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}" @selected((string) request('sub_category_id') === (string) $subCategory->id)>{{ $subCategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Brand HP</label>
                        <select name="brand_id[]" class="form-select form-select-sm js-catalog-select-multiple" multiple
                            data-placeholder="Semua Brand">
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(in_array((string) $brand->id, $selectedBrandIds, true))>{{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Merek</label>
                        <select name="maker_id[]" class="form-select form-select-sm js-catalog-select-multiple" multiple
                            data-placeholder="Semua Merek">
                            @foreach ($makers as $maker)
                                <option value="{{ $maker->id }}" @selected(in_array((string) $maker->id, $selectedMakerIds, true))>{{ $maker->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg">
                        <label class="form-label">Tipe HP</label>
                        <select name="product_type_id[]" class="form-select form-select-sm js-catalog-select-multiple"
                            multiple data-placeholder="Semua Tipe HP">
                            @foreach ($productTypes as $type)
                                <option value="{{ $type->id }}" @selected(in_array((string) $type->id, $selectedProductTypeIds, true))>{{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="catalog-toolbar">
                            <div class="btn-group view-toggle" role="group" aria-label="Mode tampilan">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="viewGridBtn"
                                    aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}">
                                    <i class="bx bx-grid-alt"></i> Grid
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="viewListBtn"
                                    aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                            </div>

                            <a href="{{ route('website.products.index', $baseResetParams) }}"
                                class="btn btn-outline-secondary btn-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if ($viewMode === 'list')
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 catalog-list-table">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th class="text-center" style="width:110px;">Stok</th>
                                <th class="d-none d-lg-table-cell" style="width:170px;">Merek</th>
                                <th class="d-none d-lg-table-cell" style="width:220px;">Kategori</th>
                                <th class="d-none d-lg-table-cell" style="width:220px;">Brand</th>
                                <th class="d-none d-lg-table-cell" style="width:250px;">Tipe HP</th>
                                <th class="text-end" style="width:160px;">Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php
                                    $ready = (float) $product->stock_global > 0;
                                    $canSeePrice = !empty($customer) && ($customer['type'] ?? null) === 'member';
                                    $searchTerm = trim((string) request('q', ''));
                                    $typeItems = $product->productTypes?->pluck('name')->filter()->values()->all() ?? [];
                                    $typeCount = count($typeItems);
                                    $typeLimit = 2;
                                    $shouldExpandTypes = $searchTerm !== '';
                                    $shownTypes = $shouldExpandTypes ? $typeItems : array_slice($typeItems, 0, $typeLimit);
                                    $hiddenTypes = $shouldExpandTypes ? [] : array_slice($typeItems, $typeLimit);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $product->name }}</div>
                                        <div class="text-muted small">{{ $product->product_code }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $ready ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $ready ? 'Ready' : 'Kosong' }}</span>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        {{ $product->maker?->name ?: '-' }}
                                        @if (!empty($product->quality))
                                            <div class="text-muted small">{{ $product->quality }}</div>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        {{ $product->category?->name ?: '-' }}
                                        @if ($product->subCategory)
                                            / {{ $product->subCategory->name }}
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">{{ $product->brand?->name ?: '-' }}</td>
                                    <td class="d-none d-lg-table-cell">
                                        @if ($typeCount === 0)
                                            -
                                        @else
                                            {{ implode(', ', $shownTypes) }}
                                            @if (count($hiddenTypes) > 0)
                                                <span class="text-muted" title="{{ implode(', ', $hiddenTypes) }}">+{{ count($hiddenTypes) }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($canSeePrice)
                                            <span class="fw-bold text-primary">Rp
                                                {{ number_format((float) $product->selling_price, 0, ',', '.') }}</span>
                                        @else
                                            <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Lihat Harga
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">Produk tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="row g-3">
                @forelse($products as $product)
                    @php
                        $ready = (float) $product->stock_global > 0;
                        $canSeePrice = !empty($customer) && ($customer['type'] ?? null) === 'member';
                    @endphp
                    <div class="col-6 col-lg-4 col-xl-3">
                        <div class="card product-card h-100">
                            @if ($product->primaryImageUrl())
                                <img src="{{ $product->primaryImageUrl() }}" class="product-thumb rounded-top"
                                    alt="{{ $product->name }}">
                            @else
                                <div
                                    class="product-thumb rounded-top d-flex align-items-center justify-content-center text-muted">
                                    No Photo
                                </div>
                            @endif
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between gap-2 mb-2">
                                    <span
                                        class="badge {{ $ready ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $ready ? 'Ready' : 'Kosong' }}</span>
                                    <span class="text-muted small text-truncate">
                                        {{ $product->maker?->name ?: 'Tanpa merek' }}
                                        @if (!empty($product->quality))
                                            · {{ $product->quality }}
                                        @endif
                                    </span>
                                </div>
                                <h6 class="mb-2">{{ $product->name }}</h6>
                                <div class="text-muted small mb-3">
                                    {{ $product->category?->name ?: '-' }}
                                    @if ($product->subCategory)
                                        / {{ $product->subCategory->name }}
                                    @endif
                                    @if ($product->brand)
                                        @php
                                            $searchTerm = trim((string) request('q', ''));
                                            $typeItems = $product->productTypes?->pluck('name')->filter()->values()->all() ?? [];
                                            $typeCount = count($typeItems);
                                            $typeLimit = 2;
                                            $shouldExpandTypes = $searchTerm !== '';
                                            $shownTypes = $shouldExpandTypes ? $typeItems : array_slice($typeItems, 0, $typeLimit);
                                            $hiddenTypes = $shouldExpandTypes ? [] : array_slice($typeItems, $typeLimit);
                                        @endphp
                                        <br>{{ $product->brand->name }}
                                        @if ($typeCount > 0)
                                            - {{ implode(', ', $shownTypes) }}
                                            @if (count($hiddenTypes) > 0)
                                                <span class="text-muted" title="{{ implode(', ', $hiddenTypes) }}">+{{ count($hiddenTypes) }}</span>
                                            @endif
                                        @endif
                                    @endif
                                </div>

                                @if ($canSeePrice)
                                    <div class="fw-bold text-primary">Rp
                                        {{ number_format((float) $product->selling_price, 0, ',', '.') }}</div>
                                @else
                                    <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Lihat Harga
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center text-muted py-5">Produk tidak ditemukan.</div>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('catalogFilterForm');
            if (!form) return;
            let submitTimer = null;

            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $(form).find('.js-catalog-select-single').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return this.dataset.placeholder || 'Semua';
                    }
                });

                $(form).find('.js-catalog-select-multiple').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return this.dataset.placeholder || 'Pilih';
                    }
                });
            }

            const viewInput = document.getElementById('viewModeInput');
            const gridBtn = document.getElementById('viewGridBtn');
            const listBtn = document.getElementById('viewListBtn');

            function submitForm() {
                form.submit();
            }

            function queueSubmit(delay = 0) {
                clearTimeout(submitTimer);
                submitTimer = setTimeout(submitForm, delay);
            }

            function setView(mode) {
                if (!viewInput) return;
                viewInput.value = mode;
                submitForm();
            }

            if (gridBtn) gridBtn.addEventListener('click', () => setView('grid'));
            if (listBtn) listBtn.addEventListener('click', () => setView('list'));

            const searchInput = form.querySelector('input[name="q"]');
            let searchTimer = null;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(() => queueSubmit(), 450);
                });
            }

            form.querySelectorAll('select').forEach((selectEl) => {
                selectEl.addEventListener('change', () => queueSubmit());
            });

            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $(form).find('select').on('select2:select select2:unselect select2:clear', function() {
                    queueSubmit();
                });
            }
        })();
    </script>
@endpush

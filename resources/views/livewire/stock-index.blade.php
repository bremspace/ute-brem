<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-surface-800 mb-0">Manajemen Stok</h1>
            <p class="text-muted mb-0">Kelola stok produk per lokasi</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-box-seam me-1"></i> Data Produk
            </a>
            <a href="{{ route('stock-opname.index') }}" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-clipboard-data me-1"></i> Stock Opname
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-medium text-surface-600">Cari</label>
                    <input type="text"
                           wire:model.debounce.300ms="search"
                           class="form-control"
                           placeholder="Kode / Nama produk...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium text-surface-600">Lokasi</label>
                    <select wire:model="filterLocation" class="form-select">
                        <option value="">Semua Lokasi</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-medium text-surface-600">Status</label>
                    <select wire:model="filterLowStock" class="form-select">
                        <option value="">Semua</option>
                        <option value="1">Stok Rendah</option>
                        <option value="0">Semua Stok</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="text-muted small">
                        <span wire:loading.remove>{{ $products->total() }} produk</span>
                        <span wire:loading>Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Produk</th>
                            <th>Stok Global</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td class="fw-medium">{{ $product->product_code }}</td>
                            <td>{{ $product->name }}</td>
                            <td class="fw-medium">{{ $product->stock_global ?? '-' }}</td>
                            <td>{{ $product->sale_unit ?? '-' }}</td>
                            <td>
                                @php
                                    $locationStock = $product->stocks->first();
                                @endphp
                                @if($locationStock)
                                    <span class="badge bg-{{ $locationStock->quantity < ($locationStock->stock_min ?? 999) ? 'warning' : 'success' }}">
                                        {{ $locationStock->location->name ?? '-' }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $locationStock = $product->stocks->first();
                                @endphp
                                @if($locationStock)
                                    <span class="{{ $locationStock->quantity < ($locationStock->stock_min ?? 999) ? 'text-danger fw-bold' : '' }}">
                                        {{ $locationStock->quantity }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $minStock = $product->stocks->first()?->stock_min ?? 0;
                                    $qty = $product->stocks->first()?->quantity ?? 0;
                                @endphp
                                @if($qty > 0 && $qty < $minStock)
                                    <span class="badge bg-warning text-dark">Perlu Reorder</span>
                                @elseif($qty == 0)
                                    <span class="badge bg-danger">Habis</span>
                                @else
                                    <span class="badge bg-success">Tersedia</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('products.stocks.index', $product) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3 d-flex justify-content-end">
        {{ $products->links() }}
    </div>
</div>
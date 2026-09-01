@extends('layouts.sneat')

@section('title', 'Stok Lokasi Produk')

@php
    $stockByLocation = $product->stocks->keyBy('location_id');
    $totalStock = $product->stocks->sum(fn ($row) => (float) $row->quantity);
    $totalDamaged = $product->stocks->sum(fn ($row) => (float) $row->damaged_quantity);
@endphp

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">Periksa input stok.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Stok per Lokasi</h4>
                <div class="text-muted">{{ $product->name }} • {{ $product->product_code }}</div>
            </div>
            <div class="d-flex gap-2">
                @if(auth()->user()->hasPermission('master.product_stocks.edit'))
                    <a href="{{ route('purchase-orders.create', ['product_id' => $product->id]) }}" class="btn btn-primary">
                        <i class="bx bx-purchase-tag me-1"></i> Buat PO
                    </a>
                @endif
                <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-edit-alt me-1"></i> Edit Produk
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Total stok siap jual</div>
                        <div class="fs-3 fw-bold">{{ rtrim(rtrim(number_format($totalStock, 2, ',', '.'), '0'), ',') }} {{ $product->sale_unit ?: 'pcs' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Total stok rusak</div>
                        <div class="fs-3 fw-bold">{{ rtrim(rtrim(number_format($totalDamaged, 2, ',', '.'), '0'), ',') }} {{ $product->sale_unit ?: 'pcs' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-1">Lokasi default</div>
                        <div class="fs-5 fw-semibold">{{ $product->defaultLocation?->name ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Saldo per Lokasi</h5>
                        <small class="text-muted">Saldo sekarang dihitung dari mutasi dan transfer. Quantity tidak diedit langsung dari tabel ini.</small>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('products.stocks.update', $product) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Lokasi</th>
                                            <th width="18%">Rak</th>
                                            <th width="14%">Stok</th>
                                            <th width="14%">Rusak</th>
                                            <th width="14%">Min</th>
                                            <th width="14%">Max</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($locations as $index => $location)
                                            @php($row = $stockByLocation->get($location->id))
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $location->name }}</div>
                                                    <div class="text-muted small">{{ $location->code }}</div>
                                                    <input type="hidden" name="stocks[{{ $index }}][location_id]" value="{{ $location->id }}">
                                                </td>
                                                <td>
                                                    <select
                                                        name="stocks[{{ $index }}][location_rack_id]"
                                                        class="form-select"
                                                        @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))
                                                    >
                                                        <option value="">-</option>
                                                        @foreach($location->racks as $rack)
                                                            <option value="{{ $rack->id }}" {{ (string) old("stocks.$index.location_rack_id", $row?->location_rack_id) === (string) $rack->id ? 'selected' : '' }}>
                                                                {{ $rack->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ rtrim(rtrim(number_format((float) ($row?->quantity ?? 0), 2, ',', '.'), '0'), ',') }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-warning">{{ rtrim(rtrim(number_format((float) ($row?->damaged_quantity ?? 0), 2, ',', '.'), '0'), ',') }}</div>
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="stocks[{{ $index }}][stock_min]"
                                                        class="form-control"
                                                        value="{{ old("stocks.$index.stock_min", $row?->stock_min) }}"
                                                        @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="number"
                                                        step="0.01"
                                                        min="0"
                                                        name="stocks[{{ $index }}][stock_max]"
                                                        class="form-control"
                                                        value="{{ old("stocks.$index.stock_max", $row?->stock_max) }}"
                                                        @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                        type="text"
                                                        name="stocks[{{ $index }}][notes]"
                                                        class="form-control"
                                                        maxlength="500"
                                                        placeholder="Contoh: stok display depan, stok rak atas"
                                                        value="{{ old("stocks.$index.notes", $row?->notes) }}"
                                                        @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))
                                                    >
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">Belum ada lokasi aktif. Buat lokasi dulu sebelum memakai modul stok.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @if(auth()->user()->hasPermission('master.product_stocks.edit'))
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="bx bx-save me-1"></i> Simpan Min/Max & Catatan
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Mutasi Stok</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('products.stock-movements.store', $product) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Lokasi</label>
                                <select name="location_id" class="form-select" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                    <option value="">Pilih lokasi</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}" {{ (string) old('location_id', $product->default_location_id) === (string) $location->id ? 'selected' : '' }}>
                                            {{ $location->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rak</label>
                                <select name="location_rack_id" class="form-select js-rack-select" data-location-field="location_id" @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                    <option value="">Pilih rak</option>
                                    @foreach($locations as $location)
                                        @foreach($location->racks as $rack)
                                            <option value="{{ $rack->id }}" data-location-id="{{ $location->id }}" {{ (string) old('location_rack_id', $product->default_location_rack_id) === (string) $rack->id ? 'selected' : '' }}>
                                                {{ $location->name }} - {{ $rack->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jenis Mutasi</label>
                                <select name="movement_type" class="form-select" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                    @foreach($movementTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('movement_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Qty</label>
                                    <input type="number" step="0.01" min="0.01" name="movement_quantity" class="form-control" value="{{ old('movement_quantity') }}" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal</label>
                                    <input type="datetime-local" name="movement_at" class="form-control" value="{{ old('movement_at', now()->format('Y-m-d\TH:i')) }}" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="movement_notes" rows="3" class="form-control" placeholder="Contoh: stok datang dari supplier / selisih opname / rusak saat bongkar" @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>{{ old('movement_notes') }}</textarea>
                            </div>
                            @if(auth()->user()->hasPermission('master.product_stocks.edit'))
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-transfer-alt me-1"></i> Simpan Mutasi
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Transfer Antar Lokasi</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('products.stock-transfers.store', $product) }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Lokasi Asal</label>
                                    <select name="source_location_id" class="form-select" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                        <option value="">Pilih</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ (string) old('source_location_id', $product->default_location_id) === (string) $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Lokasi Tujuan</label>
                                    <select name="target_location_id" class="form-select" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                        <option value="">Pilih</option>
                                        @foreach($locations as $location)
                                            <option value="{{ $location->id }}" {{ (string) old('target_location_id') === (string) $location->id ? 'selected' : '' }}>
                                                {{ $location->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-0">
                                <div class="col-md-6">
                                    <label class="form-label">Rak Asal</label>
                                    <select name="source_location_rack_id" class="form-select js-rack-select" data-location-field="source_location_id" @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                        <option value="">Pilih</option>
                                        @foreach($locations as $location)
                                            @foreach($location->racks as $rack)
                                                <option value="{{ $rack->id }}" data-location-id="{{ $location->id }}" {{ (string) old('source_location_rack_id', $product->default_location_rack_id) === (string) $rack->id ? 'selected' : '' }}>
                                                    {{ $location->name }} - {{ $rack->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rak Tujuan</label>
                                    <select name="target_location_rack_id" class="form-select js-rack-select" data-location-field="target_location_id" @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                        <option value="">Pilih</option>
                                        @foreach($locations as $location)
                                            @foreach($location->racks as $rack)
                                                <option value="{{ $rack->id }}" data-location-id="{{ $location->id }}" {{ (string) old('target_location_rack_id') === (string) $rack->id ? 'selected' : '' }}>
                                                    {{ $location->name }} - {{ $rack->name }}
                                                </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-0">
                                <div class="col-md-6">
                                    <label class="form-label">Qty</label>
                                    <input type="number" step="0.01" min="0.01" name="transfer_quantity" class="form-control" value="{{ old('transfer_quantity') }}" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal</label>
                                    <input type="datetime-local" name="transferred_at" class="form-control" value="{{ old('transferred_at', now()->format('Y-m-d\TH:i')) }}" required @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="transfer_notes" rows="3" class="form-control" placeholder="Contoh: pindah stok ke toko / restock etalase / kirim ke teknisi" @disabled(! auth()->user()->hasPermission('master.product_stocks.edit'))>{{ old('transfer_notes') }}</textarea>
                            </div>
                            @if(auth()->user()->hasPermission('master.product_stocks.edit'))
                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-send me-1"></i> Simpan Transfer
                                    </button>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Riwayat Mutasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Lokasi</th>
                                        <th>Tipe</th>
                                        <th>Qty</th>
                                        <th>Saldo Akhir</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentMovements as $movement)
                                        <tr>
                                            <td>{{ $movement->movement_at?->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <div>{{ $movement->location?->name ?: '-' }}</div>
                                                @if($movement->rack)
                                                    <small class="text-muted">{{ $movement->rack->name }}</small>
                                                @endif
                                            </td>
                                            <td>{{ str_replace('_', ' ', strtoupper($movement->movement_type)) }}</td>
                                            <td>{{ rtrim(rtrim(number_format((float) $movement->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                            <td>
                                                <div>{{ rtrim(rtrim(number_format((float) $movement->stock_after, 2, ',', '.'), '0'), ',') }}</div>
                                                <small class="text-muted">Rusak: {{ rtrim(rtrim(number_format((float) $movement->damaged_after, 2, ',', '.'), '0'), ',') }}</small>
                                            </td>
                                            <td>{{ $movement->notes ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada mutasi stok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Riwayat Transfer</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Rute</th>
                                        <th>Qty</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransfers as $transfer)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $transfer->transfer_code }}</div>
                                                <small class="text-muted">{{ $transfer->notes ?: '-' }}</small>
                                            </td>
                                            <td>
                                                <div>{{ $transfer->sourceLocation?->name }} &rarr; {{ $transfer->targetLocation?->name }}</div>
                                                @if($transfer->sourceRack || $transfer->targetRack)
                                                    <small class="text-muted">{{ $transfer->sourceRack?->name ?: '-' }} &rarr; {{ $transfer->targetRack?->name ?: '-' }}</small>
                                                @endif
                                            </td>
                                            <td>{{ rtrim(rtrim(number_format((float) $transfer->quantity, 2, ',', '.'), '0'), ',') }}</td>
                                            <td>{{ $transfer->transferred_at?->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Belum ada transfer stok.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function filterRackSelect(select) {
        const locationField = select.dataset.locationField;
        const form = select.closest('form');
        const locationSelect = form?.querySelector(`[name="${locationField}"]`);
        const locationId = String(locationSelect?.value || '');

        [...select.options].forEach(option => {
            if (!option.value) return;
            option.hidden = locationId !== '' && String(option.dataset.locationId) !== locationId;
        });

        if (select.options[select.selectedIndex]?.hidden) {
            select.value = '';
        }
    }

    document.querySelectorAll('.js-rack-select').forEach(select => {
        filterRackSelect(select);

        const form = select.closest('form');
        const locationSelect = form?.querySelector(`[name="${select.dataset.locationField}"]`);
        locationSelect?.addEventListener('change', () => filterRackSelect(select));
    });
});
</script>
@endpush

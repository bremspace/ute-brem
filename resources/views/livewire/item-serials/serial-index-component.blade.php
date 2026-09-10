@extends('layouts.app')

@section('title', 'Serial & Bin Tracking')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Serial & Bin Tracking</h4>
            <div class="text-muted">Penelusuran per Serial Number / IMEI dan statusnya (available · reserved · sold).</div>
        </div>
        <a href="{{ route('item-serials.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Input Serial
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Total</div>
                            <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary"><i class="bx bx-package fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Available</div>
                            <h3 class="mb-0 fs-4">{{ $stats['available'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success"><i class="bx bx-check-circle fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Reserved</div>
                            <h3 class="mb-0 fs-4">{{ $stats['reserved'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info"><i class="bx bx-time fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-end">
                        <div>
                            <div class="text-muted small">Sold</div>
                            <h3 class="mb-0 fs-4">{{ $stats['sold'] ?? 0 }}</h3>
                        </div>
                        <div class="text-secondary"><i class="bx bx-shopping-bag fs-1"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="$refresh">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" wire:model="statusFilter">
                            <option value="">Semua Status</option>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Produk</label>
                        <select class="form-select" wire:model="productFilter">
                            <option value="">Semua</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_code }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model="search" placeholder="Serial / IMEI / Produk">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tampilkan</label>
                        <select class="form-select" wire:model="perPage">
                            <option value="15">15</option>
                            <option value="30">30</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Serial</h5>
            @if($serials->total() > 0)
                <span class="text-muted">Total: {{ $serials->total() }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($serials->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Serial / IMEI</th>
                                <th>Produk</th>
                                <th>Bin / Rak</th>
                                <th>Status</th>
                                <th>Referensi</th>
                                <th>Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serials as $serial)
                                <tr>
                                    <td class="font-monospace">{{ $serial->serial_number }}</td>
                                    <td>{{ $serial->product->name ?? '-' }}</td>
                                    <td class="text-muted">
                                        @if($serial->rack)
                                            {{ $serial->rack->location?->name ?? '' }}/{{ $serial->rack->code ?? '' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($serial->status) {
                                                'available' => 'bg-label-success',
                                                'reserved' => 'bg-label-info',
                                                'sold' => 'bg-label-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }} fs-6">{{ ucfirst($serial->status) }}</span>
                                    </td>
                                    <td class="text-muted small">{{ $serial->reference_code ?? '-' }}</td>
                                    <td class="text-muted small">{{ $serial->created_at?->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $serials->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-inbox fs-1"></i></div>
                    <p>Belum ada serial terdaftar.</p>
                    <a href="{{ route('item-serials.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Input Serial
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" x-show="showCreateModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Serial / IMEI</h5>
                <button type="button" class="btn-close" @click="showCreateModal = false"></button>
            </div>
            <div class="modal-body">
                @if($products->isEmpty())
                    <div class="alert alert-warning">Belum ada produk dengan Has Serial Number aktif.</div>
                @else
                    <form wire:submit.prevent="store">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Produk (Serialized)</label>
                                <select wire:model="productId" class="form-select" required>
                                    <option value="">Pilih Produk</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product['id'] }}">{{ $product['product_code'] }} · {{ $product['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bin / Rak (opsional)</label>
                                <select wire:model="locationRackId" class="form-select">
                                    <option value="">-- Tanpa rak --</option>
                                    @foreach($racks as $rack)
                                        <option value="{{ $rack['id'] }}">{{ $rack['location']['name'] ?? '' }} / {{ $rack['code'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Daftar Serial / IMEI</label>
                            <textarea wire:model="serials" rows="6" class="form-control font-monospace"
                                      placeholder="Satu serial per baris (bisa juga dipisah koma):&#10;IMEI-123456789012345&#10;SN-ABC123"></textarea>
                            <div class="form-text">Unique per produk — duplikat otomatis dilewati.</div>
                        </div>
                        @if($showResult)
                            <div class="alert alert-success">
                                <strong>Berhasil!</strong> Ditambahkan {{ $createdCount }} serial.
                                @if($skippedCount > 0)
                                    <span class="text-danger">{{ $skippedCount }} serial dilewati (duplikat).</span>
                                @endif
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">Simpan Serial</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection
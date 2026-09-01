@extends('layouts.sneat')

@section('title', 'Laporan Stok & Mutasi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold py-1 mb-1"><span class="text-muted fw-light">Laporan /</span> Stok</h4>
            <p class="text-muted mb-0">Kelola kuantitas stok barang per area penyimpanan dan telusuri log mutasi stok.</p>
        </div>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary"><i class="bx bx-chevron-left me-1"></i>Kembali</a>
    </div>

    <!-- Alert Stok Menipis -->
    @if($lowStocks->count() > 0)
        <div class="card bg-label-danger border-0 mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start">
                    <div class="avatar me-3">
                        <span class="avatar-initial rounded bg-danger text-white"><i class="bx bx-error-alt"></i></span>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="alert-heading fw-bold mb-1 text-danger">Peringatan: {{ $lowStocks->count() }} Item Stok Menipis!</h6>
                        <p class="mb-2 text-danger">Beberapa item berikut berada di bawah ambang batas minimum dan membutuhkan tindakan Purchase Order.</p>
                        
                        <div class="row g-2">
                            @foreach($lowStocks->take(5) as $low)
                                <div class="col-12 col-md-4">
                                    <div class="bg-white p-2 rounded shadow-sm d-flex justify-content-between align-items-center">
                                        <span class="small fw-semibold text-dark">{{ \Str::limit($low->product?->name, 25) }}</span>
                                        <span class="badge bg-danger">{{ $low->quantity }} / min {{ $low->stock_min ?? 5 }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Stock Listing & Search (Left Column) -->
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center pb-2">
                    <h5 class="mb-2 mb-md-0 fw-bold">Saldo Stok per Lokasi</h5>
                    
                    <form method="GET" action="{{ route('reports.stocks') }}" class="d-flex gap-2">
                        <select name="location_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Semua Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" @selected((string) $selectedLocationId === (string) $loc->id)>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari barang..." value="{{ request('q') }}">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-search"></i></button>
                    </form>
                </div>
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Lokasi</th>
                                <th>Rak</th>
                                <th class="text-center">Min Stok</th>
                                <th class="text-center">Max Stok</th>
                                <th class="text-end">Stok Bagus</th>
                                <th class="text-end">Stok Rusak</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($stocks as $stock)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $stock->product?->name ?: '-' }}</div>
                                        <small class="text-muted">{{ $stock->product?->product_code ?: '-' }}</small>
                                    </td>
                                    <td><span class="badge bg-label-info">{{ $stock->location?->name ?: '-' }}</span></td>
                                    <td>{{ $stock->rack?->rack_name ?: '-' }}</td>
                                    <td class="text-center text-muted">{{ $stock->stock_min ?: '-' }}</td>
                                    <td class="text-center text-muted">{{ $stock->stock_max ?: '-' }}</td>
                                    <td class="text-end fw-bold text-success">{{ number_format($stock->quantity, 0, ',', '.') }}</td>
                                    <td class="text-end text-danger">{{ number_format($stock->damaged_quantity, 0, ',', '.') }}</td>
                                    <td>
                                        <a href="{{ route('products.stocks.index', $stock->product_id) }}" class="btn btn-xs btn-outline-primary"><i class="bx bx-cog"></i> Kelola</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Tidak ada data stok ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer py-3">
                    {{ $stocks->links() }}
                </div>
            </div>
        </div>

        <!-- Recent Stock Movements (Right Column) -->
        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header pb-3">
                    <h5 class="card-title fw-bold mb-0">Aktivitas Mutasi Terakhir</h5>
                    <small class="text-muted">Log keluar masuk barang real-time</small>
                </div>
                <div class="card-body">
                    <ul class="timeline timeline-dashed mb-0">
                        @forelse($movements as $mv)
                            @php
                                $typeColors = [
                                    'opening' => 'secondary',
                                    'in' => 'success',
                                    'out' => 'danger',
                                    'adjustment_plus' => 'success',
                                    'adjustment_minus' => 'warning',
                                    'damaged_in' => 'danger',
                                    'damaged_out' => 'dark',
                                    'recover_damaged' => 'info',
                                    'transfer_in' => 'primary',
                                    'transfer_out' => 'warning'
                                ];
                                $badgeColor = $typeColors[$mv->movement_type] ?? 'primary';
                            @endphp
                            <li class="timeline-item timeline-item-{{ $badgeColor }} mb-4">
                                <span class="timeline-indicator timeline-indicator-{{ $badgeColor }}">
                                    <i class="bx bx-refresh"></i>
                                </span>
                                <div class="timeline-event">
                                    <div class="timeline-header d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 fw-bold">{{ \Str::limit($mv->product?->name, 25) }}</h6>
                                        <small class="text-muted">{{ $mv->movement_at ? $mv->movement_at->diffForHumans() : '-' }}</small>
                                    </div>
                                    <p class="mb-2 small">
                                        <span class="badge bg-label-{{ $badgeColor }} py-1 me-1">{{ ucfirst(str_replace('_', ' ', $mv->movement_type)) }}</span>
                                        <strong>
                                            @if(in_array($mv->movement_type, ['out', 'adjustment_minus', 'damaged_in', 'transfer_out']))
                                                -{{ $mv->quantity }}
                                            @else
                                                +{{ $mv->quantity }}
                                            @endif
                                        </strong>
                                        di <span class="text-info">{{ $mv->location?->name }}</span>
                                    </p>
                                    @if($mv->notes)
                                        <div class="bg-light p-2 rounded small text-muted">
                                            {{ $mv->notes }}
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <div class="text-center py-4 text-muted">Belum ada aktivitas mutasi stok hari ini.</div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

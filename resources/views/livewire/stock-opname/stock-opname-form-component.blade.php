@extends('layouts.app')

@section('title', '{{ $opname ? $opname->opname_code . " - Edit" : "Buat Stock Opname" }}')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $opname ? 'Edit Stock Opname' : 'Buat Stock Opname' }}</h4>
            <div class="text-muted">
                @if($opname)
                    Detail opname {{ $opname->opname_code }} • {{ $opname->location->name ?? '-' }}
                @else
                    Isi jumlah stok fisik (actual) untuk tiap produk. Selisih vs sistem dicatat saat diproses.
                @endif
            </div>
        </div>
        <a href="{{ route('stock-opname.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="{{ $opname ? 'update' : 'store' }}">
                <!-- Header Info -->
                <div class="row g-4 mb-4">
                    <!-- Location -->
                    <div class="col-md-4">
                        <label class="form-label">Lokasi <span class="text-danger">*</span></label>
                        <select name="location_id" wire:model="locationId" class="form-select" required {{ $opname ? 'disabled' : '' }}>
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ $locationId == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($opname)
                            <input type="hidden" name="location_id" value="{{ $opname->location_id }}">
                        @endif
                    </div>

                    <!-- Date -->
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Opname <span class="text-danger">*</span></label>
                        <input type="date" name="opname_date" wire:model="opnameDate" class="form-control" required>
                    </div>

                    <!-- Notes -->
                    <div class="col-md-4">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="notes" wire:model="notes" class="form-control" 
                               placeholder="Opsional">
                    </div>
                </div>

                @if(empty($items))
                    <div class="alert alert-warning">
                        <i class="bx bx-error me-1"></i>
                        Tidak ada stok tercatat di lokasi ini, atau belum ada produk.
                    </div>
                @else
                    <!-- Items Table -->
                    <div class="mb-4">
                        <h5 class="mb-3">Daftar Produk</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Produk</th>
                                        <th class="text-end">Stok Sistem</th>
                                        <th class="text-end" style="width: 180px;">Stok Fisik</th>
                                        <th class="text-end">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $productId => $item)
                                        @php
                                            $diff = $item['difference'];
                                            $diffClass = $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : '');
                                        @endphp
                                        <tr class="{{ abs($diff) > 0 ? 'table-warning' : '' }}">
                                            <td class="text-muted">{{ $item['product_code'] }}</td>
                                            <td>{{ $item['product_name'] }}</td>
                                            <td class="text-end">{{ number_format($item['system_qty'], 0, ',', '.') }}</td>
                                            <td class="text-end">
                                                <input type="hidden" name="qty[{{ $productId }}]" value="{{ $item['system_qty'] }}">
                                                <input type="number" 
                                                       min="0" 
                                                       step="0.01"
                                                       name="actual[{{ $productId }}]" 
                                                       wire:model="items.{{ $productId }}.actual_qty"
                                                       wire:change="updatedItems('{{ $productId }}', 'actual_qty')"
                                                       class="form-control form-control-sm text-end"
                                                       style="width: 140px;">
                                            </td>
                                            <td class="text-end {{ $diffClass }}">
                                                @if($diff > 0)
                                                    +{{ number_format($diff, 0, ',', '.') }}
                                                @else
                                                    {{ number_format($diff, 0, ',', '.') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> {{ $opname ? 'Simpan Perubahan' : 'Simpan Opname' }}
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>

    @if($opname && $hasDifference)
        <!-- Complete Alert -->
        <div class="alert alert-warning mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-error me-1"></i>
                    <strong>Ada selisih stok!</strong> 
                    @if($totalDifference > 0)
                        Total surplus: {{ $totalDifference }} item
                    @elseif($totalDifference < 0)
                        Total shortage: {{ abs($totalDifference) }} item
                    @endif
                </div>
                @if($opname->status === 'open')
                    <button wire:click="$emitUp('confirmComplete', {{ $opname->id }})"
                            class="btn btn-warning"
                            onclick="event.preventDefault(); return confirm('Proses opname ini? Selisih stok akan disesuaikan.');">
                        <i class="bx bx-check me-1"></i> Proses Opname
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

@livewireScripts
@endsection
@extends('layouts.sneat')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">{{ $title }}</h4><div class="text-muted">{{ $type === 'correction' ? 'Adjustment tambah/kurang stok.' : 'Pemakaian barang akan mengurangi stok.' }}</div></div>
        <a href="{{ route('back-office.dashboard') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('back-office.stock-documents.store', $type) }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-2"><label class="form-label">Tanggal</label><input type="date" name="document_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-4"><label class="form-label">Produk</label><select name="product_id" class="form-select" required>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->product_code }} - {{ $product->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Lokasi</label><select name="location_id" class="form-select js-location-select" required>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Rak</label><select name="location_rack_id" class="form-select js-rack-select"><option value="">-</option>@foreach($locations as $location)@foreach($location->racks as $rack)<option value="{{ $rack->id }}" data-location-id="{{ $location->id }}">{{ $location->name }} - {{ $rack->name }}</option>@endforeach @endforeach</select></div>
                <div class="col-md-3">
                    <label class="form-label">Jenis</label>
                    @if($type === 'correction')
                        <select name="movement_type" class="form-select">
                            <option value="adjustment_plus">Tambah Stok</option>
                            <option value="adjustment_minus">Kurangi Stok</option>
                        </select>
                    @else
                        <input type="hidden" name="movement_type" value="out">
                        <input class="form-control" value="Stok Keluar" readonly>
                    @endif
                </div>
                <div class="col-md-3"><label class="form-label">Qty</label><input type="number" min="0.01" step="0.01" name="quantity" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label">Keterangan</label><input name="description" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bx bx-save me-1"></i>Simpan</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Produk</th><th>Lokasi</th><th>Jenis</th><th class="text-end">Qty</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr><td>{{ optional($row->document_date)->format('d/m/Y') }}</td><td class="fw-semibold">{{ $row->document_code }}</td><td>{{ $row->product?->name ?: '-' }}</td><td>{{ $row->location?->name ?: '-' }}{{ $row->rack ? ' - ' . $row->rack->name : '' }}</td><td>{{ str_replace('_', ' ', $row->movement_type) }}</td><td class="text-end fw-bold">{{ rtrim(rtrim(number_format((float) $row->quantity, 2, ',', '.'), '0'), ',') }}</td><td>{{ $row->description ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Belum ada dokumen.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const locationSelect = document.querySelector('.js-location-select');
    const rackSelect = document.querySelector('.js-rack-select');
    function syncRacks() {
        const locationId = String(locationSelect?.value || '');
        rackSelect?.querySelectorAll('option').forEach(option => {
            option.hidden = option.value !== '' && String(option.dataset.locationId || '') !== locationId;
        });
        if (rackSelect?.selectedOptions[0]?.hidden) rackSelect.value = '';
    }
    locationSelect?.addEventListener('change', syncRacks);
    syncRacks();
});
</script>
@endpush

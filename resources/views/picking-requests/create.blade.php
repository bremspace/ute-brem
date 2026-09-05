@extends('layouts.sneat')

@section('title', 'Buat Picking Request')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Buat Picking Request</h4>
        <small class="text-muted">Pilih sparepart yang dibutuhkan meja servis.</small>
    </div>
    <a href="{{ route('picking-requests.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<form method="POST" action="{{ route('picking-requests.store') }}">
    @csrf
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Lokasi Gudang</label>
                    <select name="location_id" class="form-select form-select-sm" required>
                        @foreach ($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Keterangan</label>
                    <input type="text" name="notes" class="form-control form-control-sm" placeholder="Opsional">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Line Sparepart</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addLine"><i class="bx bx-plus"></i> Tambah</button>
        </div>
        <div class="card-body" id="lines">
            <div class="row g-2 line-row">
                <div class="col-md-6">
                    <select name="items[0][product_id]" class="form-select form-select-sm product-select" required>
                        <option value="">-- Pilih Produk --</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" data-stock="{{ $stockMap[$p->id] ?? 0 }}">{{ $p->product_code }} · {{ $p->name }} (stok: {{ $stockMap[$p->id] ?? 0 }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" min="0.01" step="1" name="items[0][qty]" class="form-control form-control-sm" placeholder="Qty" required>
                </div>
                <div class="col-md-2">
                    <input type="text" name="items[0][note]" class="form-control form-control-sm" placeholder="Catatan">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-line"><i class="bx bx-trash"></i></button>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button class="btn btn-primary">Simpan Request</button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let lineIdx = 0;
document.getElementById('addLine').addEventListener('click', () => {
    lineIdx++;
    const first = document.querySelector('.line-row');
    const clone = first.cloneNode(true);
    const idx = lineIdx;
    clone.querySelectorAll('[name]').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, '[' + idx + ']');
        if (el.tagName === 'INPUT') el.value = '';
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });
    document.getElementById('lines').appendChild(clone);
});
document.addEventListener('click', (e) => {
    if (e.target.closest('.remove-line')) {
        const rows = document.querySelectorAll('.line-row');
        if (rows.length > 1) e.target.closest('.line-row').remove();
    }
});
</script>
@endpush

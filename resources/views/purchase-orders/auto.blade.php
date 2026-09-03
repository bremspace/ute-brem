@extends('layouts.sneat')

@section('title', 'Rekomendasi Restock')

@section('content')
@php $gi = 0; @endphp
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Rekomendasi Restock (Need Restock)</h4>
        <small class="text-muted">
            Produk dengan <b>stok ≤ ROP</b> (Reorder Point). ROP = (ADU × Lead Time) + Safety Stock, Safety Stock = ADU × 3.
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="?recalculate=1" class="btn btn-outline-primary btn-sm">Hitung Ulang Proyeksi</a>
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-light btn-sm">Daftar PO</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="border rounded p-3 text-center bg-white">
            <div class="text-muted small">Produk Kritis</div>
            <div class="fs-4 fw-bold text-danger">{{ $totalCritical }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded p-3 text-center bg-white">
            <div class="text-muted small">Fast Moving (A)</div>
            <div class="fs-4 fw-bold text-primary">{{ $critical->where('abc_class','A')->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded p-3 text-center bg-white">
            <div class="text-muted small">Medium (B)</div>
            <div class="fs-4 fw-bold text-warning">{{ $critical->where('abc_class','B')->count() }}</div>
        </div>
    </div>
</div>

@if ($critical->isEmpty())
    <div class="alert alert-success">Tidak ada produk yang perlu restock saat ini. Stok semua aman.</div>
@endif

<form method="POST" action="{{ route('purchase-orders.generate_auto_po') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label small mb-1">Lokasi Tujuan PO</label>
        <select name="location_id" class="form-select form-select-sm" style="max-width:320px;">
            @foreach (\App\Models\Location::where('is_active', true)->get() as $l)
                <option value="{{ $l->id }}">{{ $l->name }}</option>
            @endforeach
        </select>
    </div>

    @foreach ($bySupplier as $supplierId => $items)
        @php $supplier = $items->first()->suppliers->first(); @endphp
        <div class="card mb-3">
            <div class="card-header bg-light">
                <span class="fw-semibold">{{ $supplier ? $supplier->name : 'Tanpa Supplier' }}</span>
                <span class="badge bg-secondary">{{ $items->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th></th><th>Kode</th><th>Produk</th><th>Kelas</th>
                            <th class="text-end">ADU</th><th class="text-end">ROP</th>
                            <th class="text-end">Stok</th><th class="text-end">Lead</th><th class="text-end">Qty Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $p)
                            @php $stk = \App\Models\ProductStock::where('product_id',$p->id)->sum('quantity'); @endphp
                            <tr>
                                <td>
                                    <input class="form-check-input item-check" type="checkbox"
                                           name="items[{{ $gi }}][product_id]" value="{{ $p->id }}">
                                </td>
                                <td class="text-muted">{{ $p->product_code }}</td>
                                <td>{{ $p->name }}</td>
                                <td>
                                    <span class="badge {{ $p->abc_class === 'A' ? 'bg-primary' : ($p->abc_class === 'B' ? 'bg-warning text-dark' : 'bg-secondary') }}">{{ $p->abc_class }}</span>
                                </td>
                                <td class="text-end">{{ $p->adu }}</td>
                                <td class="text-end fw-semibold text-danger">{{ $p->rop }}</td>
                                <td class="text-end">{{ number_format($stk,0,',','.') }}</td>
                                <td class="text-end">{{ $p->lead_time_days }}</td>
                                <td class="text-end">
                                    <input type="number" name="items[{{ $gi }}][qty]" min="0" step="1"
                                           class="form-control form-control-sm text-end" style="width:110px"
                                           value="{{ max(0, (float)$p->stock_max - (float)$stk) }}">
                                </td>
                            </tr>
                            @php $gi++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-between mt-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="checkAll"><i class="bx bx-check-double me-1"></i>Pilih Semua</button>
        <button class="btn btn-primary btn-sm" disabled id="genBtn">Buat Draft PO Terpilih</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
const items = document.querySelectorAll('.item-check');
const btn = document.getElementById('genBtn');
function sync(){ btn.disabled = document.querySelectorAll('.item-check:checked').length === 0; }
items.forEach(i => i.addEventListener('change', sync));
document.getElementById('checkAll').addEventListener('click', () => { items.forEach(i => i.checked = true); sync(); });
</script>
@endpush

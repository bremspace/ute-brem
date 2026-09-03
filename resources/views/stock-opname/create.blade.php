@extends('layouts.sneat')

@section('title', 'Buat Stock Opname')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Buat Stock Opname</h4>
        <small class="text-muted">Isi jumlah stok fisik (actual) untuk tiap produk. Selisih vs sistem dicatat saat diproses.</small>
    </div>
    <a href="{{ route('stock-opname.index') }}" class="btn btn-light btn-sm">Kembali</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('stock-opname.create') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small mb-1">Lokasi</label>
                <select name="location" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach ($locations as $l)
                        <option value="{{ $l->id }}" @selected((int)$selectedLocationId === $l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>

@if ($rows->isEmpty())
    <div class="alert alert-warning">Tidak ada stok tercatat di lokasi ini, atau belum ada produk.</div>
@else
    <form method="POST" action="{{ route('stock-opname.store') }}">
        @csrf
        <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
        <div class="card">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-auto">
                        <label class="form-label small mb-1">Tanggal Opname</label>
                        <input type="date" name="opname_date" value="{{ now()->toDateString() }}" class="form-control form-control-sm" required>
                    </div>
                    <div class="col">
                        <label class="form-label small mb-1">Keterangan</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Opsional">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th><th>Produk</th>
                                <th class="text-end">Stok Sistem</th><th class="text-end">Stok Fisik</th>
                                <th class="text-end">Selisih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $r)
                                <tr>
                                    <td class="text-muted">{{ $r->code }}</td>
                                    <td>{{ $r->name }}</td>
                                    <td class="text-end">{{ number_format($r->system_qty, 0, ',', '.') }}</td>
                                    <td class="text-end" style="width:140px;">
                                        <input type="hidden" name="qty[{{ $r->product_id }}]" value="{{ $r->system_qty }}">
                                        <input type="number" min="0" step="1" class="form-control form-control-sm text-end"
                                               name="actual[{{ $r->product_id }}]" value="{{ $r->system_qty }}"
                                               oninput="this.closest('tr').querySelector('.diff').textContent=(Number(this.value)-{{ $r->system_qty }})">
                                    </td>
                                    <td class="text-end diff">0</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-end">
                <button class="btn btn-primary">Simpan Opname</button>
            </div>
        </div>
    </form>
@endif
@endsection

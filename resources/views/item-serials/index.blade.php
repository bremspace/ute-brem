@extends('layouts.sneat')

@section('title', 'Serial & Bin Tracking')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Serial & Bin Tracking</h4>
        <small class="text-muted">Penelusuran per Serial Number / IMEI dan statusnya (available · reserved · sold).</small>
    </div>
    <a href="{{ route('item-serials.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i>Input Serial (Penerimaan)
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-auto"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari serial / referensi / produk"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach (['available','reserved','sold'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Serial / IMEI</th><th>Produk</th><th>Bin / Rak</th><th>Status</th><th>Referensi</th><th>Masuk</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($serials as $s)
                    <tr>
                        <td class="font-monospace">{{ $s->serial_number }}</td>
                        <td>{{ $s->product->name ?? '-' }}</td>
                        <td class="text-muted">{{ $s->rack?->location?->name ? $s->rack->location->name . ' / ' . $s->rack->code : ($s->rack?->code ?? '-') }}</td>
                        <td>
                            <span class="badge {{ $s->status === 'available' ? 'bg-success' : ($s->status === 'reserved' ? 'bg-info' : 'bg-secondary') }}">
                                {{ ucfirst($s->status) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $s->reference_code }}</td>
                        <td class="text-muted small">{{ $s->created_at?->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada serial terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">{{ $serials->links() }}</div>
</div>
@endsection

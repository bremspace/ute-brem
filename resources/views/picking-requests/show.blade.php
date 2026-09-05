@extends('layouts.sneat')

@section('title', 'Detail Picking Request')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Picking Request {{ $pickingRequest->request_code }}</h4>
        <small class="text-muted">
            {{ $pickingRequest->location->name ?? '-' }} · Teknisi: {{ $pickingRequest->technician->name ?? '-' }}
            · <span class="badge {{ $pickingRequest->status === 'fulfilled' ? 'bg-success' : ($pickingRequest->status === 'cancelled' ? 'bg-secondary' : 'bg-warning text-dark') }}">{{ ucfirst($pickingRequest->status) }}</span>
        </small>
    </div>
    <a href="{{ route('picking-requests.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th>Produk</th>
                    <th class="text-end">Qty Diminta</th><th class="text-end">Qty Terpicking</th>
                    <th>Status</th><th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pickingRequest->items as $item)
                    <tr>
                        <td class="text-muted">{{ $item->product->product_code ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td class="text-end">{{ number_format($item->qty_requested, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->qty_picked, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $item->status === 'reserved' ? 'bg-info' : ($item->status === 'picked' ? 'bg-success' : 'bg-light text-dark') }}">
                                {{ $item->status === 'reserved' ? 'Reserved for Repair' : $item->status }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $item->note }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($pickingRequest->status === 'open')
    <div class="mt-3 d-flex gap-2">
        <form method="POST" action="{{ route('picking-requests.fulfill', $pickingRequest) }}"
              onsubmit="return confirm('Proses picking ini? Stok gudang akan terpotong dan serial di-reserve.');">
            @csrf
            <button class="btn btn-primary"><i class="bx bx-check me-1"></i>Proses Picking</button>
        </form>
        <form method="POST" action="{{ route('picking-requests.cancel', $pickingRequest) }}">
            @csrf
            <button class="btn btn-outline-secondary">Batal</button>
        </form>
    </div>
@endif
@endsection

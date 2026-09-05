@extends('layouts.sneat')

@section('title', 'Picking Request')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Internal Picking Request</h4>
        <small class="text-muted">Permintaan sparepart dari meja teknisi servis ke gudang — stok dipotong & bertanda Reserved for Repair.</small>
    </div>
    <a href="{{ route('picking-requests.create') }}" class="btn btn-primary btn-sm"><i class="bx bx-plus me-1"></i>Request Sparepart</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th>Teknisi</th><th>Lokasi</th><th>Jumlah Item</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $r)
                    <tr>
                        <td class="fw-semibold">{{ $r->request_code }}</td>
                        <td>{{ $r->technician->name ?? '-' }}</td>
                        <td>{{ $r->location->name ?? '-' }}</td>
                        <td>{{ $r->items->count() }}</td>
                        <td>
                            <span class="badge {{ $r->status === 'fulfilled' ? 'bg-success' : ($r->status === 'cancelled' ? 'bg-secondary' : 'bg-warning text-dark') }}">
                                {{ ucfirst($r->status) }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $r->created_at?->format('d M Y H:i') }}</td>
                        <td class="text-end">
                            @if ($r->status === 'open')
                                <form method="POST" action="{{ route('picking-requests.fulfill', $r) }}" class="d-inline"
                                      onsubmit="return confirm('Proses picking ini? Stok gudang akan terpotong dan serial di-reserve.');">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">Proses</button>
                                </form>
                                <form method="POST" action="{{ route('picking-requests.cancel', $r) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">Batal</button>
                                </form>
                                <form method="POST" action="{{ route('picking-requests.destroy', $r) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus picking request ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            @endif
                            <a href="{{ route('picking-requests.show', $r) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada picking request.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

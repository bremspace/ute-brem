@extends('layouts.sneat')

@section('title', 'Stock Opname')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Stock Opname / Stock Count</h4>
        <small class="text-muted">Penghitungan fisik stok per lokasi & penyesuaian otomatis ke persediaan.</small>
    </div>
    <a href="{{ route('stock-opname.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i>Buat Opname
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>No. Opname</th><th>Lokasi</th><th>Tanggal</th><th>Status</th>
                    <th>Keterangan</th><th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opnames as $op)
                    <tr>
                        <td class="fw-semibold">{{ $op->opname_code }}</td>
                        <td>{{ $op->location->name ?? '-' }}</td>
                        <td>{{ $op->opname_date->format('d M Y') }}</td>
                        <td>
                            <span class="badge {{ $op->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ ucfirst($op->status) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $op->notes }}</td>
                        <td class="text-end">
                            @if ($op->status === 'open')
                                <form method="POST" action="{{ route('stock-opname.complete', $op) }}" class="d-inline"
                                      onsubmit="return confirm('Proses selisih opname ini? Stok & pembukuan akan diperbarui.');">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Proses</button>
                                </form>
                                <form method="POST" action="{{ route('stock-opname.destroy', $op) }}" class="d-inline"
                                      onsubmit="return confirm('Hapus draft opname ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            @endif
                            <a href="{{ route('stock-opname.show', $op) }}" class="btn btn-sm btn-light">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada stock opname.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

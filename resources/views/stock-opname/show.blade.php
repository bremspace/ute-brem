@extends('layouts.sneat')

@section('title', 'Detail Stock Opname')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Stock Opname {{ $opname->opname_code }}</h4>
        <small class="text-muted">
            {{ $opname->location->name ?? '-' }} · {{ $opname->opname_date->format('d M Y') }}
            · <span class="badge {{ $opname->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($opname->status) }}</span>
        </small>
    </div>
    <a href="{{ route('stock-opname.index') }}" class="btn btn-light btn-sm">Kembali</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th>Produk</th>
                    <th class="text-end">Stok Sistem</th><th class="text-end">Stok Fisik</th>
                    <th class="text-end">Selisih</th><th class="text-end">Nilai Selisih</th>
                </tr>
            </thead>
            <tbody>
                @php $totalDiffValue = 0; $hasDiff = false; @endphp
                @foreach ($opname->items as $item)
                    @php
                        $diff = (float) $item->difference;
                        $val = $diff * (float) ($item->product->purchase_price ?? 0);
                        $totalDiffValue += $val;
                        if (abs($diff) > 0) $hasDiff = true;
                    @endphp
                    <tr class="{{ abs($diff) > 0 ? 'table-warning' : '' }}">
                        <td class="text-muted">{{ $item->product->product_code ?? '-' }}</td>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td class="text-end">{{ number_format($item->system_qty, 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($item->actual_qty, 0, ',', '.') }}</td>
                        <td class="text-end {{ $diff < 0 ? 'text-danger' : ($diff > 0 ? 'text-success' : '') }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }}
                        </td>
                        <td class="text-end">{{ number_format($val, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <td colspan="5" class="text-end fw-semibold">Total Nilai Selisih</td>
                    <td class="text-end fw-bold">{{ number_format($totalDiffValue, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@if ($opname->status === 'open')
    @if (!$hasDiff)
        <div class="alert alert-success mt-3">Tidak ada selisih — stok fisik sudah cocok dengan sistem. Opname tetap bisa diproses.</div>
    @endif
    <form method="POST" action="{{ route('stock-opname.complete', $opname) }}" class="mt-3"
          onsubmit="return confirm('Proses opname ini? Selisih stok akan disesuaikan dan dicatat ke pembukuan.');">
        @csrf
        <button class="btn btn-success">Proses / Tutup Opname</button>
    </form>
@endif
@endsection

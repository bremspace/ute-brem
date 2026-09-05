@extends('layouts.sneat')

@section('title', 'Konfirmasi Penerimaan Barang')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Konfirmasi Penerimaan Barang</h4>
            <div class="text-muted">Nomor Dokumen: <span class="fw-bold">{{ $branchTransfer->transfer_code }}</span></div>
        </div>
        <a href="{{ route('branch-transfers.show', $branchTransfer) }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali ke Detail
        </a>
    </div>

    <div class="row g-4">
        <!-- Details Summary -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Cabang Asal</span>
                            <strong class="fs-6">{{ $branchTransfer->sourceBranch?->name ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $branchTransfer->sourceLocation?->name }}</small>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Cabang Tujuan (Penerima)</span>
                            <strong class="fs-6 text-primary">{{ $branchTransfer->targetBranch?->name ?? '-' }}</strong><br>
                            <small class="text-muted">{{ $branchTransfer->targetLocation?->name }}</small>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">Tanggal Kirim</span>
                            <strong>{{ $branchTransfer->sent_at ? $branchTransfer->sent_at->format('d/m/Y H:i') : '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Form Table -->
        <div class="col-12">
            <form action="{{ route('branch-transfers.receive', $branchTransfer) }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Verifikasi Jumlah Barang yang Diterima</h5>
                        <p class="text-muted small mb-0">Harap hitung fisik barang di lokasi tujuan dan masukkan jumlahnya di bawah ini. Jika ada selisih kurang, selisihnya akan dicatat sebagai barang hilang/rusak.</p>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-bordered align-middle">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Rak Asal</th>
                                        <th class="text-end" style="width: 15%;">Jumlah Dikirim</th>
                                        <th style="width: 25%;">Rak Tujuan (Penyimpanan)</th>
                                        <th style="width: 20%;">Jumlah Diterima <span class="text-danger">*</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branchTransfer->items as $item)
                                        @php
                                            $qtySent = (float) $item->quantity_sent;
                                        @endphp
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">[{{ $item->product?->product_code }}]</span><br>
                                                <span class="text-muted">{{ $item->product?->name }}</span>
                                            </td>
                                            <td>{{ $item->sourceRack?->name ?: '-' }}</td>
                                            <td class="text-end fw-semibold">{{ rtrim(rtrim(number_format($qtySent, 2, ',', '.'), '0'), ',') }}</td>
                                            <td>
                                                <select name="items[{{ $item->id }}][target_location_rack_id]" class="form-select form-select-sm">
                                                    <option value="">Pilih Rak</option>
                                                    @foreach($branchTransfer->targetLocation->racks as $rack)
                                                        <option value="{{ $rack->id }}" {{ ($item->target_location_rack_id ?: ($item->product?->default_location_id == $branchTransfer->target_location_id ? $item->product?->default_rack_id : '')) == $rack->id ? 'selected' : '' }}>
                                                            {{ $rack->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" step="0.01" min="0" max="{{ $qtySent }}" name="items[{{ $item->id }}][quantity_received]" class="form-control text-end" value="{{ old('items.'.$item->id.'.quantity_received', $qtySent) }}" required>
                                                    <span class="input-group-text">PCS</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Konfirmasi penerimaan barang? Stok cabang tujuan akan bertambah dan dokumen akan ditutup.')">
                                <i class="bx bx-check-double me-1"></i> Konfirmasi Penerimaan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.sneat')

@section('title', 'Detail Customer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">{{ $customer->name }}</h4>
            <div class="text-muted small">
                {{ $customer->type === 'member' ? 'Member' : 'Biasa' }}
                @if($customer->group)
                    • {{ $customer->group->name }}
                @endif
                • {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
            @if(($pointsBalanceReady ?? false) && $customer->type === 'member' && $customer->is_active && auth()->user()->hasPermission('master.customer_groups.edit'))
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#redeemPointsModal">
                    <i class="bx bx-gift me-1"></i> Tukar Poin
                </button>
            @endif
            @if($customersTableReady && auth()->user()->hasPermission('master.customer_groups.edit'))
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                    <i class="bx bx-edit-alt me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Informasi</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">Kode Member</div>
                        <div class="fw-semibold">{{ $customer->member_code ?? '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">No HP</div>
                        <div class="fw-semibold">{{ $customer->phone ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Email</div>
                        <div class="fw-semibold">{{ $customer->email ?: '-' }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Customer Group</div>
                        <div class="fw-semibold">{{ $customer->group?->name ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Total Poin</div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-label-primary fs-6">{{ number_format((int) ($customer->points_balance ?? 0), 0, ',', '.') }} poin</span>
                            @if($customer->type !== 'member')
                                <span class="text-muted small">(bukan member)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Catatan</h5>
                </div>
                <div class="card-body">
                    <div class="text-muted">Belum ada catatan khusus.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Tagihan Tempo</h5>
                    <span class="badge bg-label-warning">{{ ($creditSales ?? collect())->count() + ($creditServices ?? collect())->count() }} belum lunas</span>
                </div>
                <div class="card-body">
                    @if(($creditSales ?? collect())->isEmpty() && ($creditServices ?? collect())->isEmpty())
                        <div class="text-muted">Tidak ada tagihan tempo yang belum lunas.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Ref</th>
                                        <th>Jenis</th>
                                        <th>Jatuh Tempo</th>
                                        <th class="text-end">Sisa</th>
                                        <th style="width: 150px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($creditSales as $sale)
                                        @php $remaining = max(0, (float) $sale->grand_total - (float) $sale->paid_amount); @endphp
                                        <tr>
                                            <td><a href="{{ route('transactions.show', $sale) }}" class="fw-semibold text-decoration-none">{{ $sale->sale_code }}</a></td>
                                            <td>Penjualan</td>
                                            <td>{{ optional($sale->credit_due_at)->format('d M Y') ?: '-' }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customerCreditPaymentSale{{ $sale->id }}">
                                                    Pelunasan
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @foreach($creditServices as $row)
                                        @php $remaining = max(0, (float) $row->grand_total - (float) $row->paid_amount); @endphp
                                        <tr>
                                            <td><a href="{{ route('service-transactions.show', $row) }}" class="fw-semibold text-decoration-none">{{ $row->service_code }}</a></td>
                                            <td>Service</td>
                                            <td>{{ optional($row->credit_due_at)->format('d M Y') ?: '-' }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format($remaining, 0, ',', '.') }}</td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customerCreditPaymentService{{ $row->id }}">
                                                    Pelunasan
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Transaksi</h5>
                    @if(! ($salesTableReady ?? false))
                        <span class="badge bg-label-warning">Transaksi belum tersedia</span>
                    @else
                        <span class="badge bg-label-secondary">{{ $sales->count() }} baris</span>
                    @endif
                </div>
                <div class="card-body">
                    @if(! ($salesTableReady ?? false))
                        <div class="text-muted">Riwayat transaksi belum tersedia.</div>
                    @elseif(($sales ?? collect())->isEmpty())
                        <div class="text-muted">Belum ada transaksi untuk customer ini.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Tanggal</th>
                                        <th>Lokasi</th>
                                        <th>Kasir</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Poin</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales as $sale)
                                        <tr>
                                            <td class="fw-semibold">
                                                <a href="{{ route('transactions.show', $sale) }}" class="text-decoration-none text-primary">
                                                    {{ $sale->sale_code }}
                                                </a>
                                            </td>
                                            <td>{{ optional($sale->sale_at)->format('d M Y H:i') ?: '-' }}</td>
                                            <td>{{ $sale->location?->name ?: '-' }}</td>
                                            <td>{{ $sale->cashier?->name ?: '-' }}</td>
                                            <td class="text-end fw-semibold">Rp {{ number_format((float) $sale->grand_total, 0, ',', '.') }}</td>
                                            <td class="text-end">{{ (int) ($sale->points_earned ?? 0) }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ $sale->status === 'paid' ? 'success' : 'secondary' }}">
                                                    {{ $sale->status === 'paid' ? 'Paid' : 'Void' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Riwayat Poin</h5>
                    @if(! $ledgersTableReady)
                        <span class="badge bg-label-warning">Ledger belum tersedia</span>
                    @else
                        <span class="badge bg-label-secondary">{{ $ledgers->count() }} baris</span>
                    @endif
                </div>
                <div class="card-body">
                    @if(! $ledgersTableReady)
                        <div class="text-muted">Riwayat poin belum tersedia.</div>
                    @elseif($ledgers->isEmpty())
                        <div class="text-muted">Belum ada riwayat poin.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Sumber</th>
                                        <th>Ref</th>
                                        <th>Lampiran</th>
                                        <th class="text-end">Poin</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ledgers as $row)
                                        @php
                                            $files = ($ledgerFilesReady ?? false) ? ($row->files ?? collect()) : collect();
                                            $fileCount = is_countable($files) ? count($files) : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ optional($row->created_at)->format('d M Y H:i') ?: '-' }}</td>
                                            <td>{{ $row->source ?: '-' }}</td>
                                            <td>{{ $row->reference_code ?: '-' }}</td>
                                            <td>
                                                @if($fileCount > 0)
                                                    <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal"
                                                        data-bs-target="#ledgerFilesModal{{ $row->id }}">
                                                        {{ $fileCount }} file
                                                    </button>

                                                    <div class="modal fade" id="ledgerFilesModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Lampiran</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="list-group list-group-flush">
                                                                        @foreach($files as $file)
                                                                            <a class="list-group-item list-group-item-action"
                                                                                href="{{ $file->file_url }}" target="_blank">
                                                                                <div class="fw-semibold">{{ $file->original_name }}</div>
                                                                                <div class="text-muted small">{{ number_format((int) ($file->file_size ?? 0), 0, ',', '.') }} bytes</div>
                                                                            </a>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-label-{{ (int) $row->points >= 0 ? 'success' : 'danger' }}">
                                                    {{ (int) $row->points >= 0 ? '+' : '' }}{{ (int) $row->points }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold">{{ number_format((int) $row->balance_after, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@foreach($creditSales ?? collect() as $sale)
    @php $remaining = max(0, (float) $sale->grand_total - (float) $sale->paid_amount); @endphp
    <div class="modal fade" id="customerCreditPaymentSale{{ $sale->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('customers.credit-payments.store', $customer) }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_type" value="sale">
                    <input type="hidden" name="transaction_id" value="{{ $sale->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Pelunasan {{ $sale->sale_code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">Sisa tempo: <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong></div>
                        <div class="mb-3"><label class="form-label">Tanggal Bayar</label><input type="datetime-local" name="payment_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                        <div class="mb-3"><label class="form-label">Nominal</label><input type="number" min="1" max="{{ (int) $remaining }}" step="1" name="amount" class="form-control" value="{{ (int) $remaining }}" required></div>
                        <div class="mb-3"><label class="form-label">Metode</label><select name="payment_method" class="form-select"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option></select></div>
                        <div class="mb-3"><label class="form-label">Referensi</label><input type="text" name="reference" class="form-control" placeholder="Opsional"></div>
                        <div><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2" placeholder="Opsional"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pelunasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@foreach($creditServices ?? collect() as $row)
    @php $remaining = max(0, (float) $row->grand_total - (float) $row->paid_amount); @endphp
    <div class="modal fade" id="customerCreditPaymentService{{ $row->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('customers.credit-payments.store', $customer) }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_type" value="service">
                    <input type="hidden" name="transaction_id" value="{{ $row->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Pelunasan {{ $row->service_code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">Sisa tempo: <strong>Rp {{ number_format($remaining, 0, ',', '.') }}</strong></div>
                        <div class="mb-3"><label class="form-label">Tanggal Bayar</label><input type="datetime-local" name="payment_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required></div>
                        <div class="mb-3"><label class="form-label">Nominal</label><input type="number" min="1" max="{{ (int) $remaining }}" step="1" name="amount" class="form-control" value="{{ (int) $remaining }}" required></div>
                        <div class="mb-3"><label class="form-label">Metode</label><select name="payment_method" class="form-select"><option value="cash">Cash</option><option value="transfer">Transfer</option><option value="qris">QRIS</option></select></div>
                        <div class="mb-3"><label class="form-label">Referensi</label><input type="text" name="reference" class="form-control" placeholder="Opsional"></div>
                        <div><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="2" placeholder="Opsional"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pelunasan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@if(($pointsBalanceReady ?? false) && $customer->type === 'member' && $customer->is_active && auth()->user()->hasPermission('master.customer_groups.edit'))
    <div class="modal fade" id="redeemPointsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('customers.redeem-points', $customer) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tukar Poin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            Poin tersedia: <strong>{{ number_format((int) ($customer->points_balance ?? 0), 0, ',', '.') }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tukar Berapa Poin <span class="text-danger">*</span></label>
                            <input type="number" min="1" step="1" name="points" class="form-control @error('points') is-invalid @enderror"
                                value="{{ old('points') }}" required>
                            @error('points')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror"
                                placeholder="Contoh: tukar voucher / potongan servis">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Lampiran</label>
                            <input type="file" name="attachments[]" class="form-control @error('attachments.*') is-invalid @enderror" multiple>
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Opsional. Bisa lebih dari 1 file.</div>
                        </div>
                        @if(! ($ledgerFilesReady ?? false))
                            <div class="text-muted small">
                                Lampiran akan diabaikan karena tabel lampiran poin belum tersedia.
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-check me-1"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection

@extends('layouts.sneat')

@section('title', 'Detail Service')

@push('styles')
    <style>
        .pattern-preview {
            width: 132px;
            aspect-ratio: 1 / 1;
            position: relative;
            margin-top: .5rem;
            border: 1px solid rgba(67, 89, 113, .18);
            border-radius: .5rem;
            background: #f8fbff;
            padding: 12px;
        }

        .pattern-preview svg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .pattern-preview-grid {
            position: relative;
            z-index: 2;
            height: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 10px;
        }

        .pattern-preview-dot {
            width: 22px;
            height: 22px;
            border: 2px solid #0b4f7a;
            border-radius: 50%;
            background: #eef7ff;
            color: #0b4f7a;
            font-size: 10px;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            justify-self: center;
            align-self: center;
        }

        .pattern-preview-dot.active {
            background: #0b4f7a;
            color: #fff;
            box-shadow: 0 0 0 5px rgba(11, 79, 122, .12);
        }

        .service-mobile-page {
            display: none;
        }

        @media (max-width: 767.98px) {
            #layout-menu,
            .layout-navbar,
            .dashboard-fab {
                display: none !important;
            }

            body.transactions-standalone,
            body.transactions-standalone .layout-wrapper,
            body.transactions-standalone .layout-container,
            body.transactions-standalone .layout-page,
            body.transactions-standalone .content-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
            }

            body.transactions-standalone.sidebar-hidden .layout-page {
                padding-inline-start: 0 !important;
                -webkit-padding-start: 0 !important;
                padding-left: 0 !important;
                margin-left: 0 !important;
            }

            .service-desktop-page {
                display: none !important;
            }

            .service-mobile-page {
                display: block;
                min-height: 100vh;
                background: #f5f6fb;
                padding: .75rem;
            }

            .srv-detail-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: .75rem;
                margin-bottom: .75rem;
            }

            .srv-detail-title {
                font-size: 19px;
                font-weight: 900;
                line-height: 1.15;
                color: #263238;
            }

            .srv-detail-code {
                color: #697a8d;
                font-size: 11px;
                word-break: break-word;
                margin-top: .15rem;
            }

            .srv-detail-actions {
                display: flex;
                gap: .4rem;
                flex: 0 0 auto;
            }

            .srv-detail-card {
                background: #fff;
                border: 1px solid rgba(67, 89, 113, .12);
                border-radius: .65rem;
                padding: .7rem;
                margin-bottom: .7rem;
                box-shadow: 0 8px 18px rgba(32, 36, 44, .06);
            }

            .srv-detail-total {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: .75rem;
            }

            .srv-detail-total .k {
                font-size: 11px;
                color: #697a8d;
                font-weight: 900;
                text-transform: uppercase;
            }

            .srv-detail-total .v {
                font-size: 20px;
                font-weight: 900;
                color: #263238;
                white-space: nowrap;
            }

            .srv-detail-grid {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .25rem .75rem;
                margin-top: .55rem;
                padding-top: .55rem;
                border-top: 1px dashed rgba(67, 89, 113, .18);
                font-size: 11px;
            }

            .srv-detail-grid .v {
                color: #263238;
                font-weight: 800;
                text-align: right;
            }

            .srv-detail-section {
                font-size: 12px;
                font-weight: 900;
                color: #0b4f7a;
                text-transform: uppercase;
                margin-bottom: .55rem;
            }

            .srv-mobile-item {
                border: 1px solid rgba(67, 89, 113, .14);
                border-radius: .55rem;
                padding: .6rem;
                margin-bottom: .55rem;
            }

            .srv-mobile-item-top {
                display: flex;
                justify-content: space-between;
                gap: .6rem;
            }

            .srv-mobile-item-name {
                font-size: 12px;
                font-weight: 900;
                line-height: 1.2;
            }

            .srv-mobile-item-code {
                font-size: 11px;
                color: #697a8d;
                margin-top: .15rem;
            }

            .srv-mobile-item-total {
                font-size: 13px;
                font-weight: 900;
                white-space: nowrap;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $patternPoints = $serviceTransaction->device_lock_type === 'pattern'
            ? collect(explode('-', (string) $serviceTransaction->device_lock_value))
                ->map(fn ($point) => (int) $point)
                ->filter(fn ($point) => $point >= 1 && $point <= 9)
                ->values()
                ->all()
            : [];

        $patternCenters = [
            1 => [28, 28], 2 => [66, 28], 3 => [104, 28],
            4 => [28, 66], 5 => [66, 66], 6 => [104, 66],
            7 => [28, 104], 8 => [66, 104], 9 => [104, 104],
        ];
        $serviceOutstandingAmount = max(0, (float) $serviceTransaction->grand_total - (float) $serviceTransaction->paid_amount);
        $serviceOutstanding = 'Rp ' . number_format($serviceOutstandingAmount, 0, ',', '.');
        $serviceIsTempo = ($serviceTransaction->payment_method ?? '') === 'tempo' || in_array($serviceTransaction->credit_status ?? 'paid', ['unpaid', 'partial'], true);
    @endphp
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        <div class="service-mobile-page">
            <div class="srv-detail-header">
                <div>
                    <div class="srv-detail-title">Detail Service</div>
                    <div class="srv-detail-code">{{ $serviceTransaction->service_code }}</div>
                </div>
                <div class="srv-detail-actions">
                    <a href="{{ route('service-transactions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-arrow-back"></i></a>
                    @if(auth()->user()->hasPermission('transactions.create'))
                        <a href="{{ route('service-transactions.create') }}" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i></a>
                    @endif
                </div>
            </div>

            <div class="srv-detail-card">
                <div class="srv-detail-total">
                    <div>
                        <div class="k">Grand Total</div>
                        @php
                            $statusColors = ['process' => 'warning', 'done' => 'info', 'taken' => 'success', 'cancelled' => 'secondary'];
                            $statusLabels = ['process' => 'Proses', 'done' => 'Selesai', 'taken' => 'Diambil', 'cancelled' => 'Batal'];
                        @endphp
                        <span class="badge bg-label-{{ $statusColors[$serviceTransaction->status] ?? 'secondary' }}">
                            {{ $statusLabels[$serviceTransaction->status] ?? $serviceTransaction->status }}
                        </span>
                    </div>
                    <div class="v">Rp {{ number_format((float) $serviceTransaction->grand_total, 0, ',', '.') }}</div>
                </div>
                <div class="srv-detail-grid">
                    <div>Subtotal</div><div class="v">Rp {{ number_format((float) $serviceTransaction->subtotal, 0, ',', '.') }}</div>
                    <div>Diskon</div><div class="v">Rp {{ number_format((float) $serviceTransaction->discount_total, 0, ',', '.') }}</div>
                    <div>Bayar</div><div class="v">Rp {{ number_format((float) $serviceTransaction->paid_amount, 0, ',', '.') }}</div>
                    <div>{{ $serviceIsTempo ? 'Sisa Tempo' : 'Kembali' }}</div><div class="v">{{ $serviceIsTempo ? $serviceOutstanding : 'Rp ' . number_format((float) $serviceTransaction->change_amount, 0, ',', '.') }}</div>
                    @if($serviceIsTempo)
                        <div>Jatuh Tempo</div><div class="v">{{ optional($serviceTransaction->credit_due_at)->format('d/m/Y') ?: '-' }}</div>
                    @endif
                </div>
            </div>

            <div class="srv-detail-card">
                <div class="srv-detail-section">Informasi</div>
                <div class="srv-detail-grid">
                    <div>Tanggal</div><div class="v">{{ optional($serviceTransaction->service_at)->format('d/m/Y H:i') ?: '-' }}</div>
                    <div>Pelanggan</div><div class="v">{{ $serviceTransaction->customer_name ?: '-' }}</div>
                    <div>No HP</div><div class="v">{{ $serviceTransaction->customer_phone ?: '-' }}</div>
                    <div>Barang</div><div class="v">{{ trim(($serviceTransaction->device_brand ?: '-') . ' ' . ($serviceTransaction->device_type ?: '')) }}</div>
                    <div>Serial</div><div class="v">{{ $serviceTransaction->serial_number ?: '-' }}</div>
                    <div>Teknisi</div><div class="v">{{ $serviceTransaction->technician?->name ?: '-' }}</div>
                    <div>Kunci</div><div class="v">{{ $serviceTransaction->device_lock_type ? (($serviceTransaction->device_lock_type === 'pattern' ? 'POLA' : 'PIN') . ' - ' . ($serviceTransaction->device_lock_value ?: '-')) : '-' }}</div>
                </div>
            </div>

            <div class="srv-detail-card">
                <div class="srv-detail-section">Item</div>
                @foreach($serviceTransaction->items as $item)
                    <div class="srv-mobile-item">
                        <div class="srv-mobile-item-top">
                            <div>
                                <div class="srv-mobile-item-name">{{ $item->name }}</div>
                                <div class="srv-mobile-item-code">{{ $item->item_type === 'service' ? 'Jasa' : 'Sparepart' }} | {{ $item->code }}</div>
                            </div>
                            <div class="srv-mobile-item-total">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</div>
                        </div>
                        <div class="srv-detail-grid">
                            <div>Qty</div><div class="v">{{ number_format((float) $item->quantity, 0, ',', '.') }}</div>
                            <div>Harga</div><div class="v">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</div>
                            <div>Disc</div><div class="v">Rp {{ number_format((float) $item->discount_value, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="srv-detail-card">
                <div class="srv-detail-section">Catatan</div>
                <div class="small text-muted mb-2">Keluhan</div>
                <div class="mb-3">{{ $serviceTransaction->complaint ?: '-' }}</div>
                <div class="small text-muted mb-2">Kelengkapan</div>
                <div>{{ $serviceTransaction->accessories ?: '-' }}</div>
            </div>

            @if($serviceTransaction->status !== 'cancelled' && auth()->user()->hasPermission('transactions.view'))
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#voidServiceTransactionModal">
                    <i class="bx bx-block me-1"></i> Void Service
                </button>
            @endif
            @if($serviceIsTempo && $serviceOutstandingAmount > 0 && $serviceTransaction->customer_id)
                <button type="button" class="btn btn-outline-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#servicePaymentModal">
                    <i class="bx bx-wallet me-1"></i> Input Pelunasan
                </button>
            @endif
        </div>

        <div class="card service-desktop-page">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $serviceTransaction->service_code }}</h5>
                    <div class="text-muted small">{{ optional($serviceTransaction->service_at)->format('d M Y H:i') }}</div>
                </div>
                <div class="d-flex gap-2">
                    @if($serviceTransaction->status !== 'cancelled' && auth()->user()->hasPermission('transactions.view'))
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#voidServiceTransactionModal">
                            <i class="bx bx-block me-1"></i> Void
                        </button>
                    @endif
                    @if($serviceIsTempo && $serviceOutstandingAmount > 0 && $serviceTransaction->customer_id)
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#servicePaymentModal">
                            <i class="bx bx-wallet me-1"></i> Input Pelunasan
                        </button>
                    @endif
                    <a href="{{ route('service-transactions.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4"><div class="text-muted small">Pelanggan</div><div class="fw-semibold">{{ $serviceTransaction->customer_name }}</div><div>{{ $serviceTransaction->customer_phone ?: '-' }}</div></div>
                    <div class="col-md-4">
                        <div class="text-muted small">Barang</div>
                        <div class="fw-semibold">{{ trim(($serviceTransaction->device_brand ?: '-') . ' ' . ($serviceTransaction->device_type ?: '')) }}</div>
                        <div>SN: {{ $serviceTransaction->serial_number ?: '-' }}</div>
                        <div>
                            Kunci:
                            @if($serviceTransaction->device_lock_type)
                                {{ $serviceTransaction->device_lock_type === 'pattern' ? 'POLA' : 'PIN' }} - {{ $serviceTransaction->device_lock_value ?: '-' }}
                                @if($serviceTransaction->device_lock_type === 'pattern' && count($patternPoints) > 0)
                                    <div class="pattern-preview">
                                        <svg viewBox="0 0 132 132" preserveAspectRatio="none">
                                            @for($i = 0; $i < count($patternPoints) - 1; $i++)
                                                @php
                                                    $from = $patternCenters[$patternPoints[$i]] ?? null;
                                                    $to = $patternCenters[$patternPoints[$i + 1]] ?? null;
                                                @endphp
                                                @if($from && $to)
                                                    <line x1="{{ $from[0] }}" y1="{{ $from[1] }}" x2="{{ $to[0] }}" y2="{{ $to[1] }}"
                                                        stroke="#0b4f7a" stroke-width="4" stroke-linecap="round" />
                                                @endif
                                            @endfor
                                        </svg>
                                        <div class="pattern-preview-grid">
                                            @for($dot = 1; $dot <= 9; $dot++)
                                                <div class="pattern-preview-dot {{ in_array($dot, $patternPoints, true) ? 'active' : '' }}">{{ $dot }}</div>
                                            @endfor
                                        </div>
                                    </div>
                                @endif
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4"><div class="text-muted small">Teknisi</div><div class="fw-semibold">{{ $serviceTransaction->technician?->name ?: '-' }}</div><div>Status: {{ $serviceTransaction->status }}</div></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Item</th><th>Jenis</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Disc</th><th class="text-end">Subtotal</th></tr></thead>
                        <tbody>
                            @foreach($serviceTransaction->items as $item)
                                <tr>
                                    <td><div class="fw-semibold">{{ $item->name }}</div><div class="text-muted small">{{ $item->code }}</div></td>
                                    <td>{{ $item->item_type === 'service' ? 'Jasa' : 'Sparepart' }}</td>
                                    <td class="text-end">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format((float) $item->discount_value, 0, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><th colspan="5" class="text-end">Grand Total</th><th class="text-end">Rp {{ number_format((float) $serviceTransaction->grand_total, 0, ',', '.') }}</th></tr>
                            <tr><th colspan="5" class="text-end">Bayar</th><th class="text-end">Rp {{ number_format((float) $serviceTransaction->paid_amount, 0, ',', '.') }}</th></tr>
                            <tr><th colspan="5" class="text-end">{{ $serviceIsTempo ? 'Sisa Tempo' : 'Kembali' }}</th><th class="text-end">{{ $serviceIsTempo ? $serviceOutstanding : 'Rp ' . number_format((float) $serviceTransaction->change_amount, 0, ',', '.') }}</th></tr>
                        </tfoot>
                    </table>
                </div>
                @if($serviceIsTempo)
                    <div class="alert alert-warning mt-3 mb-0">
                        Tempo {{ $serviceTransaction->credit_term_days ?: '-' }} hari,
                        jatuh tempo {{ optional($serviceTransaction->credit_due_at)->format('d M Y') ?: '-' }}.
                        Status: <strong>{{ ($serviceTransaction->credit_status ?? 'paid') === 'paid' ? 'Lunas' : 'Belum Lunas' }}</strong>.
                    </div>
                    <div class="mt-3">
                        <h6>Riwayat Pelunasan</h6>
                        @forelse($serviceTransaction->payments as $payment)
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                <div>
                                    <div class="fw-semibold">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
                                    <div class="text-muted small">{{ optional($payment->payment_at)->format('d M Y H:i') }} | {{ strtoupper($payment->payment_method) }}</div>
                                </div>
                                <div class="text-muted small">{{ $payment->reference ?: '-' }}</div>
                            </div>
                        @empty
                            <div class="text-muted">Belum ada pelunasan.</div>
                        @endforelse
                    </div>
                @endif
                <div class="row g-3 mt-2">
                    <div class="col-md-6"><div class="text-muted small">Keluhan</div><div>{{ $serviceTransaction->complaint ?: '-' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Kelengkapan</div><div>{{ $serviceTransaction->accessories ?: '-' }}</div></div>
                </div>
            </div>
        </div>
    </div>

    @if($serviceTransaction->status !== 'cancelled' && auth()->user()->hasPermission('transactions.view'))
        <div class="modal fade" id="voidServiceTransactionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('service-transactions.void', $serviceTransaction) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Void Transaksi Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning mb-3">
                                Transaksi service akan dibatalkan dan stok sparepart akan dikembalikan ke lokasi transaksi.
                            </div>
                            <label class="form-label">Alasan Void</label>
                            <textarea name="void_reason" class="form-control" rows="3" maxlength="500" placeholder="Opsional"></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="bx bx-block me-1"></i> Void Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($serviceIsTempo && $serviceOutstandingAmount > 0 && $serviceTransaction->customer_id)
        <div class="modal fade" id="servicePaymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('service-transactions.payments.store', $serviceTransaction) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Input Pelunasan Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">Sisa tempo: <strong>{{ $serviceOutstanding }}</strong></div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Bayar</label>
                                <input type="datetime-local" name="payment_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nominal</label>
                                <input type="number" min="1" max="{{ (int) $serviceOutstandingAmount }}" step="1" name="amount" class="form-control" value="{{ (int) $serviceOutstandingAmount }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Metode</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="cash">Cash</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Referensi</label>
                                <input type="text" name="reference" class="form-control" placeholder="Opsional">
                            </div>
                            <div>
                                <label class="form-label">Catatan</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="Opsional"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan Pelunasan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

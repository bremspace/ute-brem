@extends('layouts.sneat')

@section('title', 'Transaksi Service')

@push('styles')
    <style>
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

            .srv-mobile-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: .75rem;
                margin-bottom: .75rem;
            }

            .srv-mobile-title {
                font-size: 20px;
                font-weight: 900;
                color: #263238;
                line-height: 1.1;
            }

            .srv-mobile-subtitle {
                color: #697a8d;
                font-size: 12px;
                margin-top: .15rem;
            }

            .srv-mobile-actions {
                display: flex;
                gap: .4rem;
                flex: 0 0 auto;
            }

            .srv-mobile-list {
                display: grid;
                gap: .65rem;
            }

            .srv-mobile-card {
                background: #fff;
                border: 1px solid rgba(67, 89, 113, .14);
                border-radius: .65rem;
                padding: .7rem;
                box-shadow: 0 8px 18px rgba(32, 36, 44, .06);
            }

            .srv-mobile-top {
                display: flex;
                justify-content: space-between;
                gap: .75rem;
            }

            .srv-mobile-code {
                font-size: 12px;
                font-weight: 900;
                color: #0b4f7a;
                word-break: break-word;
            }

            .srv-mobile-total {
                font-size: 15px;
                font-weight: 900;
                color: #263238;
                white-space: nowrap;
            }

            .srv-mobile-meta {
                display: grid;
                gap: .2rem;
                margin-top: .4rem;
                color: #697a8d;
                font-size: 11px;
            }

            .srv-mobile-device {
                margin-top: .55rem;
                padding-top: .5rem;
                border-top: 1px dashed rgba(67, 89, 113, .18);
                font-size: 12px;
            }

            .srv-mobile-card-actions {
                display: grid;
                grid-template-columns: 1fr;
                gap: .45rem;
                margin-top: .65rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if (!$tableReady)
            <div class="alert alert-warning">Tabel transaksi service belum tersedia. Jalankan migration terlebih dahulu.</div>
        @endif

        <div class="service-mobile-page">
            <div class="srv-mobile-header">
                <div>
                    <div class="srv-mobile-title">Transaksi Service</div>
                    <div class="srv-mobile-subtitle">Riwayat service HP</div>
                </div>
                <div class="srv-mobile-actions">
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary"><i class="bx bx-home"></i></a>
                    @if (auth()->user()->hasPermission('transactions.create'))
                        <a href="{{ route('service-transactions.create') }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus me-1"></i> Baru
                        </a>
                    @endif
                </div>
            </div>

            <div class="srv-mobile-list">
                @forelse ($recentTransactions as $row)
                    @php
                        $statusColors = ['process' => 'warning', 'done' => 'info', 'taken' => 'success', 'cancelled' => 'secondary'];
                        $statusLabels = ['process' => 'Proses', 'done' => 'Selesai', 'taken' => 'Diambil', 'cancelled' => 'Batal'];
                    @endphp
                    <div class="srv-mobile-card">
                        <div class="srv-mobile-top">
                            <div>
                                <a href="{{ route('service-transactions.show', $row) }}" class="srv-mobile-code text-decoration-none">
                                    {{ $row->service_code }}
                                </a>
                                <div class="srv-mobile-meta">
                                    <span>{{ optional($row->service_at)->format('d/m/Y H:i') ?: '-' }}</span>
                                    <span>{{ $row->customer_name ?: 'Pelanggan umum' }}</span>
                                    <span>Teknisi: {{ $row->technician?->name ?: '-' }}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="srv-mobile-total">Rp {{ number_format((float) $row->grand_total, 0, ',', '.') }}</div>
                                <span class="badge bg-label-{{ $statusColors[$row->status] ?? 'secondary' }}">
                                    {{ $statusLabels[$row->status] ?? $row->status }}
                                </span>
                            </div>
                        </div>
                        <div class="srv-mobile-device">
                            <div class="fw-semibold">{{ trim(($row->device_brand ?: '-') . ' ' . ($row->device_type ?: '')) }}</div>
                            <div class="text-muted">Kasir: {{ $row->cashier?->name ?: '-' }}</div>
                        </div>
                        <div class="srv-mobile-card-actions">
                            <a href="{{ route('service-transactions.show', $row) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show me-1"></i> Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted small py-4">Belum ada transaksi service.</div>
                @endforelse
            </div>
        </div>

        <div class="card service-desktop-page">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h5 class="mb-1">Transaksi Service</h5>
                    <div class="text-muted small">Riwayat service HP dan sparepart yang dipakai.</div>
                </div>
                @if (auth()->user()->hasPermission('transactions.create'))
                    <a href="{{ route('service-transactions.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Service Baru</a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="service-transactions-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Barang</th>
                                <th>Teknisi</th>
                                <th>Total</th>
                                <th>Kasir</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#service-transactions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('service-transactions.data') }}',
                order: [[1, 'desc']],
                columns: [
                    { data: 'code_link', name: 'service_code' },
                    { data: 'date_label', name: 'service_at' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'device_label', name: 'device_brand', orderable: false },
                    { data: 'technician_label', name: 'technician.name', orderable: false },
                    { data: 'grand_total_label', name: 'grand_total', searchable: false },
                    { data: 'cashier_label', name: 'cashier.name', orderable: false },
                    { data: 'status_badge', name: 'status', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                dom: 'lBfrtip',
                buttons: ['copy', 'excel', 'pdf', 'print', 'colvis']
            });
        });
    </script>
@endpush

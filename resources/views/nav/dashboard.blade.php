@extends('internal.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Today's Sales</div>
                            <h3 class="fw-bold mb-0">Rp 12.5M</h3>
                        </div>
                        <div class="rounded bg-success bg-opacity-10 text-success p-2">
                            <i class="bi bi-cash-coin fs-5"></i>
                        </div>
                    </div>
                    <div class="text-success small mt-2">
                        <i class="bi bi-arrow-up"></i> +12% from yesterday
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Active Customers</div>
                            <h3 class="fw-bold mb-0">847</h3>
                        </div>
                        <div class="rounded bg-primary bg-opacity-10 text-primary p-2">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">
                        <i class="bi bi-info-circle"></i> +24 today
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Low Stock</div>
                            <h3 class="fw-bold mb-0">23</h3>
                        </div>
                        <div class="rounded bg-warning bg-opacity-10 text-warning p-2">
                            <i class="bi bi-exclamation-triangle fs-5"></i>
                        </div>
                    </div>
                    <div class="text-warning small mt-2">
                        <i class="bi bi-arrow-up"></i> Need reordering
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted small mb-1">Pending Approvals</div>
                            <h3 class="fw-bold mb-0">5</h3>
                        </div>
                        <div class="rounded bg-danger bg-opacity-10 text-danger p-2">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                    <div class="text-danger small mt-2">
                        <i class="bi bi-arrow-up"></i> 2 purchase orders
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Low Stock Alerts -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Recent Transactions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#Code</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>TRX-2026-0001</td>
                                    <td>PT. Bintang Jaya</td>
                                    <td>5</td>
                                    <td class="fw-medium">Rp 1,250,000</td>
                                    <td><span class="badge bg-success">Cash</span></td>
                                    <td class="text-success">Completed</td>
                                </tr>
                                <tr>
                                    <td>TRX-2026-0002</td>
                                    <td>PT. Sejahtera Abadi</td>
                                    <td>3</td>
                                    <td class="fw-medium">Rp 850,000</td>
                                    <td><span class="badge bg-info">QRIS</span></td>
                                    <td class="text-success">Completed</td>
                                </tr>
                                <tr>
                                    <td>TRX-2026-0003</td>
                                    <td>Ahmad (Member)</td>
                                    <td>2</td>
                                    <td class="fw-medium">Rp 250,000</td>
                                    <td><span class="badge bg-warning text-dark">Tempo</span></td>
                                    <td class="text-warning">Pending</td>
                                </tr>
                                <tr>
                                    <td>TRX-2026-0004</td>
                                    <td>Budi Santoso</td>
                                    <td>10</td>
                                    <td class="fw-medium">Rp 2,100,000</td>
                                    <td><span class="badge bg-success">Cash</span></td>
                                    <td class="text-success">Completed</td>
                                </tr>
                                <tr>
                                    <td>TRX-2026-0005</td>
                                    <td>CV. Maju Jaya</td>
                                    <td>1</td>
                                    <td class="fw-medium">Rp 150,000</td>
                                    <td><span class="badge bg-success">Cash</span></td>
                                    <td class="text-success">Completed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary btn-sm">
                            View All Transactions <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Low Stock Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                @foreach(['Injeksi AST 10L (5 pcs)', 'Oil Filter 2041 (3 pcs)', 'Spark Plug (6 pcs)', 'Air Filter (8 pcs)', 'Brake Pads Kit (4 pcs)'] as $item)
                                <tr>
                                    <td x-data="{ show: false }">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span x-text="{{ $item }}"></span>
                                            <button @click="show = !show"
                                                    class="text-muted small">...</button>
                                        </div>
                                        @if($show)
                                        <small class="text-muted">Reorder when stock < 5</small>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-warning btn-sm">
                            View All Stock <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
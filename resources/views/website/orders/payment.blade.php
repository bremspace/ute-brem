@extends('website.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="text-center mb-4">
                <h4 class="fw-bold">Status Pembayaran</h4>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <small class="text-muted">Nomor Pesanan</small>
                        <div class="fw-bold fs-5">{{ $order->order_code }}</div>
                    </div>

                    @php
                        $statusClass = match($order->status) {
                            'paid' => 'success',
                            'pending' => 'warning',
                            'cancelled' => 'danger',
                            default => 'secondary',
                        };
                        $statusText = match($order->status) {
                            'paid' => 'Pembayaran Berhasil',
                            'pending' => 'Menunggu Pembayaran',
                            'cancelled' => 'Pembayaran Dibatalkan',
                            default => ucfirst($order->status),
                        };
                    @endphp

                    <div class="text-center mb-3">
                        <span class="badge bg-{{ $statusClass }} fs-6 px-4 py-2">
                            @if($order->status === 'paid')
                                <i class="bx bx-check-circle me-1"></i>
                            @elseif($order->status === 'pending')
                                <i class="bx bx-time me-1"></i>
                            @else
                                <i class="bx bx-x-circle me-1"></i>
                            @endif
                            {{ $statusText }}
                        </span>
                    </div>

                    @if($order->status === 'pending')
                        <div class="text-center mb-3">
                            <small class="text-muted">Total Pembayaran</small>
                            <div class="fw-bold fs-4" style="color: #5c73f8;">
                                Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="alert alert-info rounded-3">
                            <strong>Metode: {{ strtoupper($order->payment_method ?? 'Transfer') }}</strong><br>
                            @if($order->payment_reference)
                                <small>Kode: {{ $order->payment_reference }}</small>
                            @endif
                            <small class="d-block mt-1">Silakan selesaikan pembayaran sesuai instruksi.</small>
                        </div>
                    @endif

                    @if($order->status === 'paid')
                        <div class="text-center">
                            <a href="{{ url('/orders/' . $order->order_code . '/track') }}" class="btn btn-primary" style="background-color: #5c73f8; border-color: #5c73f8;">
                                Lacak Pesanan
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center">
                <a href="{{ url('/') }}" class="text-decoration-none" style="color: #5c73f8;">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

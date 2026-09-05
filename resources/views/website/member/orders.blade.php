@extends('website.layout')

@section('content')
<div class="container py-4">
    <h4 class="fw-bold mb-4">Pesanan Saya</h4>

    @forelse($orders ?? [] as $order)
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <code class="fw-bold">{{ $order->order_code }}</code>
                        <small class="text-muted ms-2">{{ $order->created_at->format('d M Y H:i') }}</small>
                    </div>
                    @php
                        $badgeClass = match($order->status) {
                            'pending' => 'bg-warning text-dark',
                            'paid' => 'bg-info text-white',
                            'processing' => 'bg-primary text-white',
                            'shipped' => 'bg-primary text-white',
                            'delivered' => 'bg-success text-white',
                            'completed' => 'bg-success text-white',
                            'cancelled' => 'bg-danger text-white',
                            default => 'bg-secondary text-white',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        {{ $order->items->count() }} item · {{ $order->order_type === 'pickup' ? 'Ambil di Toko' : 'Pengiriman' }}
                    </div>
                    <div class="fw-bold" style="color: #5c73f8;">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </div>
                </div>
                <a href="{{ url('/orders/' . $order->order_code) }}" class="stretched-link"></a>
            </div>
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="bx bx-shopping-bag" style="font-size: 3rem;"></i>
            <p class="mt-2">Belum ada pesanan.</p>
            <a href="{{ url('/') }}" class="btn btn-primary" style="background-color: #5c73f8; border-color: #5c73f8;">Mulai Belanja</a>
        </div>
    @endforelse
</div>
@endsection

@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-2">
        <div class="d-flex gap-3">
            <button type="button" wire:click="setTab('orders')" class="btn btn-link text-decoration-none px-0 pb-2 border-0 fw-bold {{ $tab === 'orders' ? 'border-bottom border-primary border-3 text-primary' : 'text-muted' }}" style="{{ $tab === 'orders' ? 'color: #5c73f8 !important; border-color: #5c73f8 !important;' : '' }}">
                Pesanan Saya
            </button>
            <button type="button" wire:click="setTab('points')" class="btn btn-link text-decoration-none px-0 pb-2 border-0 fw-bold {{ $tab === 'points' ? 'border-bottom border-primary border-3 text-primary' : 'text-muted' }}" style="{{ $tab === 'points' ? 'color: #5c73f8 !important; border-color: #5c73f8 !important;' : '' }}">
                Poin Saya
            </button>
        </div>
        @if($customer)
            <div class="small text-muted">
                Halo, <strong>{{ $customer->name }}</strong> (<code>{{ $customer->member_code }}</code>)
            </div>
        @endif
    </div>

    @if(!$customer)
        <div class="card border-0 shadow-sm rounded-3 p-5 text-center my-4">
            <h5 class="fw-bold mb-2">Silakan Masuk Terlebih Dahulu</h5>
            <p class="text-muted mb-4">Masuk sebagai member untuk melihat riwayat pesanan dan perolehan poin Anda.</p>
            <div>
                <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}" class="btn text-white px-4 py-2 rounded-3" style="background-color: #5c73f8;">
                    Masuk Member
                </a>
            </div>
        </div>
    @else
        @if($tab === 'orders')
            @forelse($orders ?? [] as $order)
                <div class="card border-0 shadow-sm rounded-3 mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <code class="fw-bold">{{ $order->order_code }}</code>
                                <small class="text-muted ms-2">{{ $order->created_at?->format('d M Y H:i') }}</small>
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
                            <div class="text-muted small">
                                {{ $order->items->count() }} item · {{ $order->order_type === 'pickup' ? 'Ambil di Toko' : 'Pengiriman' }}
                            </div>
                            <div class="fw-bold" style="color: #5c73f8;">
                                {{ $formatRupiah($order->grand_total) }}
                            </div>
                        </div>
                        <a href="{{ url('/orders/' . $order->order_code) }}" class="stretched-link"></a>
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <p class="mt-2">Belum ada pesanan.</p>
                    <a href="{{ route('website.products.index') }}" class="btn text-white" style="background-color: #5c73f8;">Mulai Belanja</a>
                </div>
            @endforelse
        @elseif($tab === 'points')
            <div class="text-center mb-4 p-4 bg-white rounded-3 shadow-sm">
                <div class="display-4 fw-bold" style="color: #5c73f8;">
                    {{ $customer->points_balance ?? 0 }}
                </div>
                <small class="text-muted">Total Poin Member</small>
            </div>

            <h6 class="fw-bold mb-3">Riwayat Poin</h6>

            @forelse($pointsHistory ?? [] as $point)
                <div class="card border-0 shadow-sm rounded-3 mb-2">
                    <div class="card-body p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold small">{{ $point->description }}</div>
                            <small class="text-muted">{{ $point->created_at?->format('d M Y H:i') }}</small>
                        </div>
                        <span class="fw-bold {{ $point->points > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $point->points > 0 ? '+' : '' }}{{ $point->points }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted bg-white rounded-3 shadow-sm">
                    <p class="mb-0">Belum ada riwayat poin.</p>
                </div>
            @endforelse
        @endif
    @endif
</div>

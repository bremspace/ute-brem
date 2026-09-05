@extends('website.layout')

@section('content')
<div class="container py-4">
    <div class="text-center mb-4">
        <div class="display-4 fw-bold" style="color: #5c73f8;">
            {{ $customer->points_balance ?? 0 }}
        </div>
        <small class="text-muted">Poin Saya</small>
    </div>

    <h6 class="fw-bold mb-3">Riwayat Poin</h6>

    @forelse($pointsHistory ?? [] as $point)
        <div class="card border-0 shadow-sm rounded-3 mb-2">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold small">{{ $point->description }}</div>
                    <small class="text-muted">{{ $point->created_at->format('d M Y H:i') }}</small>
                </div>
                <span class="fw-bold {{ $point->points > 0 ? 'text-success' : 'text-danger' }}">
                    {{ $point->points > 0 ? '+' : '' }}{{ $point->points }}
                </span>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-muted">
            <p>Belum ada riwayat poin.</p>
        </div>
    @endforelse
</div>
@endsection

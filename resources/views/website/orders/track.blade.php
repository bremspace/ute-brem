@extends('website.layout')

@section('title', 'Lacak Pesanan — ' . ($appCompanyName ?? 'UTE Parts'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
    <style>
        .track-timeline {
            position: relative;
            padding-left: 1.75rem;
            margin: 0;
            list-style: none;
        }

        .track-timeline::before {
            content: '';
            position: absolute;
            left: 7px;
            top: 6px;
            bottom: 6px;
            width: 2px;
            background: var(--c-surface-200, #e5e7eb);
        }

        .track-timeline-item {
            position: relative;
            padding-bottom: 1.25rem;
        }

        .track-timeline-item:last-child {
            padding-bottom: 0;
        }

        .track-timeline-dot {
            position: absolute;
            left: -1.75rem;
            top: 2px;
            width: 16px;
            height: 16px;
            border-radius: 999px;
            background: var(--c-surface-0, #fff);
            border: 2px solid var(--c-surface-300, #d1d5db);
        }

        .track-timeline-item.is-done .track-timeline-dot {
            background: var(--c-primary, #5c73f8);
            border-color: var(--c-primary, #5c73f8);
        }

        .track-timeline-item.is-current .track-timeline-dot {
            border-color: var(--c-primary, #5c73f8);
            box-shadow: 0 0 0 4px rgba(92, 115, 248, .15);
        }

        .track-timeline-title {
            font-weight: 600;
            font-size: .8125rem;
            color: var(--c-surface-800, #1f2937);
        }

        .track-timeline-meta {
            font-size: .75rem;
            color: var(--c-surface-500, #6b7280);
        }

        .track-timeline-note {
            font-size: .75rem;
            color: var(--c-surface-600, #4b5563);
            margin-top: .25rem;
        }

        .track-status-badge {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            padding: .375rem .75rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            background: rgba(92, 115, 248, .1);
            color: var(--c-primary, #5c73f8);
        }
    </style>
@endpush

@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    $statusLabels = [
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Dibayar',
        'processing' => 'Diproses',
        'ready_for_pickup' => 'Siap Diambil',
        'shipped' => 'Dikirim',
        'in_transit' => 'Dalam Perjalanan',
        'delivered' => 'Terkirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'refunded' => 'Dikembalikan',
    ];

    $statusOrder = ['pending', 'paid', 'processing', 'ready_for_pickup', 'shipped', 'in_transit', 'delivered', 'completed'];
    $currentStatus = $order->status ?? 'pending';
    $currentIndex = array_search($currentStatus, $statusOrder, true);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;

    $trackings = $order->deliveryTrackings ?? collect();
@endphp

@section('content')
    <div class="shop-container">
        <header class="shop-page-header anim-fade-in">
            <h1 class="shop-page-title">Lacak Pesanan</h1>
            <p class="shop-page-sub">Kode pesanan: <strong>{{ $order->order_code }}</strong></p>
        </header>

        <div class="shop-grid-2">
            <div class="shop-stack">
                {{-- Status timeline --}}
                <div class="shop-card anim-slide-up">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h2 class="cart-summary-title mb-0">Status Pesanan</h2>
                        <span class="track-status-badge">{{ $statusLabels[$currentStatus] ?? ucfirst($currentStatus) }}</span>
                    </div>

                    @if (in_array($currentStatus, ['cancelled', 'refunded'], true))
                        <div class="shop-empty" style="padding:1.5rem">
                            <p class="mb-0" style="color:var(--c-danger, #ef4444);font-weight:600">
                                Pesanan ini {{ $statusLabels[$currentStatus] ?? 'tidak dilanjutkan' }}.
                            </p>
                        </div>
                    @else
                        <ol class="track-timeline">
                            @foreach ($statusOrder as $i => $status)
                                @if ($i > $currentIndex)
                                    @break
                                @endif
                                <li class="track-timeline-item {{ $i === $currentIndex ? 'is-current' : 'is-done' }}">
                                    <span class="track-timeline-dot"></span>
                                    <div class="track-timeline-title">{{ $statusLabels[$status] ?? ucfirst($status) }}</div>
                                    <div class="track-timeline-meta">
                                        @if ($i === $currentIndex)
                                            Status saat ini
                                        @else
                                            Selesai
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>

                {{-- Delivery tracking events --}}
                @if ($trackings->isNotEmpty())
                    <div class="shop-card anim-slide-up anim-delay-1">
                        <h2 class="cart-summary-title mb-3">Riwayat Pengiriman</h2>
                        <ol class="track-timeline">
                            @foreach ($trackings->sortByDesc('created_at') as $tracking)
                                <li class="track-timeline-item is-done">
                                    <span class="track-timeline-dot"></span>
                                    <div class="track-timeline-title">{{ $tracking->status ?? 'Update' }}</div>
                                    @if ($tracking->location)
                                        <div class="track-timeline-meta">{{ $tracking->location }}</div>
                                    @endif
                                    @if ($tracking->notes)
                                        <div class="track-timeline-note">{{ $tracking->notes }}</div>
                                    @endif
                                    <div class="track-timeline-meta">{{ $tracking->created_at?->format('d M Y, H:i') }}</div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>

            <div class="shop-summary-sticky">
                <div class="cart-summary shop-card anim-slide-up anim-delay-2">
                    <h2 class="cart-summary-title">Ringkasan Pesanan</h2>

                    @if ($order->items->isNotEmpty())
                        <div class="mb-3">
                            @foreach ($order->items as $item)
                                <div class="order-item">
                                    <span>{{ $item->product_name }} <span class="qty">× {{ (int) $item->quantity }}</span></span>
                                    <span class="price">{{ $formatRupiah($item->subtotal) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="cart-summary-row">
                        <span>Subtotal</span>
                        <span class="value">{{ $formatRupiah($order->subtotal) }}</span>
                    </div>
                    @if ((float) $order->discount_total > 0)
                        <div class="cart-summary-row is-discount">
                            <span>Diskon</span>
                            <span class="value">-{{ $formatRupiah($order->discount_total) }}</span>
                        </div>
                    @endif
                    <div class="cart-summary-row">
                        <span>Ongkir</span>
                        <span class="value">{{ (float) $order->shipping_cost > 0 ? $formatRupiah($order->shipping_cost) : 'Gratis' }}</span>
                    </div>
                    <div class="cart-summary-divider"></div>
                    <div class="cart-summary-total">
                        <span class="label">Total Bayar</span>
                        <span class="amount">{{ $formatRupiah($order->grand_total) }}</span>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        <a href="{{ route('website.order.show', $order->order_code) }}" class="shop-btn shop-btn-outline flex-grow-1">Detail Pesanan</a>
                        <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-primary flex-grow-1">Belanja Lagi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
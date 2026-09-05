@extends('website.layout')

@section('title', 'Dashboard Member')

@php
    // ============================================================
    // Data contract (defensive — page renders even without a
    // dedicated controller yet):
    //   $customer      — session array: id, name, type, points_balance
    //   $recentOrders  — Collection of OnlineOrder (last 5), each with:
    //                    order_code, status, grand_total, created_at,
    //                    items_count (or ->items loaded)
    //   $pointsBalance — int (fallback: customer.points_balance)
    //   $memberCode    — string (fallback: customer.member_code)
    //   $memberSince   — Carbon|string|null (e.g. "Jan 2026")
    //   $pointsRate    — int, rupiah value of 1 point (default 10)
    // ============================================================
    $customer     = $customer ?? session('website_customer');
    $customerName = $customer['name'] ?? 'Member';
    $pointsBalance = (int) ($pointsBalance ?? $customer['points_balance'] ?? 0);
    $memberCode   = $memberCode ?? $customer['member_code'] ?? '';
    $memberSince  = $memberSince ?? null;
    $pointsRate   = (int) ($pointsRate ?? 10);
    $recentOrders = $recentOrders ?? collect();

    $formatRupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

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

    $ordersUrl = \Illuminate\Support\Facades\Route::has('website.member.orders')
        ? route('website.member.orders')
        : url('/member/orders');
    $pointsUrl = \Illuminate\Support\Facades\Route::has('website.member.points')
        ? route('website.member.points')
        : url('/member/points');
    $addressUrl = \Illuminate\Support\Facades\Route::has('website.member.address')
        ? route('website.member.address')
        : '#';
@endphp

@push('styles')
    <style>
        /* ================================================================
           Member Dashboard — mobile-first, card-based, touch-friendly.
           Uses the consumer layout design tokens (--c-*).
           ================================================================ */

        /* --- Shell ------------------------------------------------------ */
        .member-page {
            max-width: 680px;
            margin: 0 auto;
            padding: 1rem 0 2rem;
        }

        .member-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .member-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            color: var(--c-surface-600);
            cursor: pointer;
            transition: all var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .member-back-btn:hover {
            color: var(--c-primary);
            border-color: var(--c-primary);
        }

        .member-back-btn:active {
            transform: scale(0.94);
        }

        .member-back-btn svg {
            width: 20px;
            height: 20px;
        }

        .member-header-titles {
            min-width: 0;
        }

        .member-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--c-surface-800);
            margin: 0;
            line-height: 1.3;
        }

        .member-sub {
            font-size: 0.8125rem;
            color: var(--c-surface-400);
            margin: 0.125rem 0 0;
        }

        /* --- Cards ------------------------------------------------------ */
        .member-card {
            background: var(--c-surface-0);
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-xl);
            box-shadow: var(--c-shadow-sm);
        }

        .member-card-pad {
            padding: 1rem;
        }

        .member-card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--c-surface-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0 0 0.75rem;
        }

        .member-card-link {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--c-primary);
            text-transform: none;
            letter-spacing: 0;
            min-height: 44px;
            padding: 0 0.25rem;
        }

        .member-card-link svg {
            width: 14px;
            height: 14px;
        }

        /* --- Welcome hero ---------------------------------------------- */
        .member-welcome {
            position: relative;
            overflow: hidden;
            border-radius: var(--c-radius-xl);
            background: linear-gradient(135deg, #4a5fd4 0%, var(--c-primary) 55%, #7c8cff 100%);
            color: #fff;
            padding: 1.5rem 1.25rem;
            margin-bottom: 1rem;
            box-shadow: var(--c-shadow-md);
        }

        .member-welcome::after {
            content: "";
            position: absolute;
            top: -45%;
            right: -12%;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .member-welcome-inner {
            position: relative;
            z-index: 1;
        }

        .member-welcome-greet {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
            margin: 0 0 0.25rem;
        }

        .member-welcome-name {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 0.5rem;
            letter-spacing: -0.01em;
            word-break: break-word;
        }

        .member-welcome-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1rem;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .member-welcome-meta span {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .member-welcome-meta svg {
            width: 14px;
            height: 14px;
        }

        /* --- Points hero ------------------------------------------------ */
        .member-points-hero {
            position: relative;
            overflow: hidden;
            border-radius: var(--c-radius-xl);
            background: linear-gradient(135deg, #4a5fd4 0%, var(--c-primary) 55%, #7c8cff 100%);
            color: #fff;
            padding: 1.5rem 1.25rem;
            margin-bottom: 1rem;
            box-shadow: var(--c-shadow-md);
        }

        .member-points-hero::before {
            content: "";
            position: absolute;
            bottom: -55%;
            left: -8%;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.14) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .member-points-hero-inner {
            position: relative;
            z-index: 1;
        }

        .member-points-label {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.6875rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.22);
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            margin-bottom: 0.75rem;
        }

        .member-points-label svg {
            width: 14px;
            height: 14px;
        }

        .member-points-value {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.02em;
            margin: 0 0 0.25rem;
        }

        .member-points-conversion {
            font-size: 0.8125rem;
            color: rgba(255, 255, 255, 0.85);
            margin: 0 0 1rem;
        }

        .member-points-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.625rem;
        }

        .member-points-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-height: 44px;
            padding: 0.625rem 1.125rem;
            border: none;
            border-radius: 999px;
            background: #fff;
            color: var(--c-primary);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
            transition: transform var(--c-transition), box-shadow var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .member-points-cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.22);
        }

        .member-points-cta:active {
            transform: translateY(0);
        }

        .member-points-cta svg {
            width: 16px;
            height: 16px;
        }

        /* --- Order rows ------------------------------------------------- */
        .member-order-list {
            display: flex;
            flex-direction: column;
        }

        .member-order {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            min-height: 44px;
            transition: background var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .member-order + .member-order {
            border-top: 1px solid var(--c-surface-100);
        }

        .member-order:hover {
            background: var(--c-surface-50);
        }

        .member-order-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: var(--c-radius-md);
            background: rgba(var(--c-primary-rgb), 0.1);
            color: var(--c-primary);
        }

        .member-order-icon svg {
            width: 20px;
            height: 20px;
        }

        .member-order-main {
            flex: 1;
            min-width: 0;
        }

        .member-order-code {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--c-surface-800);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .member-order-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.6875rem;
            color: var(--c-surface-400);
            margin-top: 0.125rem;
        }

        .member-order-total {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--c-surface-800);
            white-space: nowrap;
            text-align: right;
        }

        .member-order-chevron {
            width: 16px;
            height: 16px;
            color: var(--c-surface-300);
            flex-shrink: 0;
        }

        /* --- Status badges ---------------------------------------------- */
        .member-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.625rem;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .member-status .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .member-status-pending {
            background: rgba(245, 158, 11, 0.14);
            color: #b45309;
        }

        .member-status-paid,
        .member-status-processing,
        .member-status-ready_for_pickup {
            background: rgba(59, 130, 246, 0.14);
            color: #1d4ed8;
        }

        .member-status-shipped,
        .member-status-in_transit {
            background: rgba(92, 115, 248, 0.14);
            color: var(--c-primary);
        }

        .member-status-delivered,
        .member-status-completed {
            background: rgba(34, 197, 94, 0.14);
            color: #15803d;
        }

        .member-status-cancelled,
        .member-status-refunded {
            background: rgba(239, 68, 68, 0.14);
            color: #b91c1c;
        }

        /* --- Quick links ------------------------------------------------ */
        .member-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .member-quick-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 56px;
            padding: 0.75rem;
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            border: 1px solid var(--c-surface-200);
            color: var(--c-surface-700);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            text-align: left;
            transition: all var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .member-quick-item:hover {
            border-color: var(--c-primary);
            color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.08);
        }

        .member-quick-item:active {
            transform: scale(0.98);
        }

        .member-quick-item svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            color: var(--c-primary);
        }

        /* --- Logout ------------------------------------------------------ */
        .member-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            min-height: 44px;
            margin-top: 1rem;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            color: var(--c-danger);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .member-logout:hover {
            border-color: var(--c-danger);
            background: rgba(239, 68, 68, 0.04);
        }

        .member-logout:active {
            transform: scale(0.98);
        }

        .member-logout svg {
            width: 18px;
            height: 18px;
        }

        /* --- Empty state ------------------------------------------------ */
        .member-empty {
            text-align: center;
            padding: 2rem 1rem;
            color: var(--c-surface-400);
        }

        .member-empty svg {
            width: 40px;
            height: 40px;
            margin-bottom: 0.5rem;
            color: var(--c-surface-300);
        }

        .member-empty-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--c-surface-600);
            margin: 0 0 0.25rem;
        }

        .member-empty-desc {
            font-size: 0.8125rem;
            margin: 0;
        }

        /* --- Entrance animation ----------------------------------------- */
        .member-reveal {
            opacity: 0;
            transform: translateY(12px);
            animation: memberRevealUp 0.45s ease-out forwards;
        }

        .member-reveal-1 { animation-delay: 0.05s; }
        .member-reveal-2 { animation-delay: 0.12s; }
        .member-reveal-3 { animation-delay: 0.19s; }
        .member-reveal-4 { animation-delay: 0.26s; }

        @keyframes memberRevealUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .member-reveal {
                opacity: 1;
                transform: none;
                animation: none;
            }
        }

        @media (min-width: 640px) {
            .member-quick-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .member-welcome-name {
                font-size: 1.75rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl">
        <div class="member-page">

            {{-- ============================================================
                 Header
                 ============================================================ --}}
            <header class="member-header">
                <a href="{{ route('website.products.index') }}" class="member-back-btn" aria-label="Kembali ke beranda">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </a>
                <div class="member-header-titles">
                    <h1 class="member-title">Dashboard Member</h1>
                    <p class="member-sub">Kelola pesanan, poin, dan akun Anda.</p>
                </div>
            </header>

            {{-- ============================================================
                 Welcome hero
                 ============================================================ --}}
            <section class="member-welcome member-reveal member-reveal-1" aria-label="Selamat datang">
                <div class="member-welcome-inner">
                    <p class="member-welcome-greet">Halo, selamat datang kembali</p>
                    <h2 class="member-welcome-name">{{ $customerName }}</h2>
                    <div class="member-welcome-meta">
                        @if ($memberCode)
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                                {{ $memberCode }}
                            </span>
                        @endif
                        @if ($memberSince)
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                Member sejak {{ $memberSince instanceof \Carbon\CarbonInterface ? $memberSince->translatedFormat('M Y') : $memberSince }}
                            </span>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ============================================================
                 Points balance
                 ============================================================ --}}
            <section class="member-points-hero member-reveal member-reveal-2" aria-label="Saldo poin">
                <div class="member-points-hero-inner">
                    <span class="member-points-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Poin Saya
                    </span>
                    <p class="member-points-value">{{ number_format($pointsBalance, 0, ',', '.') }}</p>
                    <p class="member-points-conversion">≈ {{ $formatRupiah($pointsBalance * $pointsRate) }} (1 poin = {{ $formatRupiah($pointsRate) }})</p>
                    <div class="member-points-actions">
                        <a href="{{ $pointsUrl }}" class="member-points-cta">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Tukar Poin
                        </a>
                    </div>
                </div>
            </section>

            {{-- ============================================================
                 Recent orders
                 ============================================================ --}}
            <section class="member-card member-reveal member-reveal-3" aria-label="Pesanan terakhir">
                <div class="member-card-pad">
                    <h3 class="member-card-title">
                        Pesanan Terakhir
                        <a href="{{ $ordersUrl }}" class="member-card-link">
                            Lihat Semua
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </a>
                    </h3>

                    @if ($recentOrders->isNotEmpty())
                        <div class="member-order-list">
                            @foreach ($recentOrders->take(5) as $order)
                                @php
                                    $status = $order->status ?? 'pending';
                                    $statusLabel = $statusLabels[$status] ?? ucfirst((string) $status);
                                    $itemsCount = (int) ($order->items_count ?? $order->items?->count() ?? 0);
                                    $orderDate = $order->created_at ?? null;
                                @endphp
                                <a href="{{ route('website.order.show', $order->order_code) }}" class="member-order">
                                    <span class="member-order-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                                        </svg>
                                    </span>
                                    <span class="member-order-main">
                                        <span class="member-order-code">{{ $order->order_code }}</span>
                                        <span class="member-order-meta">
                                            <span class="member-status member-status-{{ $status }}"><span class="dot"></span>{{ $statusLabel }}</span>
                                            @if ($orderDate)
                                                <span>{{ $orderDate instanceof \Carbon\CarbonInterface ? $orderDate->translatedFormat('d M Y') : $orderDate }}</span>
                                            @endif
                                            <span>{{ $itemsCount }} item</span>
                                        </span>
                                    </span>
                                    <span class="member-order-total">{{ $formatRupiah($order->grand_total) }}</span>
                                    <svg class="member-order-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 18 15 12 9 6"/>
                                    </svg>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="member-empty">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                            </svg>
                            <p class="member-empty-title">Belum ada pesanan</p>
                            <p class="member-empty-desc">Pesanan Anda akan muncul di sini.</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ============================================================
                 Quick links
                 ============================================================ --}}
            <section class="member-reveal member-reveal-4" aria-label="Menu member">
                <div class="member-quick-grid">
                    <a href="{{ $ordersUrl }}" class="member-quick-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                            <line x1="12" y1="22.08" x2="12" y2="12"/>
                        </svg>
                        Pesanan Saya
                    </a>
                    <a href="{{ $pointsUrl }}" class="member-quick-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        Riwayat Poin
                    </a>
                    <a href="{{ route('website.member.password.edit', ['return' => request()->fullUrl()]) }}" class="member-quick-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Pengaturan Akun
                    </a>
                    <a href="{{ $addressUrl }}" class="member-quick-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Alamat
                    </a>
                </div>

                {{-- Logout --}}
                <form action="{{ route('website.member.logout') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="member-logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </section>

        </div>
    </div>
@endsection
@extends('website.layout')

@php
    $viewMode = request('view', 'grid');
    $viewMode = in_array($viewMode, ['grid', 'list'], true) ? $viewMode : 'grid';
    $selectedBrandIds = array_map('strval', (array) request()->input('brand_id', []));
    $selectedMakerIds = array_map('strval', (array) request()->input('maker_id', []));
    $selectedProductTypeIds = array_map('strval', (array) request()->input('product_type_id', []));
    $baseResetParams = [];
    if (request('share')) {
        $baseResetParams['share'] = request('share');
    }
    if ($viewMode) {
        $baseResetParams['view'] = $viewMode;
    }
    $activeCategoryId = request('category_id');
    $wsHero = \App\Models\WebsiteSetting::getGroup('hero');
@endphp

@push('styles')
    <style>
        /* ================================================================
           Consumer Catalog — Hero, Category Nav, Product Grid + Hover Popup
           Uses layout design tokens (--c-*). Mobile-first.
           ================================================================ */

        [x-cloak] { display: none !important; }

        /* --- Hero Banner ------------------------------------------------ */
        .catalog-hero {
            position: relative;
            overflow: hidden;
            border-radius: var(--c-radius-xl);
            background-color: var(--c-primary);
            background-image: radial-gradient(circle, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
            background-size: 24px 24px;
            color: #fff;
            padding: 2.5rem 1.5rem 2.75rem;
            margin: 0.75rem 0 1.5rem;
        }

        .catalog-hero-inner {
            position: relative;
            z-index: 1;
            max-width: 560px;
        }

        .catalog-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 0.35rem 0.875rem;
            border-radius: var(--c-radius-md);
            margin-bottom: 1rem;
        }

        .catalog-hero-badge-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #4ade80;
            box-shadow: 0 0 6px rgba(74, 222, 128, 0.5);
        }

        .catalog-hero-title {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
            margin: 0 0 0.75rem;
            letter-spacing: -0.015em;
        }

        .catalog-hero-sub {
            font-size: 0.875rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.82);
            margin: 0 0 1.5rem;
            max-width: 44ch;
        }

        .catalog-hero-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-height: 44px;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 999px;
            background: #fff;
            color: var(--c-primary);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            transition: box-shadow var(--c-transition);
        }

        .catalog-hero-cta:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
        }

        .catalog-hero-cta:active {
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .catalog-hero-cta svg {
            width: 18px;
            height: 18px;
        }

        /* --- Category Navigation (horizontal scroll) -------------------- */
        .category-nav {
            display: flex;
            gap: 0.625rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
            padding: 0.25rem 0.25rem 0.75rem;
            margin: 0 -0.25rem 1.25rem;
            scrollbar-width: none;
        }

        .category-nav::-webkit-scrollbar {
            display: none;
        }

        .category-chip {
            flex: 0 0 auto;
            scroll-snap-align: start;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            min-width: 84px;
            min-height: 44px;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            color: var(--c-surface-600);
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .category-chip:hover {
            border-color: var(--c-primary);
            color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.08);
        }

        .category-chip.is-active {
            background: var(--c-primary);
            border-color: var(--c-primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(var(--c-primary-rgb), 0.28);
        }

        .category-chip svg {
            width: 20px;
            height: 20px;
        }

        /* --- Sticky Mobile Search --------------------------------------- */
        .catalog-mobile-search {
            position: sticky;
            top: var(--c-nav-height);
            z-index: 900;
            background: rgba(248, 249, 252, 0.92);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            padding: 0.5rem 0;
            margin: 0 -0.75rem 1rem;
        }

        .catalog-mobile-search .consumer-search-form {
            max-width: var(--c-max-width);
            margin: 0 auto;
            padding: 0 0.75rem;
        }

        .catalog-mobile-search .consumer-search-input {
            height: 2.5rem;
            font-size: 0.9375rem;
            background: var(--c-surface-0);
            border-color: var(--c-surface-200);
        }

        @media (min-width: 1024px) {
            .catalog-mobile-search {
                display: none;
            }
        }

        /* --- Section heading -------------------------------------------- */
        .catalog-section-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .catalog-section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--c-surface-800);
            margin: 0;
        }

        .catalog-section-count {
            font-size: 0.75rem;
            color: var(--c-surface-400);
            white-space: nowrap;
        }

        /* --- Product Card ----------------------------------------------- */
        .product-card {
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            overflow: hidden;
            box-shadow: var(--c-shadow-sm);
            transition: box-shadow var(--c-transition);
            height: 100%;
        }

        .product-card:hover {
            box-shadow: var(--c-shadow-md);
        }

        .product-card-media {
            position: relative;
            aspect-ratio: 1 / 1;
            background: var(--c-surface-100);
            overflow: hidden;
        }

        .product-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-card-media .no-photo {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--c-surface-400);
            font-size: 0.75rem;
        }

        .product-card-stock {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            box-shadow: var(--c-shadow-sm);
        }

        .product-card-stock.is-ready {
            background: rgba(34, 197, 94, 0.92);
            color: #fff;
        }

        .product-card-stock.is-empty {
            background: rgba(107, 115, 133, 0.92);
            color: #fff;
        }

        .product-card-stock .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .product-card-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 0.375rem;
            padding: 0.75rem;
        }

        .product-card-maker {
            font-size: 0.6875rem;
            color: var(--c-surface-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-card-name {
            font-size: 0.8125rem;
            font-weight: 600;
            line-height: 1.35;
            color: var(--c-surface-800);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.2em;
            margin: 0;
        }

        .product-card-meta {
            font-size: 0.6875rem;
            color: var(--c-surface-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-card-footer {
            margin-top: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
            padding-top: 0.5rem;
        }

        .product-card-price {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--c-primary);
            white-space: nowrap;
        }

        .product-card-price .price-login {
            font-size: 0.6875rem;
            font-weight: 600;
            color: var(--c-primary);
        }

        .product-card-add {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            min-width: 44px;
            min-height: 44px;
            padding: 0 0.875rem;
            border: none;
            border-radius: var(--c-radius-md);
            background: var(--c-primary);
            color: #fff;
            font-family: inherit;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--c-transition);
            -webkit-tap-highlight-color: transparent;
        }

        .product-card-add:hover {
            background: var(--c-primary-hover);
        }

        .product-card-add:active {
            transform: scale(0.96);
        }

        .product-card-add svg {
            width: 18px;
            height: 18px;
        }

        .product-card-add.is-out {
            background: var(--c-surface-200);
            color: var(--c-surface-500);
            cursor: not-allowed;
        }

        /* --- Product Hover Popup ---------------------------------------- */
        .product-popup-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1099;
        }

        .product-popup {
            position: fixed;
            z-index: 1100;
            width: 420px;
            max-width: calc(100vw - 2rem);
            background: var(--c-surface-0);
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            box-shadow: var(--c-shadow-lg);
            overflow: hidden;
        }

        .product-popup-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border: none;
            border-radius: var(--c-radius-sm);
            background: var(--c-surface-100);
            color: var(--c-surface-500);
            cursor: pointer;
            transition: background var(--c-transition);
        }

        .product-popup-close:hover {
            background: var(--c-surface-200);
            color: var(--c-surface-700);
        }

        .product-popup-inner {
            display: flex;
            gap: 1rem;
            padding: 1rem;
        }

        .product-popup-image {
            flex: 0 0 140px;
            aspect-ratio: 1 / 1;
            border-radius: var(--c-radius-md);
            overflow: hidden;
            background: var(--c-surface-100);
        }

        .product-popup-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .product-popup-noimg {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--c-surface-400);
            font-size: 0.75rem;
        }

        .product-popup-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            padding-right: 1.5rem;
        }

        .product-popup-maker {
            font-size: 0.6875rem;
            color: var(--c-surface-400);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-popup-name {
            font-size: 0.9375rem;
            font-weight: 600;
            line-height: 1.3;
            color: var(--c-surface-800);
            margin: 0;
        }

        .product-popup-meta {
            font-size: 0.75rem;
            color: var(--c-surface-500);
        }

        .product-popup-brand {
            font-size: 0.75rem;
            color: var(--c-surface-500);
        }

        .product-popup-stock {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.6875rem;
            font-weight: 600;
            margin-top: 0.125rem;
        }

        .product-popup-stock.is-ready {
            color: #10b981;
        }

        .product-popup-stock.is-empty {
            color: var(--c-surface-400);
        }

        .product-popup-stock .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .product-popup-footer {
            margin-top: auto;
            padding-top: 0.5rem;
            border-top: 1px solid var(--c-surface-100);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .product-popup-price-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--c-primary);
        }

        .product-popup-price-login {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--c-primary);
            text-decoration: underline;
            text-decoration-color: rgba(var(--c-primary-rgb), 0.3);
        }

        .product-popup-add {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            flex: 1;
            min-height: 40px;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: var(--c-radius-md);
            background: var(--c-primary);
            color: #fff;
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--c-transition);
        }

        .product-popup-add:hover {
            background: var(--c-primary-hover);
        }

        .product-popup-add:disabled {
            background: var(--c-surface-200);
            color: var(--c-surface-500);
            cursor: not-allowed;
        }

        .product-popup-add svg {
            width: 16px;
            height: 16px;
        }

        /* --- Skeleton loading ------------------------------------------- */
        .skeleton {
            position: relative;
            overflow: hidden;
            background: var(--c-surface-100);
            border-radius: var(--c-radius-sm);
        }

        .skeleton::after {
            content: "";
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            animation: skeletonShimmer 1.4s infinite;
        }

        @keyframes skeletonShimmer {
            100% { transform: translateX(100%); }
        }

        .skeleton-card {
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            overflow: hidden;
        }

        .skeleton-card .sk-media {
            aspect-ratio: 1 / 1;
        }

        .skeleton-card .sk-body {
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .skeleton-card .sk-line {
            height: 0.75rem;
        }

        .skeleton-card .sk-line.short { width: 45%; }
        .skeleton-card .sk-line.medium { width: 70%; }

        .skeleton-col { display: none; }
        .js .skeleton-col { display: block; }

        .product-card-real { opacity: 1; transform: none; }
        .js .product-card-real { opacity: 0; transform: translateY(14px); }
        .js .product-card-real.is-revealed {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        /* --- Filter / List preserved styles ----------------------------- */
        .catalog-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
        }

        .view-toggle .btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        .filter-card .form-label {
            font-size: 12px;
            font-weight: 700;
            color: rgba(56, 69, 81, 0.8);
            margin-bottom: .25rem;
        }

        .filter-card .form-control,
        .filter-card .form-select {
            font-size: 13px;
            min-height: 38px;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }

        .filter-card .select2-container .select2-selection--single {
            min-height: 38px;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .filter-card .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.55rem;
        }

        .filter-card .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
        }

        .filter-card .select2-container .select2-selection--multiple {
            min-height: 38px;
            height: 38px;
            padding: 0.15rem 0.45rem;
            overflow-y: auto;
        }

        .filter-card .select2-container--default .select2-selection--single .select2-selection__placeholder,
        .filter-card .select2-container--default .select2-selection--multiple .select2-search__field::placeholder {
            color: #8a94a6;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
            min-height: 100%;
            align-items: center;
            align-content: center;
            padding: 0;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-search--inline {
            display: flex;
            align-items: center;
            margin: 0;
            line-height: 1;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-search__field {
            margin-top: 0 !important;
            height: 22px;
            line-height: 22px;
        }

        .filter-card .select2-container--default .select2-selection--multiple .select2-selection__choice {
            margin-top: 0;
            margin-bottom: 0;
        }

        .catalog-list-table th,
        .catalog-list-table td {
            vertical-align: middle;
        }

        .catalog-list-table td {
            white-space: normal;
        }

        /* --- Responsive ------------------------------------------------- */
        @media (max-width: 575.98px) {
            .container-xxl {
                padding-left: .75rem;
                padding-right: .75rem;
            }

            .catalog-hero {
                padding: 1.75rem 1.25rem 2rem;
                margin-top: 0.5rem;
            }

            .catalog-hero-title {
                font-size: 1.375rem;
            }

            .filter-card .card-body {
                padding: .75rem;
            }
        }

        @media (max-width: 1023.98px) {
            .product-popup-overlay {
                display: block;
            }

            .product-popup {
                width: calc(100vw - 2rem) !important;
                left: 1rem !important;
                right: 1rem;
                top: 50% !important;
                transform: translateY(-50%);
                max-height: 80vh;
                overflow-y: auto;
            }

            .product-popup-inner {
                flex-direction: column;
            }

            .product-popup-image {
                flex: 0 0 auto;
                max-height: 200px;
            }

            .product-popup-details {
                padding-right: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .product-popup-overlay {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .js .product-card-real {
                opacity: 1;
                transform: none;
            }

            .skeleton::after {
                animation: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-xxl py-3 py-lg-5">

        {{-- ============================================================
             Hero Banner
             ============================================================ --}}
        <section class="catalog-hero" aria-label="Promo {{ $wsStoreName ?? 'UTE Parts' }}">
            <div class="catalog-hero-inner">
                <div class="catalog-hero-badge">
                    <span class="catalog-hero-badge-dot"></span>
                    {{ number_format($products->total(), 0, ',', '.') }}+ Produk Tersedia
                </div>
                <h1 class="catalog-hero-title">{{ $wsHero['title'] ?? 'Semua Kebutuhan HP Anda, Satu Tempat' }}</h1>
                <p class="catalog-hero-sub">
                    {{ $wsHero['subtitle'] ?? 'LCD, baterai, charger, casing, dan aksesoris HP berkualitas dengan harga terbaik.' }}
                    Cari berdasarkan kategori, brand, tipe HP, dan merek produksi.
                </p>
                <a href="{{ $wsHero['cta_link'] ?? '#catalogProducts' }}" class="catalog-hero-cta">
                    {{ $wsHero['cta_text'] ?? 'Lihat Produk' }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-chevron"/>
                    </svg>
                </a>
            </div>
        </section>

        {{-- ============================================================
             Category Navigation (horizontal scroll)
             ============================================================ --}}
        @if (count($categories) > 0)
            <nav class="category-nav" aria-label="Kategori produk">
                <a href="{{ route('website.products.index', array_merge($baseResetParams, ['category_id' => ''])) }}"
                    class="category-chip {{ $activeCategoryId === null || $activeCategoryId === '' ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-grid"/>
                    </svg>
                    Semua
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('website.products.index', array_merge($baseResetParams, ['category_id' => $category->id])) }}"
                        class="category-chip {{ (string) $activeCategoryId === (string) $category->id ? 'is-active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="#c-icon-package"/>
                        </svg>
                        {{ $category->name }}
                    </a>
                @endforeach
            </nav>
        @endif

        {{-- ============================================================
             Sticky Mobile Search
             ============================================================ --}}
        <div class="catalog-mobile-search">
            <form action="{{ route('website.products.index') }}" method="GET" class="consumer-search-form" role="search">
                <input type="text" name="q" class="consumer-search-input"
                    placeholder="Cari sparepart HP..."
                    value="{{ request('q') }}"
                    aria-label="Cari produk">
                <svg class="consumer-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#c-icon-search"/>
                </svg>
            </form>
        </div>

        {{-- ============================================================
             Section heading + Filter toggle
             ============================================================ --}}
        <div class="catalog-section-head">
            <div>
                <h2 class="catalog-section-title">Data Barang {{ $wsStoreName ?? 'UTE Parts' }}</h2>
                <p class="text-muted mb-0 small">
                    Cari sparepart HP berdasarkan kategori, brand, tipe HP, dan merek produksi.
                    @if ($isSharedCatalog)
                        <span class="badge bg-label-primary ms-1">Katalog pilihan</span>
                    @endif
                </p>
            </div>
            <span class="catalog-section-count">{{ $products->total() }} produk</span>
        </div>

        {{-- Filter card --}}
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" id="catalogFilterForm" action="{{ route('website.products.index') }}" class="row g-2 align-items-end">
                    @if (request('share'))
                        <input type="hidden" name="share" value="{{ request('share') }}">
                    @endif
                    <input type="hidden" name="view" id="viewModeInput" value="{{ $viewMode }}">

                    <div class="col-12 col-lg-4">
                        <label class="form-label">Cari</label>
                        <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                            placeholder="Nama / kode / barcode">
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select form-select-sm js-catalog-select-single"
                            data-placeholder="Semua Kategori">
                            <option value="">Semua</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Sub Kategori</label>
                        <select name="sub_category_id" class="form-select form-select-sm js-catalog-select-single"
                            data-placeholder="Semua Sub Kategori">
                            <option value="">Semua</option>
                            @foreach ($subCategories as $subCategory)
                                <option value="{{ $subCategory->id }}" @selected((string) request('sub_category_id') === (string) $subCategory->id)>{{ $subCategory->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Brand HP</label>
                        <select name="brand_id[]" class="form-select form-select-sm js-catalog-select-multiple" multiple
                            data-placeholder="Semua Brand">
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" @selected(in_array((string) $brand->id, $selectedBrandIds, true))>{{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg">
                        <label class="form-label">Merek</label>
                        <select name="maker_id[]" class="form-select form-select-sm js-catalog-select-multiple" multiple
                            data-placeholder="Semua Merek">
                            @foreach ($makers as $maker)
                                <option value="{{ $maker->id }}" @selected(in_array((string) $maker->id, $selectedMakerIds, true))>{{ $maker->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg">
                        <label class="form-label">Tipe HP</label>
                        <select name="product_type_id[]" class="form-select form-select-sm js-catalog-select-multiple"
                            multiple data-placeholder="Semua Tipe HP">
                            @foreach ($productTypes as $type)
                                <option value="{{ $type->id }}" @selected(in_array((string) $type->id, $selectedProductTypeIds, true))>{{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="catalog-toolbar">
                            <div class="btn-group view-toggle" role="group" aria-label="Mode tampilan">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="viewGridBtn"
                                    aria-pressed="{{ $viewMode === 'grid' ? 'true' : 'false' }}">
                                    <i class="bx bx-grid-alt"></i> Grid
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="viewListBtn"
                                    aria-pressed="{{ $viewMode === 'list' ? 'true' : 'false' }}">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                            </div>

                            <a href="{{ route('website.products.index', $baseResetParams) }}"
                                class="btn btn-outline-secondary btn-sm">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================
             Product listing
             ============================================================ --}}
        <div id="catalogProducts">
            @if ($viewMode === 'list')
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 catalog-list-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th class="text-center" style="width:110px;">Stok</th>
                                    <th class="d-none d-lg-table-cell" style="width:170px;">Merek</th>
                                    <th class="d-none d-lg-table-cell" style="width:220px;">Kategori</th>
                                    <th class="d-none d-lg-table-cell" style="width:220px;">Brand</th>
                                    <th class="d-none d-lg-table-cell" style="width:250px;">Tipe HP</th>
                                    <th class="text-end" style="width:160px;">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php
                                        $ready = (float) $product->stock_global > 0;
                                        $canSeePrice = !empty($customer) && ($customer['type'] ?? null) === 'member';
                                        $searchTerm = trim((string) request('q', ''));
                                        $typeItems = $product->productTypes?->pluck('name')->filter()->values()->all() ?? [];
                                        $typeCount = count($typeItems);
                                        $typeLimit = 2;
                                        $shouldExpandTypes = $searchTerm !== '';
                                        $shownTypes = $shouldExpandTypes ? $typeItems : array_slice($typeItems, 0, $typeLimit);
                                        $hiddenTypes = $shouldExpandTypes ? [] : array_slice($typeItems, $typeLimit);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $product->name }}</div>
                                            <div class="text-muted small">{{ $product->product_code }}</div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge {{ $ready ? 'bg-label-success' : 'bg-label-secondary' }}">{{ $ready ? 'Ready' : 'Kosong' }}</span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            {{ $product->maker?->name ?: '-' }}
                                            @if (!empty($product->quality))
                                                <div class="text-muted small">{{ $product->quality }}</div>
                                            @endif
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            {{ $product->category?->name ?: '-' }}
                                            @if ($product->subCategory)
                                                / {{ $product->subCategory->name }}
                                            @endif
                                        </td>
                                        <td class="d-none d-lg-table-cell">{{ $product->brand?->name ?: '-' }}</td>
                                        <td class="d-none d-lg-table-cell">
                                            @if ($typeCount === 0)
                                                -
                                            @else
                                                {{ implode(', ', $shownTypes) }}
                                                @if (count($hiddenTypes) > 0)
                                                    <span class="text-muted" title="{{ implode(', ', $hiddenTypes) }}">+{{ count($hiddenTypes) }}</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if ($canSeePrice)
                                                <span class="fw-bold text-primary">Rp
                                                    {{ number_format((float) $product->selling_price, 0, ',', '.') }}</span>
                                            @else
                                                <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    Lihat Harga
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">Produk tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <script>document.documentElement.classList.add('js');</script>
                <div class="row g-3" id="productGrid">
                    @forelse($products as $product)
                        @php
                            $ready = (float) $product->stock_global > 0;
                            $canSeePrice = !empty($customer) && ($customer['type'] ?? null) === 'member';
                            $searchTerm = trim((string) request('q', ''));
                            $typeItems = $product->productTypes?->pluck('name')->filter()->values()->all() ?? [];
                            $typeCount = count($typeItems);
                            $typeLimit = 2;
                            $shouldExpandTypes = $searchTerm !== '';
                            $shownTypes = $shouldExpandTypes ? $typeItems : array_slice($typeItems, 0, $typeLimit);
                            $hiddenTypes = $shouldExpandTypes ? [] : array_slice($typeItems, $typeLimit);

                            $popupData = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'image' => $product->primaryImageUrl(),
                                'category' => $product->category?->name,
                                'subcategory' => $product->subCategory?->name,
                                'brand' => $product->brand?->name,
                                'types' => $typeItems,
                                'maker' => $product->maker?->name,
                                'quality' => $product->quality,
                                'stock' => $ready,
                                'price' => (float) $product->selling_price,
                                'canSeePrice' => $canSeePrice,
                                'loginUrl' => route('website.member.login', ['return' => request()->fullUrl()])
                            ];
                        @endphp
                        <div class="col-6 col-lg-4 col-xl-3" x-data="productPopup(@js($popupData))">
                            <article class="product-card product-card-real" x-ref="card"
                                @mouseenter="showPopup()"
                                @mouseleave="hidePopup()"
                                @click="toggleMobile($event)">
                                <div class="product-card-media">
                                    @if ($product->primaryImageUrl())
                                        <img src="{{ $product->primaryImageUrl() }}" alt="{{ $product->name }}" loading="lazy">
                                    @else
                                        <div class="no-photo">No Photo</div>
                                    @endif
                                    <span class="product-card-stock {{ $ready ? 'is-ready' : 'is-empty' }}">
                                        <span class="dot"></span>
                                        {{ $ready ? 'Ready' : 'Kosong' }}
                                    </span>
                                </div>
                                <div class="product-card-body">
                                    <div class="product-card-maker">
                                        {{ $product->maker?->name ?: 'Tanpa merek' }}
                                        @if (!empty($product->quality))
                                            · {{ $product->quality }}
                                        @endif
                                    </div>
                                    <h3 class="product-card-name">{{ $product->name }}</h3>
                                    <div class="product-card-meta">
                                        {{ $product->category?->name ?: '-' }}
                                        @if ($product->subCategory)
                                            / {{ $product->subCategory->name }}
                                        @endif
                                        @if ($product->brand)
                                            · {{ $product->brand->name }}
                                            @if ($typeCount > 0)
                                                - {{ implode(', ', $shownTypes) }}
                                                @if (count($hiddenTypes) > 0)
                                                    <span class="text-muted" title="{{ implode(', ', $hiddenTypes) }}">+{{ count($hiddenTypes) }}</span>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                    <div class="product-card-footer">
                                        @if ($canSeePrice)
                                            <span class="product-card-price">Rp
                                                {{ number_format((float) $product->selling_price, 0, ',', '.') }}</span>
                                        @else
                                            <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                                                class="product-card-price price-login">
                                                Lihat Harga
                                            </a>
                                        @endif
                                        @if ($ready)
                                            <button type="button" class="product-card-add"
                                                data-product-id="{{ $product->id }}"
                                                data-product-name="{{ $product->name }}"
                                                aria-label="Tambah {{ $product->name }} ke keranjang">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <use href="#c-icon-cart"/>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button" class="product-card-add is-out" disabled
                                                aria-label="Stok kosong">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <use href="#c-icon-cart"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </article>

                            {{-- Hover / Tap Popup --}}
                            <div x-show="show" x-cloak class="product-popup-overlay"
                                @click="closePopup()"></div>
                            <div x-ref="popup" x-show="show" x-cloak class="product-popup"
                                :style="popupStyle" role="dialog" aria-label="Detail produk"
                                @mouseenter="onPopupEnter()" @mouseleave="onPopupLeave()">
                                <button type="button" class="product-popup-close" @click="closePopup()" aria-label="Tutup">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px">
                                        <path d="M18 6L6 18M6 6l12 12"/>
                                    </svg>
                                </button>
                                <div class="product-popup-inner">
                                    <div class="product-popup-image">
                                        <template x-if="product.image">
                                            <img :src="product.image" :alt="product.name">
                                        </template>
                                        <template x-if="!product.image">
                                            <div class="product-popup-noimg">No Photo</div>
                                        </template>
                                    </div>
                                    <div class="product-popup-details">
                                        <div class="product-popup-maker" x-text="(product.maker || 'Tanpa merek') + (product.quality ? ' · ' + product.quality : '')"></div>
                                        <h3 class="product-popup-name" x-text="product.name"></h3>
                                        <div class="product-popup-meta" x-text="(product.category || '-') + (product.subcategory ? ' / ' + product.subcategory : '')"></div>
                                        <div class="product-popup-brand" x-text="(product.brand || '') + (product.types && product.types.length ? ' · ' + product.types.join(', ') : '')"></div>
                                        <div class="product-popup-stock" :class="product.stock ? 'is-ready' : 'is-empty'">
                                            <span class="dot"></span>
                                            <span x-text="product.stock ? 'Ready' : 'Kosong'"></span>
                                        </div>
                                        <div class="product-popup-footer">
                                            <template x-if="product.canSeePrice">
                                                <span class="product-popup-price-value">Rp <span x-text="new Intl.NumberFormat('id-ID').format(product.price)"></span></span>
                                            </template>
                                            <template x-if="!product.canSeePrice">
                                                <a :href="product.loginUrl" class="product-popup-price-login">Lihat Harga</a>
                                            </template>
                                            <template x-if="product.stock">
                                                <button type="button" class="product-popup-add"
                                                    @click="addToCart($event)">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <use href="#c-icon-cart"/>
                                                    </svg>
                                                    Tambah ke Keranjang
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center text-muted py-5">Produk tidak ditemukan.</div>
                            </div>
                        </div>
                    @endforelse

                    @if ($products->count() > 0)
                        @for ($i = 0; $i < min(8, $products->count()); $i++)
                            <div class="col-6 col-lg-4 col-xl-3 skeleton-col">
                                <div class="skeleton-card">
                                    <div class="skeleton sk-media"></div>
                                    <div class="sk-body">
                                        <div class="skeleton sk-line short"></div>
                                        <div class="skeleton sk-line"></div>
                                        <div class="skeleton sk-line medium"></div>
                                        <div class="skeleton sk-line short"></div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    @endif
                </div>
            @endif
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /* --- Alpine.js Product Popup Component --------------------------- */
        document.addEventListener('alpine:init', function() {
            Alpine.data('productPopup', function(product) {
                return {
                    show: false,
                    product: product,
                    popupStyle: '',
                    _hideTimer: null,
                    _onScroll: null,
                    _onResize: null,

                    get isDesktop() {
                        return window.innerWidth >= 1024;
                    },

                    showPopup: function() {
                        if (!this.isDesktop) return;
                        clearTimeout(this._hideTimer);
                        this.show = true;
                        this.$nextTick(function() {
                            requestAnimationFrame(this._position.bind(this));
                        }.bind(this));
                    },

                    hidePopup: function() {
                        if (!this.isDesktop) return;
                        var self = this;
                        this._hideTimer = setTimeout(function() {
                            self.show = false;
                        }, 120);
                    },

                    onPopupEnter: function() {
                        clearTimeout(this._hideTimer);
                    },

                    onPopupLeave: function() {
                        if (!this.isDesktop) return;
                        this.show = false;
                    },

                    toggleMobile: function($event) {
                        if (this.isDesktop) return;
                        if ($event.target.closest('.product-card-add') ||
                            $event.target.closest('.product-popup-add') ||
                            $event.target.closest('.product-popup-close')) return;
                        this.show = !this.show;
                        document.body.classList.toggle('popup-open', this.show);
                        if (this.show) {
                            var self = this;
                            this.$nextTick(function() {
                                requestAnimationFrame(self._position.bind(self));
                            });
                        }
                    },

                    closePopup: function() {
                        this.show = false;
                        document.body.classList.remove('popup-open');
                    },

                    _position: function() {
                        if (!this.isDesktop) return;
                        var card = this.$refs.card;
                        var popup = this.$refs.popup;
                        if (!card || !popup) return;

                        var rect = card.getBoundingClientRect();
                        var gap = 12;
                        var vw = window.innerWidth;
                        var vh = window.innerHeight;
                        var popW = Math.min(popup.offsetWidth || 420, vw - 16);
                        var popH = popup.offsetHeight || 340;

                        var left, top;

                        if (rect.right + gap + popW <= vw - 8) {
                            left = rect.right + gap;
                        } else if (rect.left - gap - popW >= 8) {
                            left = rect.left - gap - popW;
                        } else {
                            left = Math.max(8, (vw - popW) / 2);
                        }

                        top = rect.top;
                        if (top + popH > vh - 8) {
                            top = Math.max(8, vh - popH - 8);
                        }
                        if (top < 8) top = 8;

                        this.popupStyle = 'left:' + left + 'px;top:' + top + 'px;';
                    },

                    addToCart: function($event) {
                        $event.preventDefault();
                        $event.stopPropagation();

                        var self = this;
                        var btn = $event.currentTarget;
                        btn.disabled = true;

                        var csrfToken = document.querySelector('meta[name="csrf-token"]');

                        fetch({!! json_encode(route('website.cart.add')) !!}, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                            },
                            body: JSON.stringify({ product_id: self.product.id, quantity: 1 })
                        })
                        .then(function(res) {
                            return res.json().then(function(data) {
                                return { ok: res.ok, data: data };
                            });
                        })
                        .then(function(result) {
                            if (result.ok && result.data.success) {
                                if (window.UTEParts && window.UTEParts.updateCartCount) {
                                    window.UTEParts.updateCartCount(result.data.cart_count);
                                }
                                self._toast(result.data.message || (self.product.name + ' ditambahkan ke keranjang'));
                            } else {
                                self._toast(result.data.message || 'Gagal menambahkan produk.', true);
                            }
                        })
                        .catch(function() {
                            self._toast('Gagal menambahkan produk. Coba lagi.', true);
                        })
                        .finally(function() {
                            btn.disabled = false;
                        });
                    },

                    _toast: function(message, isError) {
                        var existing = document.querySelector('.consumer-toast');
                        if (existing) existing.remove();
                        var toast = document.createElement('div');
                        toast.className = 'consumer-toast';
                        if (isError) toast.style.background = 'var(--c-danger)';
                        toast.textContent = message;
                        document.body.appendChild(toast);
                        setTimeout(function() {
                            toast.classList.add('is-hidden');
                            setTimeout(function() { toast.remove(); }, 250);
                        }, 2200);
                    },

                    init: function() {
                        var self = this;
                        this._onScroll = function() { if (self.show) self.closePopup(); };
                        this._onResize = function() { if (self.show) self._position(); };
                        window.addEventListener('scroll', this._onScroll, { passive: true });
                        window.addEventListener('resize', this._onResize);
                    },

                    destroy: function() {
                        if (this._onScroll) window.removeEventListener('scroll', this._onScroll);
                        if (this._onResize) window.removeEventListener('resize', this._onResize);
                        document.body.classList.remove('popup-open');
                    }
                };
            });
        });

        /* --- Existing functionality (filters, view toggle, add-to-cart) --- */
        (function() {
            var form = document.getElementById('catalogFilterForm');
            if (!form) return;
            var submitTimer = null;

            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $(form).find('.js-catalog-select-single').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return this.dataset.placeholder || 'Semua';
                    }
                });

                $(form).find('.js-catalog-select-multiple').select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: function() {
                        return this.dataset.placeholder || 'Pilih';
                    }
                });
            }

            var viewInput = document.getElementById('viewModeInput');
            var gridBtn = document.getElementById('viewGridBtn');
            var listBtn = document.getElementById('viewListBtn');

            function submitForm() {
                form.submit();
            }

            function queueSubmit(delay) {
                clearTimeout(submitTimer);
                submitTimer = setTimeout(submitForm, delay || 0);
            }

            function setView(mode) {
                if (!viewInput) return;
                viewInput.value = mode;
                submitForm();
            }

            if (gridBtn) gridBtn.addEventListener('click', function() { setView('grid'); });
            if (listBtn) listBtn.addEventListener('click', function() { setView('list'); });

            var searchInput = form.querySelector('input[name="q"]');
            var searchTimer = null;
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(function() { queueSubmit(); }, 450);
                });
            }

            form.querySelectorAll('select').forEach(function(selectEl) {
                selectEl.addEventListener('change', function() { queueSubmit(); });
            });

            if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
                $(form).find('select').on('select2:select select2:unselect select2:clear', function() {
                    queueSubmit();
                });
            }

            /* --- Skeleton to real card reveal ----------------------------- */
            var skeletonCols = document.querySelectorAll('.skeleton-col');
            var realCards = document.querySelectorAll('.product-card-real');

            if (realCards.length > 0) {
                requestAnimationFrame(function() {
                    setTimeout(function() {
                        skeletonCols.forEach(function(col) { col.remove(); });
                        realCards.forEach(function(card, i) {
                            setTimeout(function() {
                                card.classList.add('is-revealed');
                            }, i * 45);
                        });
                    }, 300);
                });
            } else {
                skeletonCols.forEach(function(col) { col.remove(); });
            }

            /* --- Add-to-cart (card buttons) -------------------------------- */
            var addButtons = document.querySelectorAll('.product-card-add:not(.is-out)');
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var cartUrl = {!! json_encode(route('website.cart.add')) !!};

            addButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = btn.dataset.productId;
                    var name = btn.dataset.productName || 'produk';
                    btn.disabled = true;

                    fetch(cartUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.content : ''
                        },
                        body: JSON.stringify({ product_id: id, quantity: 1 })
                    })
                    .then(function(res) {
                        return res.json().then(function(data) {
                            return { ok: res.ok, data: data };
                        });
                    })
                    .then(function(result) {
                        if (result.ok && result.data.success) {
                            if (window.UTEParts && window.UTEParts.updateCartCount) {
                                window.UTEParts.updateCartCount(result.data.cart_count);
                            }
                            showToast(result.data.message || (name + ' ditambahkan ke keranjang'));
                        } else {
                            showToast(result.data.message || 'Gagal menambahkan produk.', true);
                        }
                    })
                    .catch(function() {
                        showToast('Gagal menambahkan produk. Coba lagi.', true);
                    })
                    .finally(function() {
                        btn.disabled = false;
                    });
                });
            });

            function showToast(message, isError) {
                var existing = document.querySelector('.consumer-toast');
                if (existing) existing.remove();
                var toast = document.createElement('div');
                toast.className = 'consumer-toast';
                if (isError) {
                    toast.style.background = 'var(--c-danger)';
                }
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(function() {
                    toast.classList.add('is-hidden');
                    setTimeout(function() { toast.remove(); }, 250);
                }, 2200);
            }
        })();
    </script>
@endpush

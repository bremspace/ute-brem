@extends('website.layout')

@section('title', ($product->name ?? 'Produk') . ' — ' . ($appCompanyName ?? 'UTE Parts'))

@php
    $customer = $customer ?? null;
    $canSeePrice = !empty($customer) && (($customer['type'] ?? null) === 'member');

    // --- Gallery images (legacy path first, then gallery rows) ---
    $galleryImages = [];
    if (!empty($product->legacy_image_path)) {
        $galleryImages[] = asset('storage/' . $product->legacy_image_path);
    }
    foreach ($product->images ?? [] as $img) {
        if (!empty($img->image_path)) {
            $galleryImages[] = asset('storage/' . $img->image_path);
        }
    }
    $galleryImages = array_values(array_unique($galleryImages));

    // --- Price / discount ---
    $price = (float) $product->selling_price;
    $discount = (float) ($product->discount_value ?? 0);
    $originalPrice = $discount > 0 ? $price + $discount : null;
    $discountPercent = $originalPrice ? (int) round(($discount / $originalPrice) * 100) : 0;

    // --- Stock ---
    $stockQty = (float) $product->stock_global;
    $inStock = $stockQty > 0;
    $stockUnit = $product->sale_unit ?: ($product->unit ?: 'pcs');
    $locationName = $product->defaultLocation?->name;
    if (! $locationName && ($product->stocks ?? collect())->isNotEmpty()) {
        $locationName = $product->stocks->first()?->location?->name;
    }

    // --- Rating / sold (optional — rendered only when the controller provides them) ---
    $rating = $product->rating ?? null;
    $soldCount = $product->sold_count ?? null;
    $hasRating = $rating !== null;
    $hasSold = $soldCount !== null;

    // --- Specifications (key-value pairs) ---
    $specs = [];
    if ($product->maker) {
        $specs[] = ['Merek', $product->maker->name];
    }
    if ($product->brand) {
        $specs[] = ['Brand HP', $product->brand->name];
    }
    $typeNames = $product->productTypes?->pluck('name')->filter()->values()->all() ?? [];
    if ($typeNames) {
        $specs[] = ['Tipe HP', implode(', ', $typeNames)];
    }
    if (! empty($product->quality)) {
        $specs[] = ['Kualitas', $product->quality];
    }
    if ($product->category) {
        $specs[] = ['Kategori', $product->category->name];
    }
    if ($product->subCategory) {
        $specs[] = ['Sub Kategori', $product->subCategory->name];
    }
    if (! empty($stockUnit)) {
        $specs[] = ['Satuan', $stockUnit];
    }
    if (! empty($product->sku)) {
        $specs[] = ['SKU', $product->sku];
    }
    $barcode = $product->primaryBarcode();
    if ($barcode) {
        $specs[] = ['Barcode', $barcode];
    }
    if (! empty($product->rack_location)) {
        $specs[] = ['Lokasi Rak', $product->rack_location];
    } elseif ($product->defaultRack) {
        $specs[] = ['Lokasi Rak', $product->defaultRack->name];
    }

    // --- Delivery options (controller may override with real rates) ---
    $deliveryOptions = $deliveryOptions ?? [
        ['icon' => 'instant', 'title' => 'Instant (GoSend / GrabExpress)', 'desc' => 'Estimasi ± 30 menit', 'cost' => 'Estimasi di checkout'],
        ['icon' => 'expedition', 'title' => 'Ekspedisi (J&T / JNE / SiCepat)', 'desc' => 'Estimasi 2–3 hari kerja', 'cost' => 'Estimasi di checkout'],
        ['icon' => 'pickup', 'title' => 'Ambil di Toko', 'desc' => 'Tanpa ongkir', 'cost' => 'Gratis'],
    ];

    // --- Related products (controller passes 4 from same category) ---
    $relatedProducts = $relatedProducts ?? collect();
@endphp

@push('styles')
    <style>
        /* ================================================================
           Product Detail Page — mobile-first, tokens from consumer layout
           ================================================================ */

        .pdp {
            max-width: var(--c-max-width);
            margin: 0 auto;
            padding: 0.75rem 1rem 7.5rem;
        }

        /* --- Header (back / title / share) ------------------------------ */
        .pdp-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .pdp-back,
        .pdp-share {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: none;
            background: var(--c-surface-0);
            color: var(--c-surface-700);
            border-radius: var(--c-radius-lg);
            box-shadow: var(--c-shadow-sm);
            cursor: pointer;
            transition: all var(--c-transition);
        }

        .pdp-back:hover,
        .pdp-share:hover {
            color: var(--c-primary);
            box-shadow: var(--c-shadow-md);
        }

        .pdp-back:active,
        .pdp-share:active {
            transform: scale(0.95);
        }

        .pdp-back svg,
        .pdp-share svg {
            width: 20px;
            height: 20px;
        }

        .pdp-title {
            flex: 1;
            min-width: 0;
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--c-surface-800);
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* --- Grid -------------------------------------------------------- */
        .pdp-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            align-items: start;
        }

        /* --- Gallery ----------------------------------------------------- */
        .pdp-gallery {
            position: relative;
            background: var(--c-surface-0);
            border-radius: var(--c-radius-xl);
            box-shadow: var(--c-shadow-sm);
            overflow: hidden;
        }

        .pdp-gallery-track {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            outline: none;
        }

        .pdp-gallery-track::-webkit-scrollbar {
            display: none;
        }

        .pdp-gallery-slide {
            position: relative;
            flex: 0 0 100%;
            scroll-snap-align: center;
            aspect-ratio: 1 / 1;
            background: var(--c-surface-100);
            overflow: hidden;
        }

        .pdp-gallery-slide::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, var(--c-surface-100) 40%, var(--c-surface-200) 50%, var(--c-surface-100) 60%);
            background-size: 200% 100%;
            animation: pdpShimmer 1.4s infinite linear;
        }

        .pdp-gallery-slide.is-loaded::before {
            display: none;
        }

        @keyframes pdpShimmer {
            from { background-position: 200% 0; }
            to   { background-position: -200% 0; }
        }

        .pdp-gallery-slide img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 0.5rem;
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .pdp-gallery-slide.is-loaded img {
            opacity: 1;
        }

        .pdp-gallery-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: var(--c-surface-400);
            font-size: 0.8125rem;
        }

        .pdp-gallery-empty svg {
            width: 48px;
            height: 48px;
        }

        .pdp-gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: var(--c-shadow-md);
            color: var(--c-surface-700);
            cursor: pointer;
            transition: all var(--c-transition);
            z-index: 2;
        }

        .pdp-gallery-nav:hover {
            color: var(--c-primary);
            background: #fff;
        }

        .pdp-gallery-nav svg {
            width: 20px;
            height: 20px;
        }

        .pdp-gallery-nav.prev { left: 0.75rem; }
        .pdp-gallery-nav.next { right: 0.75rem; }

        .pdp-gallery-dots {
            position: absolute;
            bottom: 0.75rem;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 0.375rem;
            padding: 0.25rem 0;
            z-index: 2;
        }

        .pdp-gallery-dot {
            width: 8px;
            height: 8px;
            padding: 0;
            border: none;
            border-radius: 999px;
            background: rgba(17, 24, 39, 0.25);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .pdp-gallery-dot.is-active {
            width: 22px;
            background: var(--c-primary);
        }

        /* --- Info column ------------------------------------------------ */
        .pdp-info {
            display: grid;
            gap: 0.75rem;
            min-width: 0;
        }

        .pdp-card {
            background: var(--c-surface-0);
            border-radius: var(--c-radius-xl);
            box-shadow: var(--c-shadow-sm);
            padding: 1rem;
        }

        .pdp-card-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 0 0.75rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--c-surface-800);
        }

        .pdp-card-title svg {
            width: 18px;
            height: 18px;
            color: var(--c-primary);
            flex-shrink: 0;
        }

        /* --- Product identity ------------------------------------------- */
        .pdp-name {
            margin: 0 0 0.25rem;
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.35;
            color: var(--c-surface-900);
        }

        .pdp-code {
            margin: 0 0 0.5rem;
            font-size: 0.75rem;
            color: var(--c-surface-400);
        }

        .pdp-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            font-size: 0.75rem;
            color: var(--c-surface-500);
        }

        .pdp-stars {
            display: inline-flex;
            gap: 1px;
            color: var(--c-surface-200);
        }

        .pdp-stars svg {
            width: 14px;
            height: 14px;
        }

        .pdp-stars svg.is-filled {
            color: #f59e0b;
        }

        .pdp-rating-value {
            font-weight: 600;
            color: var(--c-surface-700);
        }

        .pdp-trust-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.5rem;
            border-radius: 999px;
            background: var(--c-surface-100);
            color: var(--c-surface-600);
            font-size: 0.6875rem;
            font-weight: 600;
        }

        .pdp-trust-badge svg {
            width: 12px;
            height: 12px;
            color: var(--c-success);
        }

        /* --- Price ------------------------------------------------------- */
        .pdp-price-row {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 0.75rem;
        }

        .pdp-price {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--c-primary);
        }

        .pdp-price-original {
            font-size: 0.875rem;
            color: var(--c-surface-400);
            text-decoration: line-through;
        }

        .pdp-price-discount {
            font-size: 0.6875rem;
            font-weight: 700;
            color: #fff;
            background: var(--c-danger);
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
        }

        .pdp-price-save {
            flex-basis: 100%;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--c-success);
        }

        .pdp-price-login {
            margin-top: 0.75rem;
        }

        /* --- Stock ------------------------------------------------------- */
        .pdp-stock {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--c-surface-700);
        }

        .pdp-stock-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .pdp-stock-dot.in  { background: var(--c-success); box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15); }
        .pdp-stock-dot.out { background: var(--c-danger);  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15); }

        .pdp-stock.out {
            color: var(--c-danger);
        }

        .pdp-location {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            margin-top: 0.375rem;
            font-size: 0.75rem;
            color: var(--c-surface-500);
        }

        .pdp-location svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* --- Quantity ---------------------------------------------------- */
        .pdp-qty-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.875rem;
        }

        .pdp-qty-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--c-surface-500);
        }

        .pdp-qty {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            overflow: hidden;
            background: var(--c-surface-0);
        }

        .pdp-qty button {
            width: 40px;
            height: 40px;
            border: none;
            background: var(--c-surface-50);
            color: var(--c-surface-700);
            font-size: 1.125rem;
            line-height: 1;
            cursor: pointer;
            transition: background var(--c-transition);
        }

        .pdp-qty button:hover {
            background: var(--c-surface-100);
        }

        .pdp-qty button:active {
            background: var(--c-surface-200);
        }

        .pdp-qty input {
            width: 44px;
            height: 40px;
            border: none;
            text-align: center;
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--c-surface-800);
            -moz-appearance: textfield;
            appearance: textfield;
        }

        .pdp-qty input::-webkit-outer-spin-button,
        .pdp-qty input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* --- Description ------------------------------------------------ */
        .pdp-desc {
            margin: 0;
            font-size: 0.8125rem;
            line-height: 1.65;
            color: var(--c-surface-600);
            white-space: pre-line;
        }

        .pdp-desc-empty {
            color: var(--c-surface-400);
            font-style: italic;
        }

        /* --- Specifications --------------------------------------------- */
        .pdp-specs {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .pdp-specs th,
        .pdp-specs td {
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--c-surface-100);
            text-align: left;
            vertical-align: top;
        }

        .pdp-specs th {
            width: 40%;
            color: var(--c-surface-400);
            font-weight: 500;
        }

        .pdp-specs td {
            color: var(--c-surface-700);
        }

        .pdp-specs tr:last-child th,
        .pdp-specs tr:last-child td {
            border-bottom: 0;
        }

        /* --- Delivery ---------------------------------------------------- */
        .pdp-delivery-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 0.75rem;
            margin-bottom: 0.625rem;
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-50);
            font-size: 0.75rem;
            color: var(--c-surface-600);
        }

        .pdp-delivery-location svg {
            width: 16px;
            height: 16px;
            color: var(--c-primary);
            flex-shrink: 0;
        }

        .pdp-delivery {
            display: grid;
            gap: 0.5rem;
        }

        .pdp-delivery-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 0.75rem;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            transition: border-color var(--c-transition);
        }

        .pdp-delivery-item:hover {
            border-color: var(--c-primary);
        }

        .pdp-delivery-icon {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--c-radius-md);
            background: var(--c-surface-100);
            color: var(--c-primary);
        }

        .pdp-delivery-icon svg {
            width: 18px;
            height: 18px;
        }

        .pdp-delivery-info {
            flex: 1;
            min-width: 0;
        }

        .pdp-delivery-title {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--c-surface-800);
        }

        .pdp-delivery-desc {
            display: block;
            font-size: 0.6875rem;
            color: var(--c-surface-400);
        }

        .pdp-delivery-cost {
            flex-shrink: 0;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--c-surface-600);
            white-space: nowrap;
        }

        /* --- Buttons ----------------------------------------------------- */
        .pdp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 44px;
            padding: 0 1.25rem;
            border: none;
            border-radius: var(--c-radius-lg);
            font-family: inherit;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--c-transition);
        }

        .pdp-btn svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .pdp-btn-primary {
            background: var(--c-primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(var(--c-primary-rgb), 0.35);
        }

        .pdp-btn-primary:hover {
            background: var(--c-primary-hover);
        }

        .pdp-btn-primary:active {
            transform: scale(0.98);
        }

        .pdp-btn-outline {
            background: var(--c-surface-0);
            color: var(--c-primary);
            border: 1px solid var(--c-primary);
        }

        .pdp-btn-outline:hover {
            background: rgba(var(--c-primary-rgb), 0.06);
        }

        .pdp-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .pdp-btn.is-loading {
            pointer-events: none;
            opacity: 0.85;
        }

        .pdp-btn-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: pdpSpin 0.6s linear infinite;
        }

        .pdp-btn.is-loading .pdp-btn-spinner {
            display: inline-block;
        }

        @keyframes pdpSpin {
            to { transform: rotate(360deg); }
        }

        /* --- Desktop buy bar (sticky, inside info column) --------------- */
        .pdp-buybar-desktop {
            display: none;
            position: sticky;
            top: calc(var(--c-nav-height) + 0.75rem);
            z-index: 5;
            gap: 0.625rem;
            padding: 0.75rem;
            background: var(--c-surface-0);
            border-radius: var(--c-radius-xl);
            box-shadow: var(--c-shadow-md);
        }

        .pdp-buybar-desktop .pdp-btn {
            flex: 1;
        }

        /* --- Mobile sticky buy bar -------------------------------------- */
        .pdp-buybar-mobile {
            position: fixed;
            left: 0;
            right: 0;
            bottom: calc(var(--c-bottom-nav-height) + env(safe-area-inset-bottom, 0px));
            z-index: 998;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.06);
        }

        .pdp-buy-cart {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-0);
            color: var(--c-surface-700);
            cursor: pointer;
            transition: all var(--c-transition);
        }

        .pdp-buy-cart:hover {
            color: var(--c-primary);
            border-color: var(--c-primary);
        }

        .pdp-buy-cart:active {
            transform: scale(0.95);
        }

        .pdp-buy-cart svg {
            width: 22px;
            height: 22px;
        }

        .pdp-buy-now {
            flex: 1;
        }

        /* --- Related products ------------------------------------------- */
        .pdp-related {
            margin-top: 1.5rem;
        }

        .pdp-related-title {
            margin: 0 0 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            color: var(--c-surface-900);
        }

        .pdp-related-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .pdp-related-card {
            display: block;
            background: var(--c-surface-0);
            border-radius: var(--c-radius-lg);
            box-shadow: var(--c-shadow-sm);
            overflow: hidden;
            transition: transform var(--c-transition), box-shadow var(--c-transition);
        }

        .pdp-related-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--c-shadow-md);
        }

        .pdp-related-thumb {
            position: relative;
            display: block;
            aspect-ratio: 1 / 1;
            background: var(--c-surface-100);
            overflow: hidden;
        }

        .pdp-related-thumb::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, var(--c-surface-100) 40%, var(--c-surface-200) 50%, var(--c-surface-100) 60%);
            background-size: 200% 100%;
            animation: pdpShimmer 1.4s infinite linear;
        }

        .pdp-related-thumb.is-loaded::before {
            display: none;
        }

        .pdp-related-thumb.is-empty::before {
            display: none;
        }

        .pdp-related-thumb img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .pdp-related-thumb.is-loaded img {
            opacity: 1;
        }

        .pdp-related-thumb-empty {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--c-surface-400);
            font-size: 0.6875rem;
        }

        .pdp-related-body {
            display: block;
            padding: 0.625rem;
        }

        .pdp-related-name {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.7em;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.35;
            color: var(--c-surface-800);
        }

        .pdp-related-price {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--c-primary);
        }

        /* --- Scroll reveal ---------------------------------------------- */
        .pdp-reveal {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 0.4s ease, transform 0.4s ease;
        }

        .pdp-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- Desktop ----------------------------------------------------- */
        @media (min-width: 1024px) {
            .pdp {
                padding: 1.5rem 1.5rem 2.5rem;
            }

            .pdp-header {
                margin-bottom: 1.25rem;
            }

            .pdp-title {
                text-align: left;
                font-size: 1.0625rem;
            }

            .pdp-grid {
                grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
                gap: 1.5rem;
            }

            .pdp-gallery {
                position: sticky;
                top: calc(var(--c-nav-height) + 0.75rem);
            }

            .pdp-gallery-nav {
                display: flex;
            }

            .pdp-info {
                gap: 1rem;
            }

            .pdp-buybar-desktop {
                display: flex;
            }

            .pdp-buybar-mobile {
                display: none;
            }

            .pdp-related-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 1rem;
            }

            .pdp-name {
                font-size: 1.25rem;
            }

            .pdp-price {
                font-size: 1.75rem;
            }
        }

        /* --- Reduced motion --------------------------------------------- */
        @media (prefers-reduced-motion: reduce) {
            .pdp-reveal {
                opacity: 1;
                transform: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="pdp">

        {{-- ============================================================
             Header: back / product name / share
             ============================================================ --}}
        <div class="pdp-header">
            <a href="{{ route('website.products.index') }}" class="pdp-back" aria-label="Kembali ke katalog"
                onclick="if (document.referrer && history.length > 1) { event.preventDefault(); history.back(); }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </a>
            <h1 class="pdp-title">{{ $product->name }}</h1>
            <button type="button" class="pdp-share" onclick="window.shareProduct && window.shareProduct()" aria-label="Bagikan produk">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="18" cy="5" r="3"/>
                    <circle cx="6" cy="12" r="3"/>
                    <circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
            </button>
        </div>

        <div class="pdp-grid">

            {{-- ============================================================
                 Image gallery (swipeable)
                 ============================================================ --}}
            <div class="pdp-gallery pdp-reveal">
                @if (count($galleryImages) > 0)
                    <div class="pdp-gallery-track" id="pdpGalleryTrack">
                        @foreach ($galleryImages as $index => $imageUrl)
                            <div class="pdp-gallery-slide">
                                <img src="{{ $imageUrl }}"
                                    alt="{{ $product->name }}{{ count($galleryImages) > 1 ? ' — foto ' . ($index + 1) : '' }}"
                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                    decoding="async"
                                    onload="this.closest('.pdp-gallery-slide').classList.add('is-loaded')"
                                    onerror="this.closest('.pdp-gallery-slide').classList.add('is-loaded')">
                            </div>
                        @endforeach
                    </div>

                    @if (count($galleryImages) > 1)
                        <button type="button" class="pdp-gallery-nav prev" id="pdpGalleryPrev" aria-label="Foto sebelumnya">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                        </button>
                        <button type="button" class="pdp-gallery-nav next" id="pdpGalleryNext" aria-label="Foto berikutnya">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"/>
                            </svg>
                        </button>
                        <div class="pdp-gallery-dots" role="tablist" aria-label="Indikator foto">
                            @foreach ($galleryImages as $index => $imageUrl)
                                <button type="button" class="pdp-gallery-dot {{ $index === 0 ? 'is-active' : '' }}"
                                    data-index="{{ $index }}" aria-label="Ke foto {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="pdp-gallery-slide pdp-gallery-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>No Photo</span>
                    </div>
                @endif
            </div>

            {{-- ============================================================
                 Info column
                 ============================================================ --}}
            <div class="pdp-info">

                {{-- Identity + price + stock + qty --}}
                <div class="pdp-card pdp-reveal">
                    <h2 class="pdp-name">{{ $product->name }}</h2>
                    <p class="pdp-code">Kode: {{ $product->product_code ?: '-' }}</p>

                    <div class="pdp-meta">
                        @if ($hasRating || $hasSold)
                            @if ($hasRating)
                                <span class="pdp-stars" aria-hidden="true">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg viewBox="0 0 24 24" class="{{ $i <= (int) round((float) $rating) ? 'is-filled' : '' }}">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                        </svg>
                                    @endfor
                                </span>
                                <span class="pdp-rating-value">{{ number_format((float) $rating, 1, ',', '.') }}</span>
                            @endif
                            @if ($hasSold)
                                <span>Terjual {{ $soldCount }}+</span>
                            @endif
                        @else
                            <span class="pdp-trust-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                Produk Original
                            </span>
                            <span class="pdp-trust-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                {{ $inStock ? 'Siap Kirim' : 'Stok Habis' }}
                            </span>
                        @endif
                    </div>

                    @if ($canSeePrice)
                        <div class="pdp-price-row">
                            <span class="pdp-price">Rp {{ number_format($price, 0, ',', '.') }}</span>
                            @if ($originalPrice)
                                <span class="pdp-price-original">Rp {{ number_format($originalPrice, 0, ',', '.') }}</span>
                                <span class="pdp-price-discount">-{{ $discountPercent }}%</span>
                            @endif
                            @if ($originalPrice)
                                <span class="pdp-price-save">Hemat Rp {{ number_format($discount, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    @else
                        <div class="pdp-price-login">
                            <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                                class="pdp-btn pdp-btn-primary">
                                Lihat Harga
                            </a>
                        </div>
                    @endif

                    <div class="pdp-stock {{ $inStock ? '' : 'out' }}">
                        <span class="pdp-stock-dot {{ $inStock ? 'in' : 'out' }}"></span>
                        @if ($inStock)
                            Tersedia · {{ number_format($stockQty, 0, ',', '.') }} {{ $stockUnit }}
                        @else
                            Stok Habis
                        @endif
                    </div>

                    @if ($locationName)
                        <div class="pdp-location">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <use href="#c-icon-map-pin"/>
                            </svg>
                            <span>Lokasi: {{ $locationName }}</span>
                        </div>
                    @endif

                    @if ($canSeePrice && $inStock)
                        <div class="pdp-qty-row">
                            <span class="pdp-qty-label" id="pdpQtyLabel">Jumlah</span>
                            <div class="pdp-qty" role="group" aria-labelledby="pdpQtyLabel">
                                <button type="button" id="pdpQtyMinus" aria-label="Kurangi jumlah">−</button>
                                <input type="number" id="pdpQty" value="1" min="1" max="{{ max(1, (int) $stockQty) }}"
                                    inputmode="numeric" aria-label="Jumlah barang">
                                <button type="button" id="pdpQtyPlus" aria-label="Tambah jumlah">+</button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Desktop buy bar (sticky) --}}
                <div class="pdp-buybar-desktop pdp-reveal">
                    @if ($canSeePrice)
                        @if ($inStock)
                            <button type="button" class="pdp-btn pdp-btn-outline js-pdp-buy-cart">
                                <span class="pdp-btn-spinner" aria-hidden="true"></span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <use href="#c-icon-cart"/>
                                </svg>
                                <span class="pdp-btn-label">Tambah</span>
                            </button>
                            <button type="button" class="pdp-btn pdp-btn-primary js-pdp-buy-now">
                                <span class="pdp-btn-spinner" aria-hidden="true"></span>
                                <span class="pdp-btn-label">Beli Sekarang</span>
                            </button>
                        @else
                            <button type="button" class="pdp-btn pdp-btn-primary" disabled>Stok Habis</button>
                        @endif
                    @else
                        <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                            class="pdp-btn pdp-btn-primary">
                            Lihat Harga
                        </a>
                    @endif
                </div>

                {{-- Description --}}
                <div class="pdp-card pdp-reveal">
                    <h3 class="pdp-card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Deskripsi
                    </h3>
                    @if (! empty($product->description))
                        <p class="pdp-desc">{{ $product->description }}</p>
                    @else
                        <p class="pdp-desc pdp-desc-empty">Tidak ada deskripsi untuk produk ini.</p>
                    @endif
                </div>

                {{-- Specifications --}}
                @if (count($specs) > 0)
                    <div class="pdp-card pdp-reveal">
                        <h3 class="pdp-card-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" y1="21" x2="4" y2="14"/>
                                <line x1="4" y1="10" x2="4" y2="3"/>
                                <line x1="12" y1="21" x2="12" y2="12"/>
                                <line x1="12" y1="8" x2="12" y2="3"/>
                                <line x1="20" y1="21" x2="20" y2="16"/>
                                <line x1="20" y1="12" x2="20" y2="3"/>
                                <line x1="1" y1="14" x2="7" y2="14"/>
                                <line x1="9" y1="8" x2="15" y2="8"/>
                                <line x1="17" y1="16" x2="23" y2="16"/>
                            </svg>
                            Spesifikasi
                        </h3>
                        <table class="pdp-specs">
                            <tbody>
                                @foreach ($specs as $spec)
                                    <tr>
                                        <th scope="row">{{ $spec[0] }}</th>
                                        <td>{{ $spec[1] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Delivery options --}}
                <div class="pdp-card pdp-reveal">
                    <h3 class="pdp-card-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="#c-icon-package"/>
                        </svg>
                        Pengiriman
                    </h3>

                    <div class="pdp-delivery-location">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="#c-icon-map-pin"/>
                        </svg>
                        <span>Dikirim dari <strong>{{ $locationName ?: 'Toko kami' }}</strong> · Estimasi ongkir dihitung saat checkout</span>
                    </div>

                    <div class="pdp-delivery">
                        @foreach ($deliveryOptions as $option)
                            <div class="pdp-delivery-item">
                                <span class="pdp-delivery-icon" aria-hidden="true">
                                    @switch($option['icon'] ?? '')
                                        @case('instant')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                                            </svg>
                                            @break
                                        @case('pickup')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 9l1-5h16l1 5"/>
                                                <path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/>
                                                <path d="M5 12v8h14v-8"/>
                                                <path d="M9 20v-5h6v5"/>
                                            </svg>
                                            @break
                                        @default
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <use href="#c-icon-package"/>
                                            </svg>
                                    @endswitch
                                </span>
                                <span class="pdp-delivery-info">
                                    <span class="pdp-delivery-title">{{ $option['title'] }}</span>
                                    <span class="pdp-delivery-desc">{{ $option['desc'] }}</span>
                                </span>
                                <span class="pdp-delivery-cost">{{ $option['cost'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================================
             Related products
             ============================================================ --}}
        @if ($relatedProducts->isNotEmpty())
            <section class="pdp-related pdp-reveal" aria-labelledby="pdpRelatedTitle">
                <h2 class="pdp-related-title" id="pdpRelatedTitle">Produk Terkait</h2>
                <div class="pdp-related-grid">
                    @foreach ($relatedProducts as $related)
                        <a href="{{ url('/products/' . $related->slug) }}" class="pdp-related-card">
                            <span class="pdp-related-thumb {{ $related->primaryImageUrl() ? '' : 'is-empty' }}">
                                @if ($related->primaryImageUrl())
                                    <img src="{{ $related->primaryImageUrl() }}" alt="{{ $related->name }}"
                                        loading="lazy" decoding="async"
                                        onload="this.closest('.pdp-related-thumb').classList.add('is-loaded')"
                                        onerror="this.closest('.pdp-related-thumb').classList.add('is-loaded')">
                                @else
                                    <span class="pdp-related-thumb-empty">No Photo</span>
                                @endif
                            </span>
                            <span class="pdp-related-body">
                                <span class="pdp-related-name">{{ $related->name }}</span>
                                @if ($canSeePrice)
                                    <span class="pdp-related-price">Rp {{ number_format((float) $related->selling_price, 0, ',', '.') }}</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </div>

    {{-- ============================================================
         Mobile sticky buy bar
         ============================================================ --}}
    <div class="pdp-buybar-mobile">
        @if ($canSeePrice)
            @if ($inStock)
                <button type="button" class="pdp-buy-cart js-pdp-buy-cart" aria-label="Tambah ke keranjang">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-cart"/>
                    </svg>
                </button>
                <button type="button" class="pdp-btn pdp-btn-primary pdp-buy-now js-pdp-buy-now">
                    <span class="pdp-btn-spinner" aria-hidden="true"></span>
                    <span class="pdp-btn-label">Beli Sekarang</span>
                </button>
            @else
                <button type="button" class="pdp-buy-cart" disabled aria-label="Stok habis">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-cart"/>
                    </svg>
                </button>
                <button type="button" class="pdp-btn pdp-btn-primary pdp-buy-now" disabled>Stok Habis</button>
            @endif
        @else
            <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}"
                class="pdp-btn pdp-btn-primary pdp-buy-now">
                Lihat Harga
            </a>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            /* ============================================================
               Image gallery — swipe (scroll-snap) + dots + arrows
               ============================================================ */
            var track = document.getElementById('pdpGalleryTrack');
            var dots = Array.prototype.slice.call(document.querySelectorAll('.pdp-gallery-dot'));
            var prevBtn = document.getElementById('pdpGalleryPrev');
            var nextBtn = document.getElementById('pdpGalleryNext');

            function galleryIndex() {
                if (!track) return 0;
                return Math.round(track.scrollLeft / track.clientWidth);
            }

            function updateDots() {
                var idx = galleryIndex();
                dots.forEach(function (dot, i) {
                    dot.classList.toggle('is-active', i === idx);
                });
            }

            function goToSlide(index) {
                if (!track) return;
                var max = track.children.length - 1;
                index = Math.max(0, Math.min(max, index));
                track.scrollTo({ left: index * track.clientWidth, behavior: 'smooth' });
            }

            if (track) {
                track.addEventListener('scroll', function () {
                    window.requestAnimationFrame(updateDots);
                }, { passive: true });

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        goToSlide(parseInt(dot.getAttribute('data-index'), 10) || 0);
                    });
                });

                if (prevBtn) prevBtn.addEventListener('click', function () { goToSlide(galleryIndex() - 1); });
                if (nextBtn) nextBtn.addEventListener('click', function () { goToSlide(galleryIndex() + 1); });

                /* Keyboard navigation */
                track.setAttribute('tabindex', '0');
                track.setAttribute('aria-label', 'Galeri gambar produk');
                track.addEventListener('keydown', function (e) {
                    if (e.key === 'ArrowLeft') { e.preventDefault(); goToSlide(galleryIndex() - 1); }
                    if (e.key === 'ArrowRight') { e.preventDefault(); goToSlide(galleryIndex() + 1); }
                });
            }

            /* ============================================================
               Quantity stepper
               ============================================================ */
            var qtyInput = document.getElementById('pdpQty');
            var qtyMinus = document.getElementById('pdpQtyMinus');
            var qtyPlus = document.getElementById('pdpQtyPlus');

            function setQty(value) {
                if (!qtyInput) return;
                var min = parseInt(qtyInput.getAttribute('min'), 10) || 1;
                var max = parseInt(qtyInput.getAttribute('max'), 10) || 999;
                if (isNaN(value)) value = min;
                value = Math.max(min, Math.min(max, value));
                qtyInput.value = value;
            }

            if (qtyMinus) qtyMinus.addEventListener('click', function () {
                setQty((parseInt(qtyInput.value, 10) || 1) - 1);
            });
            if (qtyPlus) qtyPlus.addEventListener('click', function () {
                setQty((parseInt(qtyInput.value, 10) || 1) + 1);
            });
            if (qtyInput) qtyInput.addEventListener('change', function () {
                setQty(parseInt(qtyInput.value, 10) || 1);
            });

            /* ============================================================
               Toast
               ============================================================ */
            function showToast(message) {
                var toast = document.createElement('div');
                toast.className = 'consumer-toast';
                toast.setAttribute('role', 'status');
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(function () {
                    toast.classList.add('is-hidden');
                    setTimeout(function () { toast.remove(); }, 250);
                }, 2400);
            }

            /* ============================================================
               Add to cart (AJAX POST /cart/add)
               ============================================================ */
            var addToCartUrl = @js(url('/cart/add'));
            var cartUrl = @js(url('/cart'));
            var productId = @js((int) $product->id);
            var inStock = @js($inStock);
            var isAdding = false;

            function setButtonsLoading(loading) {
                var btns = document.querySelectorAll('.js-pdp-buy-now, .js-pdp-buy-cart');
                for (var i = 0; i < btns.length; i++) {
                    if (loading) {
                        btns[i].classList.add('is-loading');
                        btns[i].setAttribute('aria-busy', 'true');
                        btns[i].disabled = true;
                    } else {
                        btns[i].classList.remove('is-loading');
                        btns[i].removeAttribute('aria-busy');
                        btns[i].disabled = false;
                    }
                }
            }

            function addToCart(redirect) {
                if (isAdding) return;
                if (!inStock) {
                    showToast('Stok produk sedang kosong');
                    return;
                }
                var qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
                isAdding = true;
                setButtonsLoading(true);

                fetch(addToCartUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ product_id: productId, quantity: qty })
                })
                    .then(function (res) {
                        return res.json().catch(function () { return {}; }).then(function (data) {
                            return { ok: res.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (!result.ok) {
                            throw new Error(result.data.message || 'Gagal menambahkan ke keranjang');
                        }
                        var count = result.data.cart_count || result.data.count || 0;
                        if (window.UTEParts && typeof window.UTEParts.updateCartCount === 'function') {
                            window.UTEParts.updateCartCount(count);
                        }
                        showToast(result.data.message || 'Ditambahkan ke keranjang');
                        if (redirect) {
                            setTimeout(function () { window.location.href = cartUrl; }, 400);
                        }
                    })
                    .catch(function (err) {
                        showToast(err.message || 'Terjadi kesalahan, coba lagi');
                    })
                    .finally(function () {
                        isAdding = false;
                        setButtonsLoading(false);
                    });
            }

            var buyNowBtns = document.querySelectorAll('.js-pdp-buy-now');
            var buyCartBtns = document.querySelectorAll('.js-pdp-buy-cart');
            for (var i = 0; i < buyNowBtns.length; i++) {
                buyNowBtns[i].addEventListener('click', function () { addToCart(true); });
            }
            for (var j = 0; j < buyCartBtns.length; j++) {
                buyCartBtns[j].addEventListener('click', function () { addToCart(false); });
            }

            /* ============================================================
               Share — native share sheet, clipboard, or WhatsApp
               ============================================================ */
            window.shareProduct = function () {
                var url = window.location.href;
                var text = @js($product->name) + ' — ' + url;
                if (navigator.share) {
                    navigator.share({ title: @js($product->name), text: text, url: url }).catch(function () {});
                } else if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(function () {
                        showToast('Link produk disalin');
                    }).catch(function () {
                        window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
                    });
                } else {
                    window.open('https://wa.me/?text=' + encodeURIComponent(text), '_blank');
                }
            };

            /* ============================================================
               Scroll reveal
               ============================================================ */
            var revealEls = document.querySelectorAll('.pdp-reveal');
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            io.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -24px 0px' });
                revealEls.forEach(function (el) { io.observe(el); });
            } else {
                revealEls.forEach(function (el) { el.classList.add('is-visible'); });
            }
        })();
    </script>
@endpush
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', ($appCompanyName ?? 'UTE Parts'))</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('brand-u.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/vendor/fonts/iconify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('sneat/assets/css/demo.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/consumer-animations.css') }}">

    <script>document.documentElement.classList.add('js');</script>

    <style>
        /* ================================================================
           Consumer Layout — Mobile-first responsive e-commerce shell
           Primary: #5c73f8 (blue-indigo) · Font: Inter · Tokens: --c-*
           ================================================================ */

        /* --- Design Tokens ----------------------------------------------- */
        :root {
            --c-primary: #5c73f8;
            --c-primary-hover: #4a5fd4;
            --c-primary-rgb: 92, 115, 248;
            --c-surface-0: #ffffff;
            --c-surface-50: #f8f9fc;
            --c-surface-100: #f1f3f8;
            --c-surface-200: #e5e8f0;
            --c-surface-300: #d2d6e2;
            --c-surface-400: #9ba3b5;
            --c-surface-500: #6b7385;
            --c-surface-600: #4b5563;
            --c-surface-700: #374151;
            --c-surface-800: #1f2937;
            --c-surface-900: #111827;
            --c-danger: #ef4444;
            --c-success: #22c55e;
            --c-warning: #f59e0b;
            --c-radius-sm: 0.375rem;
            --c-radius-md: 0.5rem;
            --c-radius-lg: 0.75rem;
            --c-radius-xl: 1rem;
            --c-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --c-shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --c-shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.12);
            --c-transition: 0.2s ease;
            --c-nav-height: 3.5rem;
            --c-bottom-nav-height: 4rem;
            --c-max-width: 1280px;
        }

        /* --- Base ------------------------------------------------------- */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        .consumer-layout {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: var(--c-surface-800);
            background: var(--c-surface-50);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
            min-height: 100dvh;
        }

        .consumer-layout a {
            text-decoration: none;
            color: inherit;
        }

        .consumer-layout img {
            max-width: 100%;
            height: auto;
        }

        /* --- Top Navigation ---------------------------------------------- */
        .consumer-nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .consumer-nav-inner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            max-width: var(--c-max-width);
            margin: 0 auto;
            padding: 0 1rem;
            height: var(--c-nav-height);
        }

        .consumer-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--c-primary);
            white-space: nowrap;
            flex-shrink: 0;
            transition: opacity var(--c-transition);
        }

        .consumer-logo:hover {
            opacity: 0.8;
        }

        .consumer-logo svg {
            width: 28px;
            height: 28px;
        }

        /* --- Desktop Search Bar ------------------------------------------ */
        .consumer-search {
            flex: 1;
            max-width: 480px;
            margin: 0 auto;
        }

        /* Sticky search: lives inside the sticky .consumer-nav, so it stays
           pinned while scrolling on desktop. */
        .search-sticky {
            position: sticky;
            top: 0;
        }

        .consumer-search-form {
            position: relative;
            width: 100%;
        }

        .consumer-search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--c-surface-400);
            pointer-events: none;
            transition: color var(--c-transition);
        }

        .consumer-search-input {
            width: 100%;
            height: 2.25rem;
            padding: 0 0.75rem 0 2.5rem;
            border: 1px solid var(--c-surface-200);
            border-radius: 999px;
            background: var(--c-surface-50);
            font-family: inherit;
            font-size: 0.8125rem;
            color: var(--c-surface-800);
            transition: all var(--c-transition);
            outline: none;
        }

        .consumer-search-input::placeholder {
            color: var(--c-surface-400);
        }

        .consumer-search-input:focus {
            background: var(--c-surface-0);
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.12);
        }

        .consumer-search-input:focus + .consumer-search-icon {
            color: var(--c-primary);
        }

        /* --- Nav Actions (Cart, Account) --------------------------------- */
        .consumer-nav-actions {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            flex-shrink: 0;
        }

        .consumer-nav-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: none;
            background: transparent;
            color: var(--c-surface-600);
            border-radius: var(--c-radius-md);
            cursor: pointer;
            transition: all var(--c-transition);
        }

        .consumer-nav-btn:hover {
            background: var(--c-surface-100);
            color: var(--c-primary);
        }

        .consumer-nav-btn:active {
            transform: scale(0.95);
        }

        .consumer-nav-btn svg {
            width: 22px;
            height: 22px;
        }

        /* --- Cart Badge ------------------------------------------------- */
        .cart-badge {
            position: absolute;
            top: 4px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: var(--c-danger);
            color: #fff;
            font-size: 0.625rem;
            font-weight: 700;
            line-height: 18px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .cart-badge.is-bouncing {
            animation: cartBounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes cartBounce {
            0%   { transform: scale(1); }
            30%  { transform: scale(1.4); }
            60%  { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        /* --- Desktop Account Menu ---------------------------------------- */
        .consumer-account-menu {
            position: relative;
        }

        .consumer-account-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            border: 1px solid var(--c-surface-200);
            border-radius: 999px;
            background: var(--c-surface-0);
            color: var(--c-surface-700);
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all var(--c-transition);
            white-space: nowrap;
        }

        .consumer-account-btn:hover {
            border-color: var(--c-primary);
            color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(var(--c-primary-rgb), 0.08);
        }

        .consumer-account-btn svg {
            width: 18px;
            height: 18px;
        }

        .consumer-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            min-width: 200px;
            background: var(--c-surface-0);
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-lg);
            box-shadow: var(--c-shadow-lg);
            padding: 0.375rem;
            z-index: 1001;
            animation: dropdownIn 0.15s ease-out;
        }

        .consumer-dropdown.is-open {
            display: block;
        }

        @keyframes dropdownIn {
            from {
                opacity: 0;
                transform: translateY(-4px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .consumer-dropdown a,
        .consumer-dropdown button {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            color: var(--c-surface-700);
            font-family: inherit;
            font-size: 0.8125rem;
            border-radius: var(--c-radius-sm);
            cursor: pointer;
            text-align: left;
            transition: background var(--c-transition);
        }

        .consumer-dropdown a:hover,
        .consumer-dropdown button:hover {
            background: var(--c-surface-100);
        }

        .consumer-dropdown a svg,
        .consumer-dropdown button svg {
            width: 16px;
            height: 16px;
            color: var(--c-surface-400);
            flex-shrink: 0;
        }

        .consumer-dropdown-divider {
            height: 1px;
            background: var(--c-surface-200);
            margin: 0.25rem 0;
        }

        /* --- Content Area ----------------------------------------------- */
        .consumer-content {
            min-height: calc(100vh - var(--c-nav-height));
            min-height: calc(100dvh - var(--c-nav-height));
            animation: contentFadeIn 0.3s ease-out;
        }

        @keyframes contentFadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- Footer ----------------------------------------------------- */
        .consumer-footer {
            background: var(--c-surface-900);
            color: var(--c-surface-300);
            padding: 3rem 1rem 1.5rem;
            margin-top: 3rem;
        }

        .consumer-footer-inner {
            max-width: var(--c-max-width);
            margin: 0 auto;
        }

        .consumer-footer-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .consumer-footer-brand {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--c-surface-0);
            margin-bottom: 0.75rem;
        }

        .consumer-footer-desc {
            font-size: 0.8125rem;
            line-height: 1.6;
            color: var(--c-surface-400);
        }

        .consumer-footer-heading {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--c-surface-400);
            margin-bottom: 0.75rem;
        }

        .consumer-footer-links {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .consumer-footer-links li {
            margin-bottom: 0.5rem;
        }

        .consumer-footer-links a {
            font-size: 0.8125rem;
            color: var(--c-surface-300);
            transition: color var(--c-transition);
        }

        .consumer-footer-links a:hover {
            color: var(--c-surface-0);
        }

        .consumer-footer-contact {
            font-size: 0.8125rem;
            line-height: 1.7;
            color: var(--c-surface-400);
        }

        .consumer-footer-contact span {
            display: block;
        }

        .consumer-footer-social {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .consumer-footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--c-radius-md);
            background: rgba(255, 255, 255, 0.06);
            color: var(--c-surface-400);
            transition: all var(--c-transition);
        }

        .consumer-footer-social a:hover {
            background: rgba(255, 255, 255, 0.12);
            color: var(--c-surface-0);
        }

        .consumer-footer-social a svg {
            width: 18px;
            height: 18px;
        }

        .consumer-footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: center;
            text-align: center;
        }

        .consumer-footer-copyright {
            font-size: 0.75rem;
            color: var(--c-surface-500);
        }

        /* --- Mobile Bottom Navigation ------------------------------------ */
        .consumer-bottom-nav {
            display: flex;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 999;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px) saturate(180%);
            -webkit-backdrop-filter: blur(16px) saturate(180%);
            border-top: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 -1px 8px rgba(0, 0, 0, 0.04);
            padding-bottom: env(safe-area-inset-bottom, 0);
            height: var(--c-bottom-nav-height);
            padding-bottom: max(env(safe-area-inset-bottom, 0px), 0px);
        }

        .consumer-bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            padding: 0.5rem 0;
            color: var(--c-surface-500);
            font-size: 0.625rem;
            font-weight: 500;
            letter-spacing: 0.01em;
            text-decoration: none;
            transition: color var(--c-transition);
            min-height: 44px;
            position: relative;
            -webkit-tap-highlight-color: transparent;
        }

        .consumer-bottom-nav a svg {
            width: 22px;
            height: 22px;
            transition: transform var(--c-transition);
        }

        .consumer-bottom-nav a.is-active {
            color: var(--c-primary);
            font-weight: 600;
        }

        .consumer-bottom-nav a.is-active svg {
            transform: scale(1.08);
        }

        .consumer-bottom-nav a:not(.is-active):active {
            color: var(--c-surface-700);
        }

        .consumer-bottom-nav .nav-badge {
            position: absolute;
            top: 4px;
            right: calc(50% - 18px);
            min-width: 16px;
            height: 16px;
            padding: 0 4px;
            border-radius: 999px;
            background: var(--c-danger);
            color: #fff;
            font-size: 0.5625rem;
            font-weight: 700;
            line-height: 16px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        }

        /* --- Responsive ------------------------------------------------- */
        @media (min-width: 1024px) {
            .consumer-bottom-nav {
                display: none;
            }

            .consumer-content {
                padding-bottom: 0;
            }

            .consumer-footer-grid {
                grid-template-columns: 2fr 1fr 1fr 1.2fr;
            }

            .consumer-footer-bottom {
                flex-direction: row;
                justify-content: space-between;
            }
        }

        @media (max-width: 1023.98px) {
            .consumer-search {
                display: none;
            }

            .consumer-account-btn span {
                display: none;
            }

            .consumer-account-btn {
                padding: 0.375rem;
                width: 44px;
                height: 44px;
                justify-content: center;
            }

            .consumer-account-menu .consumer-dropdown {
                right: 0;
                left: auto;
                min-width: 180px;
            }

            .consumer-content {
                padding-bottom: calc(var(--c-bottom-nav-height) + env(safe-area-inset-bottom, 0px));
            }

            .consumer-footer {
                padding-bottom: calc(1.5rem + var(--c-bottom-nav-height) + env(safe-area-inset-bottom, 0px));
            }
        }

        @media (min-width: 640px) and (max-width: 1023.98px) {
            .consumer-footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* --- Mobile Search (Expandable Overlay) -------------------------- */
        .consumer-mobile-search-toggle {
            display: flex;
        }

        .consumer-mobile-search-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1100;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            animation: overlayIn 0.15s ease-out;
        }

        .consumer-mobile-search-overlay.is-open {
            display: block;
        }

        @keyframes overlayIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .consumer-mobile-search-panel {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: var(--c-surface-0);
            padding: 0.75rem 1rem;
            padding-top: max(0.75rem, env(safe-area-inset-top));
            box-shadow: var(--c-shadow-md);
            animation: searchSlideDown 0.2s ease-out;
        }

        @keyframes searchSlideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .consumer-mobile-search-panel .consumer-search-form {
            max-width: 100%;
        }

        .consumer-mobile-search-panel .consumer-search-input {
            height: 2.5rem;
            font-size: 1rem;
        }

        @media (min-width: 1024px) {
            .consumer-mobile-search-toggle {
                display: none;
            }
        }

        /* --- Utility: Toast Notification --------------------------------- */
        .consumer-toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 2000;
            padding: 0.75rem 1rem;
            border-radius: var(--c-radius-lg);
            background: var(--c-surface-800);
            color: var(--c-surface-0);
            font-size: 0.8125rem;
            font-weight: 500;
            box-shadow: var(--c-shadow-lg);
            animation: toastIn 0.3s ease-out;
            pointer-events: none;
        }

        .consumer-toast.is-hidden {
            animation: toastOut 0.2s ease-in forwards;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(-0.5rem) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toastOut {
            to {
                opacity: 0;
                transform: translateY(-0.5rem) scale(0.95);
            }
        }

        /* --- Re-apply project overrides ---------------------------------- */
        body {
            background: var(--c-surface-50);
            min-height: 100vh;
        }

        .product-card {
            border: 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .product-thumb {
            width: 100%;
            height: 132px;
            object-fit: cover;
            background: var(--c-surface-100);
        }

        .filter-card {
            border: 1px solid var(--c-surface-200);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        /* --- Select2 overrides ------------------------------------------- */
        .select2-container {
            width: 100% !important;
        }

        .select2-container .select2-selection--single {
            min-height: calc(2rem + 2px);
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-md);
            padding: 0.2rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 1.5rem;
            padding-left: 0;
            font-size: 13px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(2rem + 2px);
            right: 0.5rem;
        }

        .select2-container .select2-selection--multiple {
            min-height: calc(2rem + 2px);
            border: 1px solid var(--c-surface-200);
            border-radius: var(--c-radius-md);
            padding: 0.2rem 0.45rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: var(--c-surface-100);
            border: 0;
            border-radius: 999px;
            padding: 0.15rem 1.25rem 0.15rem 0.5rem;
            margin-top: 0.15rem;
            position: relative;
            font-size: 12px;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            right: 0.38rem;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            margin: 0;
            padding: 0;
            color: var(--c-surface-500);
            background: transparent;
        }

        .select2-dropdown {
            border-color: var(--c-surface-200);
        }

        /* --- Reduced Motion --------------------------------------------- */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>

@php
    // Load website settings from DB (with fallbacks)
    $wsGeneral = \App\Models\WebsiteSetting::getGroup('general');
    $wsFooter  = \App\Models\WebsiteSetting::getGroup('footer');
    $wsStoreName = $wsGeneral['store_name'] ?? ($appCompanyName ?? 'UTE Parts');
    $wsTagline = $wsGeneral['tagline'] ?? '';
@endphp

<body>
    <div class="consumer-layout">

        @php
            // Cart route may not be registered yet (Phase 1 of e-commerce plan).
            // Fall back to a plain URL so the layout never crashes.
            $cartUrl = \Illuminate\Support\Facades\Route::has('cart.index')
                ? route('website.cart')
                : url('/cart');
        @endphp

        {{-- ============================================================
             SVG Icon Sprite (hidden, referenced via <use>)
             ============================================================ --}}
        <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
            <symbol id="c-icon-home" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </symbol>
            <symbol id="c-icon-grid" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
            </symbol>
            <symbol id="c-icon-cart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"/>
                <circle cx="20" cy="21" r="1"/>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
            </symbol>
            <symbol id="c-icon-user" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </symbol>
            <symbol id="c-icon-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </symbol>
            <symbol id="c-icon-package" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/>
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
            </symbol>
            <symbol id="c-icon-phone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </symbol>
            <symbol id="c-icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </symbol>
            <symbol id="c-icon-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </symbol>
            <symbol id="c-icon-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
            </symbol>
            <symbol id="c-icon-key" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
            </symbol>
            <symbol id="c-icon-logout" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </symbol>
        </svg>

        {{-- ============================================================
             Top Navigation
             ============================================================ --}}
        <nav class="consumer-nav" role="navigation" aria-label="Navigasi utama">
            <div class="consumer-nav-inner">

                {{-- Logo --}}
                <a href="{{ route('website.products.index') }}" class="consumer-logo" aria-label="{{ $wsStoreName }} — Beranda">
                    <svg viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="28" height="28" rx="6" fill="currentColor"/>
                        <text x="14" y="19" text-anchor="middle" fill="#fff" font-family="Inter, system-ui, sans-serif" font-size="15" font-weight="700">U</text>
                    </svg>
                    <span>{{ $wsStoreName }}</span>
                </a>

                {{-- Desktop Search --}}
                <div class="consumer-search search-sticky">
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

                {{-- Nav Actions --}}
                <div class="consumer-nav-actions">

                    {{-- Mobile Search Toggle --}}
                    <button type="button" class="consumer-nav-btn consumer-mobile-search-toggle"
                        onclick="document.querySelector('.consumer-mobile-search-overlay').classList.add('is-open'); setTimeout(function(){ document.querySelector('.consumer-mobile-search-overlay input').focus(); }, 100);"
                        aria-label="Cari produk">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="#c-icon-search"/>
                        </svg>
                    </button>

                    {{-- Cart --}}
                    @php $cartCount = $cartCount ?? 0; @endphp
                    <a href="{{ $cartUrl }}" class="consumer-nav-btn" aria-label="Keranjang belanja{{ $cartCount > 0 ? ' (' . $cartCount . ' item)' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <use href="#c-icon-cart"/>
                        </svg>
                        @if ($cartCount > 0)
                            <span class="cart-badge" id="cartBadge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                        @endif
                    </a>

                    {{-- Account (Desktop) --}}
                    @if (!empty($customer))
                        <div class="consumer-account-menu">
                            <button type="button" class="consumer-account-btn" onclick="this.nextElementSibling.classList.toggle('is-open')" aria-haspopup="true" aria-expanded="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <use href="#c-icon-user"/>
                                </svg>
                                <span>{{ Str::limit($customer['name'], 16) }}</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px">
                                    <use href="#c-icon-chevron"/>
                                </svg>
                            </button>
                            <div class="consumer-dropdown">
                                <a href="{{ route('website.member.orders') }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-package"/></svg>
                                    Pesanan Saya
                                </a>
                                <a href="{{ route('website.member.points') }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-clock"/></svg>
                                    Poin Saya
                                </a>
                                <div class="consumer-dropdown-divider"></div>
                                <a href="{{ route('website.member.password.edit', ['return' => request()->fullUrl()]) }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-key"/></svg>
                                    Ganti Password
                                </a>
                                <div class="consumer-dropdown-divider"></div>
                                <form action="{{ route('website.member.logout') }}" method="POST" style="margin:0">
                                    @csrf
                                    <button type="submit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-logout"/></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}" class="consumer-account-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <use href="#c-icon-user"/>
                            </svg>
                            <span>Masuk</span>
                        </a>
                    @endif
                </div>
            </div>
        </nav>

        {{-- ============================================================
             Mobile Search Overlay
             ============================================================ --}}
        <div class="consumer-mobile-search-overlay" onclick="if(event.target===this) this.classList.remove('is-open')">
            <div class="consumer-mobile-search-panel">
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
        </div>

        {{-- ============================================================
             Main Content
             ============================================================ --}}
        <main class="consumer-content" id="mainContent">
            @yield('content')
        </main>

        {{-- ============================================================
             Footer
             ============================================================ --}}
        <footer class="consumer-footer">
            <div class="consumer-footer-inner">
                <div class="consumer-footer-grid">

                    {{-- Brand --}}
                    <div>
                        <div class="consumer-footer-brand">{{ $wsStoreName }}</div>
                        <p class="consumer-footer-desc">
                            {{ $wsFooter['description'] ?? 'Toko sparepart HP terlengkap. Menyediakan LCD, baterai, charger, casing, dan aksesoris HP berkualitas dengan harga terbaik.' }}
                        </p>
                        <div class="consumer-footer-social">
                            @if (!empty($wsFooter['instagram']))
                                <a href="{{ $wsFooter['instagram'] }}" target="_blank" rel="noopener" aria-label="Instagram">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                                    </svg>
                                </a>
                            @endif
                            @if (!empty($wsFooter['whatsapp']))
                                <a href="{{ $wsFooter['whatsapp'] }}" target="_blank" rel="noopener" aria-label="WhatsApp">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                    </svg>
                                </a>
                            @endif
                            @if (!empty($wsFooter['facebook']))
                                <a href="{{ $wsFooter['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>
                    </div>

                    {{-- Quick Links --}}
                    <div>
                        <div class="consumer-footer-heading">Menu</div>
                        <ul class="consumer-footer-links">
                            <li><a href="{{ route('website.products.index') }}">Semua Produk</a></li>
                            <li><a href="{{ $cartUrl }}">Keranjang</a></li>
                            <li><a href="{{ route('website.member.login') }}">Masuk Member</a></li>
                            <li><a href="{{ url('/login') }}">Admin Panel</a></li>
                        </ul>
                    </div>

                    {{-- Help Links --}}
                    <div>
                        <div class="consumer-footer-heading">Bantuan</div>
                        <ul class="consumer-footer-links">
                            <li><a href="#">Cara Pemesanan</a></li>
                            <li><a href="#">Kebijakan Pengembalian</a></li>
                            <li><a href="#">FAQ</a></li>
                        </ul>
                    </div>

                    {{-- Contact --}}
                    <div>
                        <div class="consumer-footer-heading">Hubungi Kami</div>
                        <div class="consumer-footer-contact">
                            @if (!empty($wsFooter['address']))
                                <span>
                                    <svg style="width:14px;height:14px;vertical-align:-2px;margin-right:4px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-map-pin"/></svg>
                                    {{ $wsFooter['address'] }}
                                </span>
                            @endif
                            @if (!empty($wsFooter['phone']))
                                <span>
                                    <svg style="width:14px;height:14px;vertical-align:-2px;margin-right:4px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-phone"/></svg>
                                    {{ $wsFooter['phone'] }}
                                </span>
                            @endif
                            @if (!empty($wsFooter['hours']))
                                <span>
                                    <svg style="width:14px;height:14px;vertical-align:-2px;margin-right:4px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-clock"/></svg>
                                    {{ $wsFooter['hours'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Bottom --}}
                <div class="consumer-footer-bottom">
                    <span class="consumer-footer-copyright">&copy; {{ date('Y') }} {{ $wsStoreName }}. Hak cipta dilindungi.</span>
                </div>
            </div>
        </footer>

        {{-- ============================================================
             Mobile Bottom Navigation
             ============================================================ --}}
        <nav class="consumer-bottom-nav" aria-label="Navigasi mobile">
            <a href="{{ route('website.products.index') }}" class="{{ request()->routeIs('website.products.index') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#c-icon-home"/>
                </svg>
                Beranda
            </a>
            <a href="{{ route('website.products.index') }}" class="">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#c-icon-grid"/>
                </svg>
                Kategori
            </a>
            <a href="{{ $cartUrl }}" class="">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <use href="#c-icon-cart"/>
                </svg>
                Keranjang
                @if ($cartCount > 0)
                    <span class="nav-badge">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                @endif
            </a>
            @if (!empty($customer))
                <a href="{{ route('website.member.orders') }}" class="{{ request()->routeIs('website.member.orders') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-package"/>
                    </svg>
                    Pesanan
                </a>
                <a href="{{ route('website.member.points') }}" class="{{ request()->routeIs('website.member.points') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-clock"/>
                    </svg>
                    Poin
                </a>
            @else
                <a href="{{ route('website.member.login', ['return' => request()->fullUrl()]) }}" class="">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <use href="#c-icon-user"/>
                    </svg>
                    Masuk
                </a>
            @endif
        </nav>

    </div><!-- /.consumer-layout -->

    <script src="{{ asset('sneat/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        (function () {
            /* --- Close dropdowns on outside click ------------------------- */
            document.addEventListener('click', function (e) {
                var menus = document.querySelectorAll('.consumer-dropdown.is-open');
                for (var i = 0; i < menus.length; i++) {
                    if (!menus[i].parentElement.contains(e.target)) {
                        menus[i].classList.remove('is-open');
                    }
                }
            });

            /* --- Escape key closes mobile search overlay ----------------- */
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    var overlay = document.querySelector('.consumer-mobile-search-overlay.is-open');
                    if (overlay) overlay.classList.remove('is-open');
                    var dd = document.querySelector('.consumer-dropdown.is-open');
                    if (dd) dd.classList.remove('is-open');
                }
            });

            /* --- Cart badge bounce on count update ----------------------- */
            function bounceCartBadge() {
                var badge = document.getElementById('cartBadge');
                if (!badge) return;
                badge.classList.remove('is-bouncing');
                void badge.offsetWidth; /* force reflow */
                badge.classList.add('is-bouncing');
            }

            /* Expose for child pages to call after AJAX cart updates */
            window.UTEParts = window.UTEParts || {};
            window.UTEParts.bounceCart = bounceCartBadge;
            window.UTEParts.updateCartCount = function (count) {
                var badges = document.querySelectorAll('.cart-badge, .nav-badge');
                for (var i = 0; i < badges.length; i++) {
                    badges[i].textContent = count > 99 ? '99+' : count;
                }
                if (count > 0) bounceCartBadge();
            };

            /* --- Reduced motion detection --------------------------- */
            window.UTEParts.prefersReducedMotion = function () {
                return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            };

            /* --- Fly-to-cart helper ---------------------------------
               Spawns a fixed-position clone of the product thumbnail
               that arcs from the source element to the cart icon.
               No-op under prefers-reduced-motion. */
            window.UTEParts.flyToCart = function (fromEl, imageUrl) {
                if (window.UTEParts.prefersReducedMotion()) return;
                var target = document.querySelector('.consumer-nav-actions a[href*="cart"]') ||
                             document.querySelector('.consumer-bottom-nav a[href*="cart"]');
                if (!target || !fromEl) return;

                var fromRect = fromEl.getBoundingClientRect();
                var toRect = target.getBoundingClientRect();
                var size = 44;
                var dx = toRect.left + toRect.width / 2 - fromRect.left - fromRect.width / 2;
                var dy = toRect.top + toRect.height / 2 - fromRect.top - fromRect.height / 2;

                var flyer = document.createElement('div');
                flyer.className = 'fly-to-cart';
                if (imageUrl) flyer.style.backgroundImage = 'url("' + imageUrl + '")';
                flyer.style.left = (fromRect.left + fromRect.width / 2 - size / 2) + 'px';
                flyer.style.top = (fromRect.top + fromRect.height / 2 - size / 2) + 'px';
                flyer.style.setProperty('--fly-dx', dx + 'px');
                flyer.style.setProperty('--fly-dy', dy + 'px');
                flyer.style.setProperty('--fly-mx', (dx * 0.55) + 'px');
                flyer.style.setProperty('--fly-my', (dy * 0.55) + 'px');

                document.body.appendChild(flyer);
                flyer.addEventListener('animationend', function () { flyer.remove(); });
            };

            /* --- Scroll reveal via IntersectionObserver -------------
               Elements with .scroll-reveal fade/slide in as they enter
               the viewport. Falls back to instant reveal when the API
               is missing or the user prefers reduced motion. */
            (function () {
                var revealEls = document.querySelectorAll('.scroll-reveal');
                if (revealEls.length === 0) return;

                if ('IntersectionObserver' in window && !window.UTEParts.prefersReducedMotion()) {
                    var io = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                entry.target.classList.add('is-visible');
                                io.unobserve(entry.target);
                            }
                        });
                    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
                    revealEls.forEach(function (el) { io.observe(el); });
                } else {
                    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
                }
            })();

            /* --- Mobile bottom nav active state detection ---------------- */
            var currentPath = window.location.pathname;
            var bottomLinks = document.querySelectorAll('.consumer-bottom-nav a');
            for (var j = 0; j < bottomLinks.length; j++) {
                var href = bottomLinks[j].getAttribute('href');
                if (href) {
                    try {
                        var linkPath = new URL(href, window.location.origin).pathname;
                        if (linkPath === currentPath && currentPath !== '/') {
                            bottomLinks[j].classList.add('is-active');
                        }
                    } catch (err) { /* ignore malformed URLs */ }
                }
            }
        })();
    </script>
    @livewireScripts
    @stack('scripts')
</body>

</html>

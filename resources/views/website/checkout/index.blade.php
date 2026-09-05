@extends('website.layout')

@section('title', 'Checkout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    // Data contract (from CheckoutController@index):
    //   $cartItems        — Collection of cart items (same shape as cart page)
    //   $cartSummary      — ['subtotal', 'discount', 'total', 'item_count']
    //   $deliveryOptions  — Collection: code, name, desc, eta, cost, icon
    //   $paymentMethods   — Collection: code, name, desc, icon
    //   $pickupLocations  — Collection: id, name, address
    //   $customer         — array (name, phone, address, points_balance)
    //
    // Fallbacks below let the page render before the controller ships;
    // the controller always overrides them with real data.
    $cartItems   = $cartItems ?? collect();
    $cartSummary = $cartSummary ?? [];
    $subtotal    = (float) ($cartSummary['subtotal'] ?? 0);
    $discount    = (float) ($cartSummary['discount'] ?? 0);
    $total       = (float) ($cartSummary['total'] ?? 0);
    $itemCount   = (int) ($cartSummary['item_count'] ?? $cartItems->count());

    $deliveryOptions = $deliveryOptions ?? collect([
        (object) ['code' => 'instant',   'name' => 'Instant (GoSend / Grab)', 'desc' => 'Diantar kurir dalam hitungan menit', 'eta' => '± 30 menit', 'cost' => 15000, 'icon' => 'zap'],
        (object) ['code' => 'expedition','name' => 'Ekspedisi (J&T / JNE)',   'desc' => 'Dikirim via kurir ekspedisi',        'eta' => '2–3 hari',  'cost' => 12000, 'icon' => 'truck'],
        (object) ['code' => 'pickup',    'name' => 'Ambil di Toko',           'desc' => 'Ambil langsung di lokasi toko',      'eta' => 'Gratis',    'cost' => 0,     'icon' => 'store'],
    ]);

    $paymentMethods = $paymentMethods ?? collect([
        (object) ['code' => 'va',     'name' => 'Transfer Bank (Virtual Account)', 'desc' => 'BCA, BRI, Mandiri, Permata', 'icon' => 'landmark'],
        (object) ['code' => 'ewallet', 'name' => 'E-Wallet',                       'desc' => 'GoPay, OVO, DANA, ShopeePay', 'icon' => 'wallet'],
        (object) ['code' => 'qris',    'name' => 'QRIS',                           'desc' => 'Scan QR dari aplikasi apa pun', 'icon' => 'qr'],
        (object) ['code' => 'card',    'name' => 'Kartu Kredit',                   'desc' => 'Visa, Mastercard, JCB',         'icon' => 'credit-card'],
    ]);

    $pickupLocations = $pickupLocations ?? collect();
    $customer        = $customer ?? [];

    $storeUrl = Route::has('website.checkout.store') ? route('website.checkout.store') : url('/checkout');
@endphp

@section('content')
    {{-- Local icon sprite (extends the layout sprite with shop-specific icons) --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="c-icon-zap" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
        </symbol>
        <symbol id="c-icon-store" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l1-5h16l1 5"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 13v8h14v-8"/><path d="M9 21v-6h6v6"/>
        </symbol>
        <symbol id="c-icon-truck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
        </symbol>
        <symbol id="c-icon-wallet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/>
        </symbol>
        <symbol id="c-icon-credit-card" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </symbol>
        <symbol id="c-icon-qr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z"/><path d="M20 14h1v1h-1z"/><path d="M14 20h1v1h-1z"/><path d="M18 18h3v3h-3z"/>
        </symbol>
        <symbol id="c-icon-landmark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/>
        </symbol>
        <symbol id="c-icon-arrow-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
        </symbol>
        <symbol id="c-icon-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </symbol>
        <symbol id="c-icon-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </symbol>
        <symbol id="c-icon-map-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
        </symbol>
        <symbol id="c-icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </symbol>
    </svg>

    <div class="shop-container">
        <header class="shop-page-header anim-fade-in">
            <h1 class="shop-page-title">Checkout</h1>
            <p class="shop-page-sub">Selesaikan pesananmu dalam 4 langkah mudah</p>
        </header>

        @if ($cartItems->isEmpty())
            <div class="shop-card shop-empty anim-scale-in">
                <svg class="shop-empty-icon" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="100" cy="100" r="88" fill="#eef2ff"/>
                    <circle cx="100" cy="100" r="88" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="3 5"/>
                    <path d="M58 74h84l-9 46H67z" fill="#ffffff" stroke="#5c73f8" stroke-width="3" stroke-linejoin="round"/>
                    <path d="M67 96h66" stroke="#5c73f8" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="80" cy="128" r="7" fill="#5c73f8"/>
                    <circle cx="120" cy="128" r="7" fill="#5c73f8"/>
                    <path d="M58 74l-8-14H38" stroke="#5c73f8" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <h2 class="shop-empty-title">Keranjangmu kosong</h2>
                <p class="shop-empty-desc">Tambahkan produk dulu sebelum melanjutkan ke checkout.</p>
                <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-primary">Mulai Belanja</a>
            </div>
        @else
            {{-- Progress indicator --}}
            <div class="checkout-steps anim-fade-in" role="tablist" aria-label="Langkah checkout">
                <div class="checkout-step active" data-step="1" role="tab" aria-selected="true" tabindex="0">
                    <span class="checkout-step-dot">1</span>
                    <span class="checkout-step-label">Pengiriman</span>
                </div>
                <div class="checkout-step-line"></div>
                <div class="checkout-step" data-step="2" role="tab" aria-selected="false" tabindex="0">
                    <span class="checkout-step-dot">2</span>
                    <span class="checkout-step-label">Detail</span>
                </div>
                <div class="checkout-step-line"></div>
                <div class="checkout-step" data-step="3" role="tab" aria-selected="false" tabindex="0">
                    <span class="checkout-step-dot">3</span>
                    <span class="checkout-step-label">Pembayaran</span>
                </div>
                <div class="checkout-step-line"></div>
                <div class="checkout-step" data-step="4" role="tab" aria-selected="false" tabindex="0">
                    <span class="checkout-step-dot">4</span>
                    <span class="checkout-step-label">Konfirmasi</span>
                </div>
            </div>

            <div class="shop-grid-2">
                {{-- ==================== Steps ==================== --}}
                <form id="checkoutForm" method="POST" action="{{ $storeUrl }}" novalidate>
                    @csrf
                    <input type="hidden" name="delivery_method" id="deliveryMethodInput" value="{{ $deliveryOptions->first()->code ?? 'instant' }}">
                    <input type="hidden" name="payment_method" id="paymentMethodInput" value="{{ $paymentMethods->first()->code ?? 'va' }}">
                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lng" id="lng">

                    {{-- Step 1: Delivery method --}}
                    <section class="checkout-panel is-active" data-panel="1">
                        <h2 class="checkout-panel-title">Metode Pengiriman</h2>
                        <p class="checkout-panel-sub">Pilih cara pesananmu sampai ke tanganmu</p>
                        <div class="shop-stack">
                            @foreach ($deliveryOptions as $option)
                                <button type="button"
                                    class="delivery-option js-delivery-option {{ $loop->first ? 'selected' : '' }}"
                                    data-code="{{ $option->code }}"
                                    data-cost="{{ (float) ($option->cost ?? 0) }}"
                                    data-eta="{{ $option->eta ?? '' }}"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                    <span class="delivery-option-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="#c-icon-{{ $option->icon ?? ($option->code === 'instant' ? 'zap' : ($option->code === 'pickup' ? 'store' : 'truck')) }}"/>
                                        </svg>
                                    </span>
                                    <span class="delivery-option-info">
                                        <span class="delivery-option-name">{{ $option->name }}</span>
                                        <span class="delivery-option-desc">{{ $option->desc ?? '' }}</span>
                                        <span class="delivery-option-meta">
                                            <span class="eta">{{ $option->eta ?? '' }}</span>
                                            @if ((float) ($option->cost ?? 0) > 0)
                                                <span>{{ $formatRupiah($option->cost) }}</span>
                                            @else
                                                <span>Gratis</span>
                                            @endif
                                        </span>
                                    </span>
                                    <span class="delivery-option-check"></span>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    {{-- Step 2: Address / pickup detail --}}
                    <section class="checkout-panel" data-panel="2">
                        <h2 class="checkout-panel-title">Detail Pengiriman</h2>
                        <p class="checkout-panel-sub">Lengkapi alamat atau jadwal pengambilan</p>

                        <div id="deliveryFields">
                            <div class="shop-field">
                                <label class="shop-label" for="address">Alamat Lengkap</label>
                                <textarea class="shop-textarea" id="address" name="address" required
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan">{{ $customer['address'] ?? '' }}</textarea>
                                <span class="shop-field-error">Alamat wajib diisi</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="shop-field">
                                        <label class="shop-label" for="city">Kota / Kabupaten</label>
                                        <input type="text" class="shop-input" id="city" name="city" required>
                                        <span class="shop-field-error">Kota wajib diisi</span>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="shop-field">
                                        <label class="shop-label" for="postal_code">Kode Pos</label>
                                        <input type="text" class="shop-input" id="postal_code" name="postal_code" inputmode="numeric">
                                    </div>
                                </div>
                            </div>
                            <div class="shop-field">
                                <label class="shop-label" for="notes">Catatan untuk Kurir <span style="color:var(--c-surface-400);font-weight:500">(opsional)</span></label>
                                <input type="text" class="shop-input" id="notes" name="notes" placeholder="Misal: patokan warung biru">
                            </div>
                            <button type="button" class="shop-btn shop-btn-outline" id="useLocationBtn" style="min-height:44px;font-size:0.8125rem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-map-pin"/></svg>
                                Gunakan Lokasi Saya
                            </button>
                        </div>

                        <div id="pickupFields" style="display:none">
                            <div class="shop-field">
                                <label class="shop-label" for="pickup_location">Lokasi Pengambilan</label>
                                <select class="shop-select" id="pickup_location" name="pickup_location_id">
                                    @forelse ($pickupLocations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }} — {{ $location->address }}</option>
                                    @empty
                                        <option value="">Toko Utama — Jl. Raya Sparepart No. 123</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="shop-field">
                                <label class="shop-label" for="pickup_time">Waktu Pengambilan</label>
                                <input type="datetime-local" class="shop-input" id="pickup_time" name="pickup_time">
                                <span class="shop-field-error">Pilih waktu pengambilan</span>
                            </div>
                            <p style="font-size:0.75rem;color:var(--c-surface-500);margin:0">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:-2px;margin-right:4px"><use href="#c-icon-clock"/></svg>
                                Pesanan siap diambil pada waktu yang kamu pilih. Konfirmasi akan dikirim via WhatsApp.
                            </p>
                        </div>
                    </section>

                    {{-- Step 3: Payment method --}}
                    <section class="checkout-panel" data-panel="3">
                        <h2 class="checkout-panel-title">Metode Pembayaran</h2>
                        <p class="checkout-panel-sub">Pilih metode pembayaran yang paling nyaman</p>
                        <div class="shop-stack">
                            @foreach ($paymentMethods as $method)
                                <button type="button"
                                    class="payment-option js-payment-option {{ $loop->first ? 'selected' : '' }}"
                                    data-code="{{ $method->code }}"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                                    <span class="payment-option-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="#c-icon-{{ $method->icon }}"/>
                                        </svg>
                                    </span>
                                    <span class="payment-option-info">
                                        <span class="payment-option-name">{{ $method->name }}</span>
                                        <span class="payment-option-desc">{{ $method->desc ?? '' }}</span>
                                    </span>
                                    <span class="payment-option-check"></span>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    {{-- Step 4: Confirm --}}
                    <section class="checkout-panel" data-panel="4">
                        <h2 class="checkout-panel-title">Konfirmasi Pesanan</h2>
                        <p class="checkout-panel-sub">Periksa kembali pesananmu sebelum membayar</p>

                        <div class="shop-card" style="padding:1rem">
                            @foreach ($cartItems as $item)
                                @php
                                    $product = $item->product ?? $item['product'] ?? null;
                                    $name    = $product->name ?? $item['name'] ?? 'Produk';
                                    $qty     = (float) ($item->quantity ?? $item['quantity'] ?? 1);
                                    $sub     = (float) ($item->subtotal ?? $item['subtotal'] ?? 0);
                                @endphp
                                <div class="order-item">
                                    <span>{{ $name }} <span class="qty">× {{ (int) $qty }}</span></span>
                                    <span class="price">{{ $formatRupiah($sub) }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span style="font-weight:700;color:var(--c-surface-900)">Total Bayar</span>
                            <span style="font-size:1.25rem;font-weight:800;color:var(--c-primary)" id="confirmTotal">{{ $formatRupiah($total) }}</span>
                        </div>
                    </section>

                    {{-- Step navigation --}}
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="shop-btn shop-btn-outline" id="prevStepBtn" style="display:none">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-arrow-left"/></svg>
                            Kembali
                        </button>
                        <button type="button" class="shop-btn shop-btn-primary flex-grow-1" id="nextStepBtn">
                            Lanjut
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-arrow-right"/></svg>
                        </button>
                        <button type="submit" class="shop-btn shop-btn-primary flex-grow-1" id="submitBtn" style="display:none">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-shield"/></svg>
                            Bayar Sekarang
                        </button>
                    </div>
                </form>

                {{-- ==================== Summary sidebar ==================== --}}
                <div class="shop-summary-sticky">
                    <div class="cart-summary shop-card anim-slide-up anim-delay-2"
                        data-subtotal="{{ $subtotal }}"
                        data-discount="{{ $discount }}">
                        <h2 class="cart-summary-title">Ringkasan Pesanan</h2>
                        <div class="cart-summary-row">
                            <span>Subtotal ({{ $itemCount }} item)</span>
                            <span class="value">{{ $formatRupiah($subtotal) }}</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Ongkir</span>
                            <span class="value" id="summaryShipping">—</span>
                        </div>
                        @if ($discount > 0)
                            <div class="cart-summary-row is-discount">
                                <span>Diskon</span>
                                <span class="value">-{{ $formatRupiah($discount) }}</span>
                            </div>
                        @endif
                        <div class="cart-summary-divider"></div>
                        <div class="cart-summary-total">
                            <span class="label">Total Bayar</span>
                            <span class="amount" id="summaryTotal">{{ $formatRupiah($total) }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-3" style="font-size:0.75rem;color:var(--c-surface-500)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;flex-shrink:0"><use href="#c-icon-shield"/></svg>
                            Pembayaran diproses aman melalui payment gateway
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="shop-toast" id="shopToast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var form = document.getElementById('checkoutForm');
            if (!form) return;

            var totalSteps = 4;
            var currentStep = 1;
            var toast = document.getElementById('shopToast');

            var summaryCard = document.querySelector('.cart-summary');
            var summaryShipping = document.getElementById('summaryShipping');
            var summaryTotal = document.getElementById('summaryTotal');
            var confirmTotal = document.getElementById('confirmTotal');

            var baseSubtotal = parseFloat(summaryCard.dataset.subtotal) || 0;
            var baseDiscount = parseFloat(summaryCard.dataset.discount) || 0;
            var shippingCost = 0;

            function formatRupiah(value) {
                return 'Rp ' + Math.round(value).toLocaleString('id-ID');
            }

            function showToast(message) {
                toast.textContent = message;
                toast.classList.add('is-visible');
                clearTimeout(showToast._t);
                showToast._t = setTimeout(function () {
                    toast.classList.remove('is-visible');
                }, 2600);
            }

            /* --- Step navigation -------------------------------------- */
            function goToStep(step) {
                currentStep = step;

                document.querySelectorAll('.checkout-panel').forEach(function (panel) {
                    panel.classList.toggle('is-active', parseInt(panel.dataset.panel, 10) === step);
                });

                document.querySelectorAll('.checkout-step').forEach(function (el) {
                    var s = parseInt(el.dataset.step, 10);
                    el.classList.toggle('active', s === step);
                    el.classList.toggle('done', s < step);
                    el.setAttribute('aria-selected', s === step ? 'true' : 'false');
                });

                document.querySelectorAll('.checkout-step-line').forEach(function (line, i) {
                    line.classList.toggle('done', i < step - 1);
                });

                document.getElementById('prevStepBtn').style.display = step === 1 ? 'none' : '';
                document.getElementById('nextStepBtn').style.display = step === totalSteps ? 'none' : '';
                document.getElementById('submitBtn').style.display = step === totalSteps ? '' : 'none';

                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            function validatePanel(step) {
                var panel = document.querySelector('.checkout-panel[data-panel="' + step + '"]');
                var fields = panel.querySelectorAll('[required]');
                var valid = true;

                fields.forEach(function (field) {
                    var isVisible = field.offsetParent !== null;
                    if (!isVisible) return; /* skip fields in hidden (non-selected) sections */
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        valid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                return valid;
            }

            document.getElementById('nextStepBtn').addEventListener('click', function () {
                if (!validatePanel(currentStep)) {
                    showToast('Lengkapi data yang wajib diisi dulu ya');
                    return;
                }
                goToStep(currentStep + 1);
            });

            document.getElementById('prevStepBtn').addEventListener('click', function () {
                goToStep(currentStep - 1);
            });

            document.querySelectorAll('.checkout-step').forEach(function (el) {
                el.addEventListener('click', function () {
                    var target = parseInt(el.dataset.step, 10);
                    if (target < currentStep) {
                        goToStep(target);
                    }
                });
                el.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        el.click();
                    }
                });
            });

            form.addEventListener('submit', function (e) {
                for (var i = 1; i <= totalSteps; i++) {
                    if (!validatePanel(i)) {
                        goToStep(i);
                        showToast('Lengkapi data yang wajib diisi dulu ya');
                        e.preventDefault();
                        return;
                    }
                }
            });

            /* --- Delivery method -------------------------------------- */
            document.querySelectorAll('.js-delivery-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    document.querySelectorAll('.js-delivery-option').forEach(function (o) {
                        o.classList.remove('selected');
                        o.setAttribute('aria-pressed', 'false');
                    });
                    opt.classList.add('selected');
                    opt.setAttribute('aria-pressed', 'true');

                    document.getElementById('deliveryMethodInput').value = opt.dataset.code;

                    var isPickup = opt.dataset.code === 'pickup';
                    document.getElementById('deliveryFields').style.display = isPickup ? 'none' : '';
                    document.getElementById('pickupFields').style.display = isPickup ? '' : 'none';

                    shippingCost = parseFloat(opt.dataset.cost) || 0;
                    updateSummary();
                });
            });

            /* --- Payment method --------------------------------------- */
            document.querySelectorAll('.js-payment-option').forEach(function (opt) {
                opt.addEventListener('click', function () {
                    document.querySelectorAll('.js-payment-option').forEach(function (o) {
                        o.classList.remove('selected');
                        o.setAttribute('aria-pressed', 'false');
                    });
                    opt.classList.add('selected');
                    opt.setAttribute('aria-pressed', 'true');
                    document.getElementById('paymentMethodInput').value = opt.dataset.code;
                });
            });

            /* --- Summary ---------------------------------------------- */
            function updateSummary() {
                var total = baseSubtotal - baseDiscount + shippingCost;
                summaryShipping.textContent = shippingCost > 0 ? formatRupiah(shippingCost) : 'Gratis';
                summaryTotal.textContent = formatRupiah(total);
                if (confirmTotal) confirmTotal.textContent = formatRupiah(total);
            }

            /* --- Use my location -------------------------------------- */
            var useLocationBtn = document.getElementById('useLocationBtn');
            if (useLocationBtn) {
                useLocationBtn.addEventListener('click', function () {
                    if (!navigator.geolocation) {
                        showToast('Geolokasi tidak didukung browser ini');
                        return;
                    }
                    useLocationBtn.disabled = true;
                    navigator.geolocation.getCurrentPosition(function (pos) {
                        document.getElementById('lat').value = pos.coords.latitude;
                        document.getElementById('lng').value = pos.coords.longitude;
                        showToast('Lokasi terdeteksi. Lengkapi alamatmu ya.');
                        useLocationBtn.disabled = false;
                    }, function () {
                        showToast('Gagal mendeteksi lokasi. Masukkan alamat manual.');
                        useLocationBtn.disabled = false;
                    });
                });
            }
        })();
    </script>
@endpush
@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    $cartItems   = $cartItems ?? collect();
    $cartSummary = $cartSummary ?? [];
    $subtotal    = (float) ($cartSummary['subtotal'] ?? 0);
    $discount    = (float) ($cartSummary['discount'] ?? 0);
    $total       = (float) ($cartSummary['total'] ?? 0);
    $itemCount   = (int) ($cartSummary['item_count'] ?? $cartItems->count());

    $deliveryOptions = $deliveryOptions ?? $this->deliveryOptions;
    $paymentMethods  = $paymentMethods ?? $this->paymentMethods;
    $pickupLocations = $pickupLocations ?? $this->pickupLocations;
    $customer        = $customer ?? $this->customer;
@endphp

<div
    x-data="{
        currentStep: @entangle('currentStep'),
        deliveryMethod: @entangle('deliveryMethod'),
        paymentMethod: @entangle('paymentMethod'),
        toastMessage: @entangle('toastMessage'),
        showToast: @entangle('showToast'),
        shippingCost: 0,
        init() {
            this.$watch('showToast', (val) => {
                if (val) {
                    setTimeout(() => { this.showToast = false; }, 2600);
                }
            });
            this.$watch('deliveryMethod', (val) => {
                const opt = this.$el.querySelector('.js-delivery-option[data-code=\"' + val + '\"]');
                if (opt) this.shippingCost = parseFloat(opt.dataset.cost) || 0;
            });
        },
        isPickup() { return this.deliveryMethod === 'pickup'; },
        grandTotal() {
            return {{ $subtotal }} - {{ $discount }} + this.shippingCost;
        },
        formatRupiah(value) {
            return 'Rp ' + Math.round(value).toLocaleString('id-ID');
        },
        goToStep(step) {
            if (step < this.currentStep) this.currentStep = step;
        },
        nextStep() {
            if (this.currentStep === 2 && !this.isPickup() && (!this.$refs.address || !this.$refs.address.value.trim() || !this.$refs.city || !this.$refs.city.value.trim())) {
                this.showToast = true;
                this.toastMessage = 'Lengkapi data yang wajib diisi dulu ya';
                return;
            }
            if (this.currentStep < 4) this.currentStep++;
        },
        prevStep() {
            if (this.currentStep > 1) this.currentStep--;
        },
        selectDelivery(code, cost) {
            this.deliveryMethod = code;
            this.shippingCost = parseFloat(cost) || 0;
        },
        selectPayment(code) {
            this.paymentMethod = code;
        },
        useLocation() {
            if (!navigator.geolocation) {
                this.toastMessage = 'Geolokasi tidak didukung browser ini';
                this.showToast = true;
                return;
            }
            navigator.geolocation.getCurrentPosition((pos) => {
                @this.set('lat', pos.coords.latitude);
                @this.set('lng', pos.coords.longitude);
                this.toastMessage = 'Lokasi terdeteksi. Lengkapi alamatmu ya.';
                this.showToast = true;
            }, () => {
                this.toastMessage = 'Gagal mendeteksi lokasi. Masukkan alamat manual.';
                this.showToast = true;
            });
        }
    }"
>
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
                <div class="checkout-step"
                    :class="{ 'active': currentStep === 1, 'done': currentStep > 1 }"
                    data-step="1" role="tab" :aria-selected="currentStep === 1 ? 'true' : 'false'" tabindex="0"
                    @click="goToStep(1)">
                    <span class="checkout-step-dot">1</span>
                    <span class="checkout-step-label">Pengiriman</span>
                </div>
                <div class="checkout-step-line" :class="{ 'done': currentStep > 2 }"></div>
                <div class="checkout-step"
                    :class="{ 'active': currentStep === 2, 'done': currentStep > 2 }"
                    data-step="2" role="tab" :aria-selected="currentStep === 2 ? 'true' : 'false'" tabindex="0"
                    @click="goToStep(2)">
                    <span class="checkout-step-dot">2</span>
                    <span class="checkout-step-label">Detail</span>
                </div>
                <div class="checkout-step-line" :class="{ 'done': currentStep > 3 }"></div>
                <div class="checkout-step"
                    :class="{ 'active': currentStep === 3, 'done': currentStep > 3 }"
                    data-step="3" role="tab" :aria-selected="currentStep === 3 ? 'true' : 'false'" tabindex="0"
                    @click="goToStep(3)">
                    <span class="checkout-step-dot">3</span>
                    <span class="checkout-step-label">Pembayaran</span>
                </div>
                <div class="checkout-step-line" :class="{ 'done': currentStep > 4 }"></div>
                <div class="checkout-step"
                    :class="{ 'active': currentStep === 4, 'done': currentStep > 4 }"
                    data-step="4" role="tab" :aria-selected="currentStep === 4 ? 'true' : 'false'" tabindex="0"
                    @click="goToStep(4)">
                    <span class="checkout-step-dot">4</span>
                    <span class="checkout-step-label">Konfirmasi</span>
                </div>
            </div>

            <div class="shop-grid-2">
                {{-- ==================== Steps ==================== --}}
                <form wire:submit="submitOrder" novalidate>
                    {{-- Step 1: Delivery method --}}
                    <section class="checkout-panel" :class="{ 'is-active': currentStep === 1 }" data-panel="1">
                        <h2 class="checkout-panel-title">Metode Pengiriman</h2>
                        <p class="checkout-panel-sub">Pilih cara pesananmu sampai ke tanganmu</p>
                        <div class="shop-stack">
                            @foreach ($deliveryOptions as $option)
                                <button type="button"
                                    class="delivery-option js-delivery-option"
                                    :class="{ 'selected': deliveryMethod === '{{ $option['code'] }}' }"
                                    data-code="{{ $option['code'] }}"
                                    data-cost="{{ (float) ($option['cost'] ?? 0) }}"
                                    data-eta="{{ $option['eta'] ?? '' }}"
                                    :aria-pressed="deliveryMethod === '{{ $option['code'] }}' ? 'true' : 'false'"
                                    @click="selectDelivery('{{ $option['code'] }}', {{ (float) ($option['cost'] ?? 0) }})">
                                    <span class="delivery-option-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="#c-icon-{{ $option['icon'] ?? ($option['code'] === 'instant' ? 'zap' : ($option['code'] === 'pickup' ? 'store' : 'truck')) }}"/>
                                        </svg>
                                    </span>
                                    <span class="delivery-option-info">
                                        <span class="delivery-option-name">{{ $option['name'] }}</span>
                                        <span class="delivery-option-desc">{{ $option['desc'] ?? '' }}</span>
                                        <span class="delivery-option-meta">
                                            <span class="eta">{{ $option['eta'] ?? '' }}</span>
                                            @if ((float) ($option['cost'] ?? 0) > 0)
                                                <span>{{ $formatRupiah($option['cost']) }}</span>
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
                    <section class="checkout-panel" :class="{ 'is-active': currentStep === 2 }" data-panel="2">
                        <h2 class="checkout-panel-title">Detail Pengiriman</h2>
                        <p class="checkout-panel-sub">Lengkapi alamat atau jadwal pengambilan</p>

                        <div x-show="!isPickup()">
                            <div class="shop-field">
                                <label class="shop-label" for="address">Alamat Lengkap</label>
                                <textarea class="shop-textarea" id="address" x-ref="address"
                                    wire:model="address"
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan">{{ $customer['address'] ?? '' }}</textarea>
                                <span class="shop-field-error">Alamat wajib diisi</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-sm-6">
                                    <div class="shop-field">
                                        <label class="shop-label" for="city">Kota / Kabupaten</label>
                                        <input type="text" class="shop-input" id="city" x-ref="city" wire:model="city">
                                        <span class="shop-field-error">Kota wajib diisi</span>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="shop-field">
                                        <label class="shop-label" for="postal_code">Kode Pos</label>
                                        <input type="text" class="shop-input" id="postal_code" wire:model="postalCode" inputmode="numeric">
                                    </div>
                                </div>
                            </div>
                            <div class="shop-field">
                                <label class="shop-label" for="notes">Catatan untuk Kurir <span style="color:var(--c-surface-400);font-weight:500">(opsional)</span></label>
                                <input type="text" class="shop-input" id="notes" wire:model="notes" placeholder="Misal: patokan warung biru">
                            </div>
                            <button type="button" class="shop-btn shop-btn-outline" @click="useLocation()" style="min-height:44px;font-size:0.8125rem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-map-pin"/></svg>
                                Gunakan Lokasi Saya
                            </button>
                        </div>

                        <div x-show="isPickup()">
                            <div class="shop-field">
                                <label class="shop-label" for="pickup_location">Lokasi Pengambilan</label>
                                <select class="shop-select" id="pickup_location" wire:model="pickupLocationId">
                                    @forelse ($pickupLocations as $location)
                                        <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                                    @empty
                                        <option value="">Toko Utama — Jl. Raya Sparepart No. 123</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="shop-field">
                                <label class="shop-label" for="pickup_time">Waktu Pengambilan</label>
                                <input type="datetime-local" class="shop-input" id="pickup_time" wire:model="pickupTime">
                                <span class="shop-field-error">Pilih waktu pengambilan</span>
                            </div>
                            <p style="font-size:0.75rem;color:var(--c-surface-500);margin:0">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;vertical-align:-2px;margin-right:4px"><use href="#c-icon-clock"/></svg>
                                Pesanan siap diambil pada waktu yang kamu pilih. Konfirmasi akan dikirim via WhatsApp.
                            </p>
                        </div>
                    </section>

                    {{-- Step 3: Payment method --}}
                    <section class="checkout-panel" :class="{ 'is-active': currentStep === 3 }" data-panel="3">
                        <h2 class="checkout-panel-title">Metode Pembayaran</h2>
                        <p class="checkout-panel-sub">Pilih metode pembayaran yang paling nyaman</p>
                        <div class="shop-stack">
                            @foreach ($paymentMethods as $method)
                                <button type="button"
                                    class="payment-option js-payment-option"
                                    :class="{ 'selected': paymentMethod === '{{ $method['code'] }}' }"
                                    data-code="{{ $method['code'] }}"
                                    :aria-pressed="paymentMethod === '{{ $method['code'] }}' ? 'true' : 'false'"
                                    @click="selectPayment('{{ $method['code'] }}')">
                                    <span class="payment-option-icon">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <use href="#c-icon-{{ $method['icon'] }}"/>
                                        </svg>
                                    </span>
                                    <span class="payment-option-info">
                                        <span class="payment-option-name">{{ $method['name'] }}</span>
                                        <span class="payment-option-desc">{{ $method['desc'] ?? '' }}</span>
                                    </span>
                                    <span class="payment-option-check"></span>
                                </button>
                            @endforeach
                        </div>
                    </section>

                    {{-- Step 4: Confirm --}}
                    <section class="checkout-panel" :class="{ 'is-active': currentStep === 4 }" data-panel="4">
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
                            <span style="font-size:1.25rem;font-weight:800;color:var(--c-primary)" x-text="formatRupiah(grandTotal())">{{ $formatRupiah($total) }}</span>
                        </div>
                    </section>

                    {{-- Step navigation --}}
                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="shop-btn shop-btn-outline" @click="prevStep()" x-show="currentStep > 1">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-arrow-left"/></svg>
                            Kembali
                        </button>
                        <button type="button" class="shop-btn shop-btn-primary flex-grow-1" @click="nextStep()" x-show="currentStep < 4">
                            Lanjut
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-arrow-right"/></svg>
                        </button>
                        <button type="submit" class="shop-btn shop-btn-primary flex-grow-1" x-show="currentStep === 4">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-shield"/></svg>
                            Bayar Sekarang
                        </button>
                    </div>
                </form>

                {{-- ==================== Summary sidebar ==================== --}}
                <div class="shop-summary-sticky">
                    <div class="cart-summary shop-card anim-slide-up anim-delay-2">
                        <h2 class="cart-summary-title">Ringkasan Pesanan</h2>
                        <div class="cart-summary-row">
                            <span>Subtotal ({{ $itemCount }} item)</span>
                            <span class="value">{{ $formatRupiah($subtotal) }}</span>
                        </div>
                        <div class="cart-summary-row">
                            <span>Ongkir</span>
                            <span class="value" x-text="shippingCost > 0 ? formatRupiah(shippingCost) : 'Gratis'">—</span>
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
                            <span class="amount" x-text="formatRupiah(grandTotal())">{{ $formatRupiah($total) }}</span>
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

    {{-- Toast notification --}}
    <div class="shop-toast" x-show="showToast" x-transition.opacity.duration.300ms role="status" aria-live="polite">
        <span x-text="toastMessage"></span>
    </div>
</div>

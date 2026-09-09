@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    $cartItems   = $cartItems ?? collect();
    $cartSummary = $cartSummary ?? [];
    $subtotal    = (float) ($cartSummary['subtotal'] ?? 0);
    $discount    = (float) ($cartSummary['discount'] ?? 0);
    $total       = (float) ($cartSummary['total'] ?? 0);
    $itemCount   = (int) ($cartSummary['item_count'] ?? $cartItems->count());

    $checkoutUrl = \Illuminate\Support\Facades\Route::has('website.checkout') ? route('website.checkout') : url('/checkout');
@endphp

<div
    x-data="{
        toastMessage: @entangle('toastMessage'),
        showToast: @entangle('showToast'),
        init() {
            this.$watch('showToast', (val) => {
                if (val) {
                    setTimeout(() => { this.showToast = false; }, 2600);
                }
            });
        }
    }"
>
    {{-- Local icon sprite (extends the layout sprite with shop-specific icons) --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="c-icon-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </symbol>
        <symbol id="c-icon-minus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/>
        </symbol>
        <symbol id="c-icon-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        </symbol>
        <symbol id="c-icon-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
        </symbol>
    </svg>

    <div class="shop-container">
        <header class="shop-page-header anim-fade-in">
            <h1 class="shop-page-title">Keranjang</h1>
            <p class="shop-page-sub">
                @if ($cartItems->isEmpty())
                    Keranjang belanjamu masih kosong
                @else
                    {{ $itemCount }} item di keranjangmu
                @endif
            </p>
        </header>

        {{-- ============================================================
             Empty cart state
             ============================================================ --}}
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
                    <path d="M140 52l2.5 6 6 2.5-6 2.5-2.5 6-2.5-6-6-2.5 6-2.5z" fill="#c7d2fe"/>
                    <path d="M52 48l2 5 5 2-5 2-2 5-2-5-5-2 5-2z" fill="#c7d2fe"/>
                    <circle cx="150" cy="86" r="3" fill="#c7d2fe"/>
                    <circle cx="46" cy="96" r="2.5" fill="#c7d2fe"/>
                </svg>
                <h2 class="shop-empty-title">Keranjangmu masih kosong</h2>
                <p class="shop-empty-desc">Yuk isi keranjang dengan sparepart HP berkualitas dari katalog kami.</p>
                <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-primary">Mulai Belanja</a>
            </div>

        {{-- ============================================================
             Cart items + summary
             ============================================================ --}}
        @else
            <div class="shop-grid-2">
                <div class="shop-stack" id="cartItemsList">
                    @foreach ($cartItems as $item)
                        @php
                            $product   = $item->product ?? $item['product'] ?? null;
                            $name      = $product->name ?? $item['name'] ?? 'Produk';
                            $code      = $product->product_code ?? $item['product_code'] ?? '';
                            $image     = $product->primaryImageUrl() ?? $item['image'] ?? null;
                            $price     = (float) ($item->unit_price ?? $item['unit_price'] ?? $product->selling_price ?? 0);
                            $qty       = (float) ($item->quantity ?? $item['quantity'] ?? 1);
                            $lineTotal = (float) ($item->subtotal ?? $item['subtotal'] ?? ($price * $qty));
                            $itemId    = $item->id ?? $item['id'] ?? $loop->index;
                        @endphp
                        <div class="cart-item anim-slide-up anim-delay-{{ min($loop->iteration, 5) }}"
                            wire:key="cart-item-{{ $itemId }}">
                            <button type="button" class="cart-item-delete"
                                wire:click="removeItem({{ $itemId }})"
                                aria-label="Hapus {{ $name }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-trash"/></svg>
                                Hapus
                            </button>
                            <div class="cart-item-body">
                                @if ($image)
                                    <img src="{{ $image }}" alt="{{ $name }}" class="cart-item-image" loading="lazy">
                                @else
                                    <div class="cart-item-image d-flex align-items-center justify-content-center text-muted" style="font-size:0.6875rem">No Photo</div>
                                @endif
                                <div class="cart-item-info">
                                    <div class="cart-item-name">{{ $name }}</div>
                                    @if ($code)
                                        <div class="cart-item-code">{{ $code }}</div>
                                    @endif
                                    <div class="cart-item-price">{{ $formatRupiah($lineTotal) }}</div>
                                </div>
                                <div class="cart-item-actions">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn"
                                            wire:click="updateQuantity({{ $itemId }}, {{ max(1, $qty - 1) }})"
                                            aria-label="Kurangi jumlah {{ $name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-minus"/></svg>
                                        </button>
                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                            class="qty-input"
                                            value="{{ (int) $qty }}"
                                            wire:change="updateQuantity({{ $itemId }}, $event.target.value)"
                                            aria-label="Jumlah {{ $name }}">
                                        <button type="button" class="qty-btn"
                                            wire:click="updateQuantity({{ $itemId }}, {{ $qty + 1 }})"
                                            aria-label="Tambah jumlah {{ $name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-plus"/></svg>
                                        </button>
                                    </div>
                                    <button type="button" class="cart-item-remove" wire:click="removeItem({{ $itemId }})">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-trash"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="shop-summary-sticky">
                    <div class="cart-summary shop-card anim-slide-up anim-delay-2">
                        <h2 class="cart-summary-title">Ringkasan Belanja</h2>
                        <div class="cart-summary-row">
                            <span>Subtotal ({{ $itemCount }} item)</span>
                            <span class="value">{{ $formatRupiah($subtotal) }}</span>
                        </div>
                        @if ($discount > 0)
                            <div class="cart-summary-row is-discount">
                                <span>Diskon</span>
                                <span class="value">-{{ $formatRupiah($discount) }}</span>
                            </div>
                        @endif
                        <div class="cart-summary-divider"></div>
                        <div class="cart-summary-total">
                            <span class="label">Total</span>
                            <span class="amount">{{ $formatRupiah($total) }}</span>
                        </div>
                        <a href="{{ $checkoutUrl }}" class="shop-btn shop-btn-primary shop-btn-block mt-3">
                            Lanjut ke Checkout
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-arrow-right"/></svg>
                        </a>
                        <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-outline shop-btn-block mt-2">Lanjut Belanja</a>
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

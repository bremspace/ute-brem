@extends('website.layout')

@section('title', 'Keranjang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    // Data contract (from CartController@index):
    //   $cartItems   — Collection|null. null = loading (renders skeleton).
    //                  Each item: id, product (Product model), quantity,
    //                  unit_price, discount_amount, subtotal.
    //   $cartSummary — ['subtotal' => float, 'discount' => float,
    //                  'total' => float, 'item_count' => int]
    $cartItems   = $cartItems ?? collect();
    $cartSummary = $cartSummary ?? [];
    $subtotal    = (float) ($cartSummary['subtotal'] ?? 0);
    $discount    = (float) ($cartSummary['discount'] ?? 0);
    $total       = (float) ($cartSummary['total'] ?? 0);
    $itemCount   = (int) ($cartSummary['item_count'] ?? $cartItems->count());

    $checkoutUrl = Route::has('website.checkout') ? route('website.checkout') : url('/checkout');
@endphp

@section('content')
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
            <p class="shop-page-sub" id="cartPageSub">
                @if ($cartItems === null)
                    Memuat keranjangmu...
                @elseif ($cartItems->isEmpty())
                    Keranjang belanjamu masih kosong
                @else
                    {{ $itemCount }} item di keranjangmu
                @endif
            </p>
        </header>

        {{-- ============================================================
             Skeleton loading state — shown while $cartItems is null
             ============================================================ --}}
        @if ($cartItems === null)
            <div class="shop-grid-2" id="cartSkeleton" aria-hidden="true">
                <div class="shop-stack">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="skeleton-card">
                            <div class="skeleton skeleton-circle" style="width:76px;height:76px"></div>
                            <div style="flex:1;min-width:0">
                                <div class="skeleton" style="height:14px;width:70%;margin-bottom:8px"></div>
                                <div class="skeleton" style="height:12px;width:40%;margin-bottom:12px"></div>
                                <div class="skeleton" style="height:16px;width:30%"></div>
                            </div>
                        </div>
                    @endfor
                </div>
                <div class="skeleton-card" style="flex-direction:column">
                    <div class="skeleton" style="height:16px;width:50%;margin-bottom:16px"></div>
                    <div class="skeleton" style="height:14px;width:100%;margin-bottom:10px"></div>
                    <div class="skeleton" style="height:14px;width:100%;margin-bottom:10px"></div>
                    <div class="skeleton" style="height:14px;width:100%;margin-bottom:16px"></div>
                    <div class="skeleton" style="height:48px;width:100%;border-radius:12px"></div>
                </div>
            </div>

        {{-- ============================================================
             Empty cart state
             ============================================================ --}}
        @elseif ($cartItems->isEmpty())
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
                            $updateUrl = Route::has('website.cart.update') ? route('website.cart.update', $itemId) : '';
                            $removeUrl = Route::has('website.cart.remove') ? route('website.cart.remove', $itemId) : '';
                        @endphp
                        <div class="cart-item anim-slide-up anim-delay-{{ min($loop->iteration, 5) }}"
                            data-id="{{ $itemId }}"
                            data-price="{{ $price }}"
                            data-update-url="{{ $updateUrl }}"
                            data-remove-url="{{ $removeUrl }}">
                            <button type="button" class="cart-item-delete js-cart-delete" aria-label="Hapus {{ $name }}">
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
                                        <button type="button" class="qty-btn js-qty-minus" aria-label="Kurangi jumlah {{ $name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-minus"/></svg>
                                        </button>
                                        <input type="text" inputmode="numeric" pattern="[0-9]*"
                                            class="qty-input js-qty-input" value="{{ (int) $qty }}"
                                            aria-label="Jumlah {{ $name }}">
                                        <button type="button" class="qty-btn js-qty-plus" aria-label="Tambah jumlah {{ $name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-plus"/></svg>
                                        </button>
                                    </div>
                                    <button type="button" class="cart-item-remove js-cart-remove">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-trash"/></svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="shop-summary-sticky">
                    <div class="cart-summary shop-card anim-slide-up anim-delay-2"
                        data-subtotal="{{ $subtotal }}"
                        data-discount="{{ $discount }}">
                        <h2 class="cart-summary-title">Ringkasan Belanja</h2>
                        <div class="cart-summary-row">
                            <span>Subtotal ({{ $itemCount }} item)</span>
                            <span class="value" id="summarySubtotal">{{ $formatRupiah($subtotal) }}</span>
                        </div>
                        @if ($discount > 0)
                            <div class="cart-summary-row is-discount">
                                <span>Diskon</span>
                                <span class="value" id="summaryDiscount">-{{ $formatRupiah($discount) }}</span>
                            </div>
                        @endif
                        <div class="cart-summary-divider"></div>
                        <div class="cart-summary-total">
                            <span class="label">Total</span>
                            <span class="amount" id="summaryTotal">{{ $formatRupiah($total) }}</span>
                        </div>
                        <a href="{{ $checkoutUrl }}" class="shop-btn shop-btn-primary shop-btn-block mt-3">
                            Lanjut ke Checkout
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-arrow-right"/></svg>
                        </a>
                        <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-outline shop-btn-block mt-2">Lanjut Belanja</a>
                    </div>
                </div>
            </div>

            {{-- Hidden template: swapped in by JS when the last item is removed --}}
            <template id="cartEmptyTemplate">
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
            </template>
        @endif
    </div>

    <div class="shop-toast" id="shopToast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var list = document.getElementById('cartItemsList');
            if (!list) return;

            var summaryCard   = document.querySelector('.cart-summary');
            var summarySubtotal = document.getElementById('summarySubtotal');
            var summaryDiscount = document.getElementById('summaryDiscount');
            var summaryTotal    = document.getElementById('summaryTotal');
            var pageSub         = document.getElementById('cartPageSub');
            var toast           = document.getElementById('shopToast');
            var emptyTemplate   = document.getElementById('cartEmptyTemplate');
            var csrfToken       = document.querySelector('meta[name="csrf-token"]');

            var baseSubtotal = parseFloat(summaryCard.dataset.subtotal) || 0;
            var baseDiscount = parseFloat(summaryCard.dataset.discount) || 0;

            function formatRupiah(value) {
                return 'Rp ' + Math.round(value).toLocaleString('id-ID');
            }

            function showToast(message) {
                if (!toast) return;
                toast.textContent = message;
                toast.classList.add('is-visible');
                clearTimeout(showToast._t);
                showToast._t = setTimeout(function () {
                    toast.classList.remove('is-visible');
                }, 2600);
            }

            function syncServer(method, url, body) {
                if (!url) return Promise.resolve();
                var headers = { 'Accept': 'application/json' };
                if (csrfToken) headers['X-CSRF-TOKEN'] = csrfToken.content;
                if (body) headers['Content-Type'] = 'application/json';
                return fetch(url, {
                    method: method,
                    headers: headers,
                    body: body ? JSON.stringify(body) : undefined
                }).catch(function () {
                    /* Offline / route not implemented — keep optimistic UI. */
                });
            }

            function recalc() {
                var items = list.querySelectorAll('.cart-item');
                var subtotal = 0;
                var count = 0;

                items.forEach(function (item) {
                    var price = parseFloat(item.dataset.price) || 0;
                    var input = item.querySelector('.js-qty-input');
                    var qty = Math.max(1, parseInt(input.value, 10) || 1);
                    input.value = qty;
                    subtotal += price * qty;
                    count += qty;
                    item.querySelector('.cart-item-price').textContent = formatRupiah(price * qty);
                });

                var total = subtotal - baseDiscount;
                if (summarySubtotal) summarySubtotal.textContent = formatRupiah(subtotal);
                if (summaryTotal) summaryTotal.textContent = formatRupiah(total);
                if (pageSub) pageSub.textContent = count + ' item di keranjangmu';

                if (typeof window.UTEParts !== 'undefined' && window.UTEParts.updateCartCount) {
                    window.UTEParts.updateCartCount(count);
                }
            }

            function removeItem(item) {
                var removeUrl = item.dataset.removeUrl || '';
                var id = item.dataset.id;

                item.classList.add('is-swiped');
                setTimeout(function () {
                    item.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.96)';
                }, 120);

                setTimeout(function () {
                    item.remove();
                    recalc();
                    syncServer('DELETE', removeUrl);

                    if (list.querySelectorAll('.cart-item').length === 0 && emptyTemplate) {
                        var clone = emptyTemplate.content.cloneNode(true);
                        var grid = document.querySelector('.shop-grid-2');
                        if (grid) grid.replaceWith(clone);
                        if (pageSub) pageSub.textContent = 'Keranjang belanjamu masih kosong';
                    }
                }, 380);
            }

            /* --- Quantity steppers ------------------------------------ */
            list.addEventListener('click', function (e) {
                var btn = e.target.closest('.js-qty-plus, .js-qty-minus');
                if (!btn) return;

                var item = btn.closest('.cart-item');
                var input = item.querySelector('.js-qty-input');
                var qty = parseInt(input.value, 10) || 1;

                if (btn.classList.contains('js-qty-plus')) {
                    qty += 1;
                } else {
                    qty = Math.max(1, qty - 1);
                }

                input.value = qty;
                recalc();
                syncServer('PATCH', item.dataset.updateUrl, { quantity: qty });
            });

            list.addEventListener('change', function (e) {
                var input = e.target.closest('.js-qty-input');
                if (!input) return;

                var item = input.closest('.cart-item');
                var qty = Math.max(1, parseInt(input.value, 10) || 1);
                input.value = qty;
                recalc();
                syncServer('PATCH', item.dataset.updateUrl, { quantity: qty });
            });

            /* --- Remove buttons --------------------------------------- */
            list.addEventListener('click', function (e) {
                var btn = e.target.closest('.js-cart-remove, .js-cart-delete');
                if (!btn) return;
                removeItem(btn.closest('.cart-item'));
            });

            /* --- Swipe to delete (mobile) ----------------------------- */
            var startX = 0;
            var currentX = 0;
            var dragging = false;
            var activeItem = null;

            list.addEventListener('touchstart', function (e) {
                var item = e.target.closest('.cart-item');
                if (!item) return;
                activeItem = item;
                dragging = true;
                startX = e.touches[0].clientX;
                currentX = 0;
            }, { passive: true });

            list.addEventListener('touchmove', function (e) {
                if (!dragging || !activeItem) return;
                currentX = e.touches[0].clientX - startX;
                if (currentX < 0) {
                    activeItem.style.transform = 'translateX(' + Math.max(currentX, -88) + 'px)';
                }
            }, { passive: true });

            list.addEventListener('touchend', function () {
                if (!dragging) return;
                dragging = false;
                if (activeItem) {
                    if (currentX < -44) {
                        activeItem.classList.add('is-swiped');
                    } else {
                        activeItem.classList.remove('is-swiped');
                    }
                    activeItem.style.transform = '';
                    activeItem = null;
                }
                currentX = 0;
            });
        })();
    </script>
@endpush
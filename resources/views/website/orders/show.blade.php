@extends('website.layout')

@section('title', 'Pesanan Berhasil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

    // Data contract (from OrderController@show):
    //   $order — object with: order_code, status, status_label, grand_total,
    //            payment_method, payment_method_name, payment_reference,
    //            payment_expires_at (Carbon|null), delivery_method, delivery_eta,
    //            items (Collection: name, quantity, subtotal)
    $order = $order ?? null;

    if ($order) {
        $paymentMethod    = strtolower($order->payment_method ?? 'va');
        $paymentName      = $order->payment_method_name ?? ucfirst($paymentMethod);
        $paymentReference = $order->payment_reference ?? '';
        $expiresAt        = $order->payment_expires_at?->toIso8601String() ?? now()->addHours(24)->toIso8601String();
        $statusLabel      = $order->status_label ?? ($order->status ?? 'Menunggu Pembayaran');
        $orderItems       = $order->items ?? collect();

        // Deterministic pseudo-QR (21×21 modules) so the page renders before
        // a real QR generator is wired in. Seeded by order code → stable per order.
        $qrSize = 21;
        $qrModules = [];
        mt_srand(crc32($order->order_code ?? 'UTE'));
        for ($y = 0; $y < $qrSize; $y++) {
            for ($x = 0; $x < $qrSize; $x++) {
                $qrModules[$y][$x] = mt_rand(0, 1) === 1;
            }
        }
        $drawFinder = function ($fx, $fy) use (&$qrModules) {
            for ($y = 0; $y < 7; $y++) {
                for ($x = 0; $x < 7; $x++) {
                    $on = $x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4);
                    $qrModules[$fy + $y][$fx + $x] = $on;
                }
            }
        };
        $drawFinder(0, 0);
        $drawFinder(14, 0);
        $drawFinder(0, 14);
        mt_srand();
    }
@endphp

@section('content')
    {{-- Local icon sprite --}}
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="c-icon-package" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
        </symbol>
        <symbol id="c-icon-copy" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
        </symbol>
        <symbol id="c-icon-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </symbol>
        <symbol id="c-icon-landmark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="22" x2="21" y2="22"/><line x1="6" y1="18" x2="6" y2="11"/><line x1="10" y1="18" x2="10" y2="11"/><line x1="14" y1="18" x2="14" y2="11"/><line x1="18" y1="18" x2="18" y2="11"/><polygon points="12 2 20 7 4 7"/>
        </symbol>
        <symbol id="c-icon-wallet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/>
        </symbol>
        <symbol id="c-icon-qr" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3z"/><path d="M20 14h1v1h-1z"/><path d="M14 20h1v1h-1z"/><path d="M18 18h3v3h-3z"/>
        </symbol>
        <symbol id="c-icon-credit-card" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
        </symbol>
        <symbol id="c-icon-truck" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
        </symbol>
        <symbol id="c-icon-store" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 9l1-5h16l1 5"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M5 13v8h14v-8"/><path d="M9 21v-6h6v6"/>
        </symbol>
    </svg>

    <div class="shop-container">
        @if ($order)
            <div class="order-success anim-fade-in">
                <div class="order-success-card anim-scale-in">
                    {{-- Animated checkmark --}}
                    <div class="checkmark-wrap">
                        <svg class="checkmark-svg" viewBox="0 0 52 52" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <circle class="checkmark-circle" cx="26" cy="26" r="25"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>

                    <h1 class="order-success-title">Pesanan Berhasil Dibuat!</h1>
                    <p class="order-success-sub">Terima kasih, pesananmu sedang kami proses.</p>

                    <div class="order-code-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-package"/></svg>
                        {{ $order->order_code }}
                    </div>

                    {{-- Payment countdown --}}
                    <p class="countdown-label">Selesaikan pembayaran sebelum</p>
                    <div class="countdown" id="countdown" data-expires="{{ $expiresAt }}">
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdHours">--</div>
                            <div class="countdown-unit">Jam</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdMinutes">--</div>
                            <div class="countdown-unit">Menit</div>
                        </div>
                        <div class="countdown-box">
                            <div class="countdown-num" id="cdSeconds">--</div>
                            <div class="countdown-unit">Detik</div>
                        </div>
                    </div>

                    {{-- Payment instructions --}}
                    <div class="payment-box">
                        <div class="payment-box-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <use href="#c-icon-{{ $paymentMethod === 'va' ? 'landmark' : ($paymentMethod === 'qris' ? 'qr' : ($paymentMethod === 'card' ? 'credit-card' : 'wallet')) }}"/>
                            </svg>
                            Bayar via {{ $paymentName }}
                        </div>

                        @if ($paymentMethod === 'va')
                            <div class="payment-va">
                                <div>
                                    <div class="payment-va-label">Nomor Virtual Account</div>
                                    <div class="payment-va-number">{{ $paymentReference ?: '8800 1234 5678 9012' }}</div>
                                </div>
                                <button type="button" class="payment-copy-btn js-copy" data-copy="{{ $paymentReference ?: '8800123456789012' }}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-copy"/></svg>
                                    Salin
                                </button>
                            </div>
                            <ol class="payment-steps">
                                <li>Buka aplikasi mobile banking / ATM dari bank kamu.</li>
                                <li>Pilih menu Transfer &gt; Virtual Account, lalu masukkan nomor di atas.</li>
                                <li>Masukkan nominal <strong>{{ $formatRupiah($order->grand_total) }}</strong> dan konfirmasi.</li>
                                <li>Pesanan otomatis terverifikasi setelah pembayaran masuk.</li>
                            </ol>
                        @elseif ($paymentMethod === 'qris')
                            <div class="payment-qr">
                                <svg viewBox="0 0 {{ $qrSize * 8 + 32 }} {{ $qrSize * 8 + 32 }}" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="QR Code pembayaran">
                                    <rect width="100%" height="100%" fill="#ffffff"/>
                                    @foreach ($qrModules as $y => $row)
                                        @foreach ($row as $x => $on)
                                            @if ($on)
                                                <rect x="{{ $x * 8 + 16 }}" y="{{ $y * 8 + 16 }}" width="8" height="8" fill="#111111"/>
                                            @endif
                                        @endforeach
                                    @endforeach
                                </svg>
                                <div class="payment-qr-hint">Scan QRIS ini dengan aplikasi apa pun (GoPay, OVO, DANA, ShopeePay, mobile banking)</div>
                            </div>
                            <ol class="payment-steps">
                                <li>Buka aplikasi pembayaran &amp; pilih menu Scan / QRIS.</li>
                                <li>Scan kode QR di atas.</li>
                                <li>Periksa nominal <strong>{{ $formatRupiah($order->grand_total) }}</strong>, lalu konfirmasi.</li>
                                <li>Pesanan otomatis terverifikasi setelah pembayaran masuk.</li>
                            </ol>
                        @elseif ($paymentMethod === 'card')
                            <ol class="payment-steps">
                                <li>Klik tombol bayar di bawah untuk membuka halaman pembayaran kartu.</li>
                                <li>Masukkan nomor kartu, masa berlaku, dan CVV.</li>
                                <li>Ikuti instruksi 3D Secure dari bank penerbit.</li>
                                <li>Pesanan otomatis terverifikasi setelah pembayaran berhasil.</li>
                            </ol>
                        @else
                            <ol class="payment-steps">
                                <li>Buka aplikasi {{ $paymentName }} di ponsel kamu.</li>
                                <li>Pilih menu Bayar, lalu masukkan kode pesanan <strong>{{ $order->order_code }}</strong>.</li>
                                <li>Periksa nominal <strong>{{ $formatRupiah($order->grand_total) }}</strong>, lalu konfirmasi.</li>
                                <li>Pesanan otomatis terverifikasi setelah pembayaran masuk.</li>
                            </ol>
                        @endif
                    </div>

                    {{-- Order meta --}}
                    <div class="order-meta">
                        <div class="order-meta-item">
                            <div class="order-meta-label">Total Bayar</div>
                            <div class="order-meta-value">{{ $formatRupiah($order->grand_total) }}</div>
                        </div>
                        <div class="order-meta-item">
                            <div class="order-meta-label">Status</div>
                            <div class="order-meta-value">{{ $statusLabel }}</div>
                        </div>
                        <div class="order-meta-item">
                            <div class="order-meta-label">Pengiriman</div>
                            <div class="order-meta-value">{{ $order->delivery_method ?? 'Instant' }}</div>
                        </div>
                        <div class="order-meta-item">
                            <div class="order-meta-label">Estimasi Tiba</div>
                            <div class="order-meta-value">{{ $order->delivery_eta ?? '± 30 menit' }}</div>
                        </div>
                    </div>

                    {{-- Items --}}
                    @if ($orderItems->isNotEmpty())
                        <div class="order-items">
                            @foreach ($orderItems as $item)
                                <div class="order-item">
                                    <span>{{ $item->name ?? 'Produk' }} <span class="qty">× {{ (int) ($item->quantity ?? 1) }}</span></span>
                                    <span class="price">{{ $formatRupiah($item->subtotal ?? 0) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                        @if (Route::has('website.order.track'))
                            <a href="{{ route('website.order.track', $order->order_code) }}" class="shop-btn shop-btn-primary flex-grow-1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#c-icon-truck"/></svg>
                                Lacak Pesanan
                            </a>
                        @endif
                        <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-outline flex-grow-1">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        @else
            <div class="shop-card shop-empty anim-scale-in">
                <svg class="shop-empty-icon" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="100" cy="100" r="88" fill="#eef2ff"/>
                    <circle cx="100" cy="100" r="88" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="3 5"/>
                    <path d="M100 62l-30 18v40l30 18 30-18V80z" fill="#ffffff" stroke="#5c73f8" stroke-width="3" stroke-linejoin="round"/>
                    <path d="M70 80l30 18 30-18" stroke="#5c73f8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M100 98v40" stroke="#5c73f8" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <h2 class="shop-empty-title">Pesanan tidak ditemukan</h2>
                <p class="shop-empty-desc">Kode pesanan tidak valid atau sudah kedaluwarsa.</p>
                <a href="{{ route('website.products.index') }}" class="shop-btn shop-btn-primary">Kembali ke Beranda</a>
            </div>
        @endif
    </div>

    <div class="shop-toast" id="shopToast" role="status" aria-live="polite"></div>
@endsection

@push('scripts')
    <script>
        (function () {
            'use strict';

            var toast = document.getElementById('shopToast');

            function showToast(message) {
                toast.textContent = message;
                toast.classList.add('is-visible');
                clearTimeout(showToast._t);
                showToast._t = setTimeout(function () {
                    toast.classList.remove('is-visible');
                }, 2600);
            }

            /* --- Countdown ------------------------------------------- */
            var countdown = document.getElementById('countdown');
            if (countdown) {
                var expiresAt = new Date(countdown.dataset.expires).getTime();
                var cdHours = document.getElementById('cdHours');
                var cdMinutes = document.getElementById('cdMinutes');
                var cdSeconds = document.getElementById('cdSeconds');

                function pad(n) { return n < 10 ? '0' + n : '' + n; }

                function tick() {
                    var diff = expiresAt - Date.now();
                    if (diff <= 0) {
                        cdHours.textContent = '00';
                        cdMinutes.textContent = '00';
                        cdSeconds.textContent = '00';
                        showToast('Waktu pembayaran habis. Silakan buat pesanan baru.');
                        return;
                    }
                    cdHours.textContent = pad(Math.floor(diff / 3600000));
                    cdMinutes.textContent = pad(Math.floor((diff % 3600000) / 60000));
                    cdSeconds.textContent = pad(Math.floor((diff % 60000) / 1000));
                }

                tick();
                setInterval(tick, 1000);
            }

            /* --- Copy to clipboard ----------------------------------- */
            document.querySelectorAll('.js-copy').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var text = btn.dataset.copy || '';
                    function done() { showToast('Nomor berhasil disalin'); }

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(text).then(done, function () {
                            fallbackCopy(text, done);
                        });
                    } else {
                        fallbackCopy(text, done);
                    }
                });
            });

            function fallbackCopy(text, done) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try {
                    document.execCommand('copy');
                    done();
                } catch (e) { /* ignore */ }
                document.body.removeChild(ta);
            }
        })();
    </script>
@endpush
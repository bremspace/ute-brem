@php
    $formatRupiah = fn($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
    $paperWidth = $printer['paper_width_mm'] ?? 80;
@endphp

<div class="receipt-container" style="background: #f4f6f9; min-height: 100vh; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 11px; line-height: 1.2;">
    {{-- Toolbar --}}
    <div class="d-flex justify-content-between align-items-center p-3 bg-white border-bottom shadow-sm">
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('transactions.create') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-plus me-1"></i> Transaksi Baru
            </a>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-muted small">Lebar Kertas: <strong>{{ $paperWidth }}mm</strong></span>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-primary" style="background: #5c73f8; border-color: #5c73f8;">
                <i class="bx bx-printer me-1"></i> Cetak Struk
            </button>
        </div>
    </div>

    {{-- Thermal Paper Simulation --}}
    <div class="d-flex justify-content-center py-4 px-2">
        @if (! $sale)
            <div class="bg-white p-5 rounded shadow-sm border text-center text-muted" style="max-width: 400px;">
                <i class="bx bx-receipt fs-1 mb-2"></i>
                <h5>Transaksi Tidak Ditemukan</h5>
                <p class="small mb-3">Data struk untuk transaksi ini tidak tersedia atau telah dihapus.</p>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-primary" style="background: #5c73f8;">Kembali ke Daftar Transaksi</a>
            </div>
        @else
            <div class="bg-white p-3 rounded shadow-sm border" style="width: {{ $paperWidth }}mm; max-width: 100%; border-style: dashed !important;">
            {{-- Store Header --}}
            <div class="text-center mb-2">
                @if ($printer['show_store_name'])
                    <div class="fw-bold fs-6 text-uppercase">{{ $company['name'] ?? 'UTE PARTS' }}</div>
                @endif
                @if (! empty($company['slogan']))
                    <div class="text-muted small">{{ $company['slogan'] }}</div>
                @endif
                @if (! empty($company['address']))
                    <div class="text-muted" style="font-size: 10px;">{{ $company['address'] }}</div>
                @endif
                @if (! empty($company['city']))
                    <div class="text-muted" style="font-size: 10px;">{{ $company['city'] }}</div>
                @endif
            </div>

            <hr style="border-top: 1px dashed #999; margin: 4px 0;" />

            {{-- Metadata --}}
            <div style="font-size: 10px;" class="mb-2">
                <div class="d-flex justify-content-between">
                    <span>No: {{ $sale->sale_code }}</span>
                    <span>{{ $sale->cashier?->name ?? 'Kasir' }}</span>
                </div>
                @if ($printer['show_datetime'])
                    <div class="d-flex justify-content-between">
                        <span>Waktu:</span>
                        <span>{{ $sale->sale_at?->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                @endif
                @if ($sale->customer)
                    <div class="d-flex justify-content-between">
                        <span>Customer:</span>
                        <span>{{ $sale->customer->name }}</span>
                    </div>
                @endif
            </div>

            <hr style="border-top: 1px dashed #999; margin: 4px 0;" />

            {{-- Items --}}
            <div class="my-2">
                @foreach ($sale->items as $item)
                    <div class="mb-1">
                        <div class="fw-bold">{{ $item->product_name }}</div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                            <span>{{ (float) $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                            <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <hr style="border-top: 1px dashed #999; margin: 4px 0;" />

            {{-- Totals --}}
            <div style="font-size: 10px;" class="my-2">
                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <span>{{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ((float) $sale->discount_total > 0)
                    <div class="d-flex justify-content-between text-danger">
                        <span>Diskon</span>
                        <span>-{{ number_format($sale->discount_total, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between fw-bold fs-6 my-1">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Bayar ({{ ucfirst($sale->payment_method) }})</span>
                    <span>{{ number_format($sale->paid_amount, 0, ',', '.') }}</span>
                </div>
                @if ($sale->payment_method === 'cash')
                    <div class="d-flex justify-content-between">
                        <span>Kembali</span>
                        <span>{{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
            </div>

            <hr style="border-top: 1px dashed #999; margin: 4px 0;" />

            {{-- Footer Text --}}
            <div class="text-center mt-2 text-muted" style="font-size: 10px;">
                @if (! empty($printer['footer_text']))
                    <div>{{ $printer['footer_text'] }}</div>
                @else
                    <div>Terima kasih atas kunjungan Anda.</div>
                    <div>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

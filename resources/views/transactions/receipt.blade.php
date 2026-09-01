<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Struk {{ $sale->sale_code }}</title>

    <style>
        :root {
            --paper-width: {{ (int) ($printer['paper_width_mm'] ?? 80) }}mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 11px;
            line-height: 1.18;
            color: #111827;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 12px 14px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .toolbar .left,
        .toolbar .right {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn {
            appearance: none;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            padding: 8px 10px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            line-height: 1;
        }

        .btn.primary {
            border-color: #2563eb;
            background: #2563eb;
            color: #fff;
        }

        .hint {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
        }

        .paper-wrap {
            display: flex;
            justify-content: center;
            padding: 18px 12px 40px;
        }

        .paper {
            width: var(--paper-width);
            background: #fff;
            border: 1px dashed rgba(17, 24, 39, 0.25);
            border-radius: 10px;
            padding: 9px 10px;
            box-shadow: 0 6px 24px rgba(17, 24, 39, 0.08);
        }

        .center {
            text-align: center;
        }

        .store {
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: 12px;
        }

        .muted {
            opacity: .78;
        }

        .hr {
            border-top: 1px dashed rgba(17, 24, 39, 0.25);
            margin: 5px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .row .k {
            min-width: 50px;
        }

        .items {
            margin-top: 4px;
        }

        .item {
            margin: 4px 0;
        }

        .item .name {
            font-weight: 800;
            margin-bottom: 1px;
        }

        .item .meta {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .item .meta .left {
            opacity: .85;
        }

        .item .meta .right {
            font-weight: 800;
        }

        .totals .row {
            margin: 2px 0;
        }

        .totals .grand {
            font-weight: 900;
            font-size: 11px;
        }

        @media print {
            body {
                background: #fff;
            }

            .toolbar {
                display: none !important;
            }

            .paper-wrap {
                padding: 0;
            }

            .paper {
                width: auto;
                border: none;
                border-radius: 0;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>

@php
    $copies = max(1, (int) ($printer['copies'] ?? 1));
    $headerText = trim((string) ($printer['header_text'] ?? ''));
    $footerText = trim((string) ($printer['footer_text'] ?? ''));
    $showStoreName = (bool) ($printer['show_store_name'] ?? true);
    $showDatetime = (bool) ($printer['show_datetime'] ?? true);
    $autoPrint = (bool) ($printer['auto_print'] ?? false);
    $companyName = trim((string) ($company['name'] ?? 'UTE Parts'));
    $companyAddress = trim((string) ($company['address'] ?? ''));
    $companyRegion = trim(implode(', ', array_filter([
        trim((string) ($company['city'] ?? '')),
        trim((string) ($company['province'] ?? '')),
        trim((string) ($company['country'] ?? '')),
    ])));
    $companySlogan = trim((string) ($company['slogan'] ?? ''));
    $saleChannel = in_array($sale->sale_channel ?? 'toko', ['toko', 'cabang', 'partai'], true) ? $sale->sale_channel : 'toko';
    $saleIndexRoute = route('transactions.index.channel', $saleChannel);
    $isTempo = ($sale->payment_method ?? '') === 'tempo' || in_array($sale->credit_status ?? 'paid', ['unpaid', 'partial'], true);
    $outstandingAmount = max(0, (float) $sale->grand_total - (float) $sale->paid_amount);
@endphp

<body>
    @if (empty($embed))
        <div class="toolbar">
            <div class="left">
                <a class="btn" href="{{ route('transactions.show', $sale) }}">Kembali</a>
                <a class="btn" href="{{ $saleIndexRoute }}">Daftar Transaksi</a>
            </div>
            <div class="right">
                <span class="hint">Struk: {{ $sale->sale_code }}</span>
                <button type="button" class="btn primary" onclick="window.print()">Print</button>
            </div>
        </div>
    @endif

    <div class="paper-wrap">
        <div>
            @for ($i = 0; $i < $copies; $i++)
                <div class="paper">
                    @if ($showStoreName)
                        <div class="center store">{{ $companyName !== '' ? $companyName : 'UTE Parts' }}</div>
                        @if ($companyAddress !== '')
                            <div class="center muted" style="white-space: pre-line;">{{ $companyAddress }}</div>
                        @endif
                        @if ($companyRegion !== '')
                            <div class="center muted">{{ $companyRegion }}</div>
                        @endif
                        @if ($companySlogan !== '')
                            <div class="center muted">{{ $companySlogan }}</div>
                        @endif
                    @endif

                    @if ($headerText !== '')
                        <div class="center muted" style="white-space: pre-line;">{{ $headerText }}</div>
                    @endif

                    <div class="hr"></div>

                    <div class="row">
                        <div class="k muted">TRX</div>
                        <div class="v">{{ $sale->sale_code }}</div>
                    </div>
                    @if ($showDatetime)
                        <div class="row muted">
                            <div class="k">Tanggal</div>
                            <div class="v">{{ optional($sale->sale_at)->format('d/m/Y H:i') ?: '-' }}</div>
                        </div>
                    @endif
                    <div class="muted">Kasir: {{ $sale->cashier?->name ?: '-' }}</div>
                    <div class="muted">Pelanggan: {{ $sale->customer?->name ?: '-' }}</div>

                    <div class="hr"></div>

                    <div class="items">
                        @foreach ($sale->items as $item)
                            <div class="item">
                                <div class="name">{{ $item->product_name }}</div>
                                <div class="meta">
                                    <div class="left">
                                        {{ rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',') }}
                                        {{ $item->unit_name ?: '' }}
                                        x {{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                        @if ((float) $item->discount_value > 0)
                                            <span class="muted">(Disc {{ number_format((float) $item->discount_value, 0, ',', '.') }})</span>
                                        @endif
                                    </div>
                                    <div class="right">{{ number_format((float) $item->subtotal, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hr"></div>

                    <div class="totals">
                        <div class="row">
                            <div class="muted">Subtotal</div>
                            <div>{{ number_format((float) $sale->subtotal, 0, ',', '.') }}</div>
                        </div>
                        <div class="row">
                            <div class="muted">Disc</div>
                            <div>{{ number_format((float) $sale->discount_total, 0, ',', '.') }}</div>
                        </div>
                        <div class="row grand">
                            <div>Grand Total</div>
                            <div>{{ number_format((float) $sale->grand_total, 0, ',', '.') }}</div>
                        </div>
                        <div class="row">
                            <div class="muted">Bayar</div>
                            <div>{{ number_format((float) $sale->paid_amount, 0, ',', '.') }}</div>
                        </div>
                        <div class="row">
                            <div class="muted">{{ $isTempo ? 'Sisa Tempo' : 'Kembali' }}</div>
                            <div>{{ number_format($isTempo ? $outstandingAmount : (float) $sale->change_amount, 0, ',', '.') }}</div>
                        </div>
                        @if($isTempo)
                            <div class="row">
                                <div class="muted">Jatuh Tempo</div>
                                <div>{{ optional($sale->credit_due_at)->format('d/m/Y') ?: '-' }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="hr"></div>

                    @if ($footerText !== '')
                        <div class="center muted" style="white-space: pre-line;">{{ $footerText }}</div>
                    @endif
                </div>

                @if ($i < $copies - 1)
                    <div style="height: 18px;"></div>
                @endif
            @endfor
        </div>
    </div>

    <script>
        (function() {
            const autoPrint = {{ $autoPrint ? 'true' : 'false' }};
            if (!autoPrint) return;
            // Let layout settle then print.
            window.addEventListener('load', () => setTimeout(() => {
                try { window.print(); } catch (e) {}
            }, 350));
        })();
    </script>
</body>

</html>

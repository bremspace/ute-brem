<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Test Print Struk</title>

    <style>
        :root {
            --paper-width: {{ (int) ($printer['paper_width_mm'] ?? 80) }}mm;
        }

        body {
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
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
            padding: 12px 12px;
            box-shadow: 0 6px 24px rgba(17, 24, 39, 0.08);
        }

        .center {
            text-align: center;
        }

        .store {
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .muted {
            opacity: .78;
        }

        .hr {
            border-top: 1px dashed rgba(17, 24, 39, 0.25);
            margin: 8px 0;
        }

        .line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .items {
            margin-top: 6px;
        }

        .item {
            margin: 6px 0;
        }

        .item .name {
            font-weight: 800;
            margin-bottom: 2px;
        }

        .item .meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .item .meta .left {
            opacity: .85;
        }

        .item .meta .right {
            font-weight: 800;
        }

        .totals .line {
            margin: 4px 0;
        }

        .totals .grand {
            font-weight: 900;
            font-size: 14px;
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

    $trxPrefix = \App\Http\Controllers\PrinterSettingController::referencePrefix('store_sale', 'TEST');
    $trxCode = $trxPrefix . '-' . now()->format('ymdHis');

    $items = [
        ['name' => 'Barang Test A', 'qty' => 1, 'price' => 10000],
        ['name' => 'Barang Test B', 'qty' => 2, 'price' => 5000],
    ];
    $subtotal = collect($items)->sum(fn ($row) => (float) $row['qty'] * (float) $row['price']);
    $discount = 0;
    $grandTotal = $subtotal - $discount;
    $paid = $grandTotal;
    $change = 0;
@endphp

<body>
    <div class="toolbar">
        <div class="left">
            <a class="btn" href="{{ route('settings.printer.edit', ['tab' => 'printer']) }}">Kembali</a>
        </div>
        <div class="right">
            <span class="hint">{{ $trxCode }}</span>
            <button type="button" class="btn primary" onclick="window.print()">Print</button>
        </div>
    </div>

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

                    <div class="line">
                        <div class="muted">TRX</div>
                        <div>{{ $trxCode }}</div>
                    </div>
                    @if ($showDatetime)
                        <div class="line muted">
                            <div>Tanggal</div>
                            <div>{{ now()->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                    <div class="muted">Kasir: {{ $cashier_name }}</div>
                    <div class="muted">Pelanggan: {{ $customer_name }}</div>

                    <div class="hr"></div>

                    <div class="items">
                        @foreach ($items as $item)
                            @php $lineSubtotal = (float) $item['qty'] * (float) $item['price']; @endphp
                            <div class="item">
                                <div class="name">{{ $item['name'] }}</div>
                                <div class="meta">
                                    <div class="left">
                                        {{ (int) $item['qty'] }} x {{ number_format((float) $item['price'], 0, ',', '.') }}
                                    </div>
                                    <div class="right">{{ number_format((float) $lineSubtotal, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="hr"></div>

                    <div class="totals">
                        <div class="line">
                            <div class="muted">Subtotal</div>
                            <div>{{ number_format((float) $subtotal, 0, ',', '.') }}</div>
                        </div>
                        <div class="line">
                            <div class="muted">Disc</div>
                            <div>{{ number_format((float) $discount, 0, ',', '.') }}</div>
                        </div>
                        <div class="line grand">
                            <div>Grand Total</div>
                            <div>{{ number_format((float) $grandTotal, 0, ',', '.') }}</div>
                        </div>
                        <div class="line">
                            <div class="muted">Bayar</div>
                            <div>{{ number_format((float) $paid, 0, ',', '.') }}</div>
                        </div>
                        <div class="line">
                            <div class="muted">Kembali</div>
                            <div>{{ number_format((float) $change, 0, ',', '.') }}</div>
                        </div>
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
            window.addEventListener('load', () => setTimeout(() => {
                try { window.print(); } catch (e) {}
            }, 350));
        })();
    </script>
</body>

</html>

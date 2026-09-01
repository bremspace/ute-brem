<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode Produk</title>
    <style>
        :root {
            --label-width: {{ $widthMm }}mm;
            --label-height: {{ $heightMm }}mm;
            --sheet-gap: 2.5mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 14px;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            padding: 12px 14px;
            border: 1px solid #d9dee3;
            border-radius: 12px;
            background: #fff;
        }

        .toolbar h1 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        .toolbar p {
            margin: 0;
            font-size: 13px;
            color: #6b7280;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            border: 1px solid #d9dee3;
            background: #fff;
            color: #1f2937;
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
        }

        .btn-primary {
            border-color: #696cff;
            background: #696cff;
            color: #fff;
        }

        .sheet {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sheet-gap);
            align-items: flex-start;
        }

        .barcode-label {
            width: var(--label-width);
            height: var(--label-height);
            background: #fff;
            border: 1px solid #d9dee3;
            padding: 2mm 2.2mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .barcode-product-code {
            font-size: 8.5pt;
            font-weight: 700;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: center;
        }

        .barcode-product-name {
            font-size: 7.4pt;
            line-height: 1.15;
            text-align: center;
            min-height: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .barcode-svg-wrap {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 26px;
        }

        .barcode-svg {
            width: 100%;
            height: auto;
        }

        .barcode-text {
            text-align: center;
            font-size: 8pt;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barcode-meta {
            text-align: center;
            font-size: 6.5pt;
            color: #6b7280;
        }

        @page {
            margin: 8mm;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .sheet {
                gap: 2mm;
            }

            .barcode-label {
                border-color: #c9ced6;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <div>
            <h1>Cetak Barcode Produk</h1>
            <p>{{ $items->count() }} label · {{ $barcodeScope === 'all' ? 'Semua barcode / satuan' : 'Barcode utama saja' }} · {{ $sizeLabel }}</p>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn" onclick="window.close()">Tutup</button>
            <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
        </div>
    </div>

    <div class="sheet">
        @foreach ($items as $item)
            <div class="barcode-label">
                <div class="barcode-product-code">{{ $item['product_code'] }}</div>
                <div class="barcode-product-name">{{ $item['product_name'] }}</div>
                <div class="barcode-svg-wrap">
                    <svg class="barcode-svg" jsbarcode-format="CODE128" jsbarcode-value="{{ $item['barcode'] }}"
                        jsbarcode-textmargin="0" jsbarcode-fontsize="14" jsbarcode-height="34"
                        jsbarcode-margin="0"></svg>
                </div>
                <div class="barcode-text">{{ $item['barcode'] }}</div>
            </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script>
        (function() {
            function renderAndPrint() {
                if (typeof JsBarcode === 'undefined') {
                    return;
                }

                try {
                    JsBarcode('.barcode-svg').init();
                } catch (e) {
                    console.error('Gagal render barcode', e);
                }

                window.setTimeout(function() {
                    try {
                        window.print();
                    } catch (e) {}
                }, 300);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', renderAndPrint);
                return;
            }

            renderAndPrint();
        })();
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $branchTransfer->transfer_code || 'Surat Jalan' }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {{:
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif {{,
            font-size: 13px {{,
            color: #1f2937 {{,
            background-color: #fff {{,
            padding: 20px {{,
        }}}
        .header-title {{:
            font-size: 20px {{,
            font-weight: 700 {{,
            text-transform: uppercase {{,
            letter-spacing: 1px {{,
        }}
        .table th {{:
            background-color: #f3f4f6 !important {{,
            color: #111827 !important {{,
        }}
        .signature-section {{:
            margin-top: 60px {{,
        }}
        .signature-box {{:
            text-align: center {{,
            height: 120px {{,
            display: flex {{,
            flex-direction: column {{,
            justify-content: space-between {{,
        }}
        @media print {{:
            .no-print {{:
                display: none !important {{,
            }}
            body {{
                padding: 0 {{,
            }}
        }}
    </style>
    @stack('print_styles')
</head>
<body>
    @vite(['resources/js/app.js'])

    @unless($branchTransfer)
        <div class="alert alert-warning">
            <i class="bx bx-error-circle me-1"></i> Data transfer tidak ditemukan.
        </div>
    @endunless

    <div class="container">
        @if($branchTransfer)
            <!-- Print Header -->
            <div class="row align-items-center mb-5">
                <div class="col-8">
                    <div class="header-title">SURAT JALAN (TRANSFER STOK)</div>
                    <div class="text-muted">UTE PARTS</div>
                </div>
                <div class="col-4 text-end no-print @livewireSuppress">
                    <a href="{{ route('branch-transfers.index', ['id' => $branchTransfer->id ?? null]) }}"
                       class="btn btn-outline-secondary me-2">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                    <button onclick="window.print();" class="btn btn-primary @livewireSuppress">
                        <i class="bx bx-printer me-1"></i> Cetak
                    </button>
                </div>
            </div>

            <hr class="my-5">

            <!-- Metadata -->
            <div class="row mb-5">
                <div class="col-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td style="width: 40%;">Nomor Dokumen</td>
                            <td style="width: 5%;">:</td>
                            <td><strong>{{ $branchTransfer->transfer_code }}</strong></td>
                        </tr>
                        <tr>
                            <td>Cabang Asal</td>
                            <td>:</td>
                            <td>{{ $branchTransfer->sourceBranch?->name ?? '-' }} ({{ $branchTransfer->sourceLocation?->name }})</td>
                        </tr>
                        <tr>
                            <td>Cabang Tujuan</td>
                            <td>:</td>
                            <td>{{ $branchTransfer->targetBranch?->name ?? '-' }} ({{ $branchTransfer->targetLocation?->name }})</td>
                        </tr>
                    </table>
                </div>
                <div class="col-6">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td style="width: 40%;">Tanggal Pembuatan</td>
                            <td style="width: 5%;">:</td>
                            <td>{{ $branchTransfer->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal Pengiriman</td>
                            <td>:</td>
                            <td>{{ $branchTransfer->sent_at ? $branchTransfer->sent_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>:</td>
                            <td class="text-uppercase"><strong>{{ $branchTransfer->status }}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Items Table -->
            <div class="row mb-5">
                <div class="col-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 20%;">Kode Produk</th>
                                <th style="width: 50%;">Nama Produk</th>
                                <th style="width: 15%;" class="text-end">Jumlah Kirim</th>
                                <th style="width: 10%;">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branchTransfer->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product?->product_code }}</td>
                                    <td>{{ $item->product?->name }}</td>
                                    <td class="text-end">{{ rtrim(rtrim(number_format((float) $item->quantity_sent, 2, ',', '.'), '0'), ',') }}</td>
                                    <td><small>{{ $item->notes ?: '-' }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($branchTransfer->notes)
                <div class="row mb-5">
                    <div class="col-12">
                        <strong>Catatan:</strong>
                        <p class="p-3 border bg-gray-50 rounded small mt-1 font-normal">
                            {{ $branchTransfer->notes }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Signatures -->
            <div class="row signature-section">
                <div class="col-4">
                    <div class="signature-box">
                        <div>Pengirim (Cabang Asal),</div>
                        <div class="border-top pt-2 text-muted mt-2">@php
                            // Placeholder for signature logic
                            if ($branchTransfer->creator) {
                                echo htmlspecialchars($branchTransfer->creator->name);
                            } else {
                                echo '.......................';
                            }
                        @endphp</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="signature-box">
                        <div>Sopir / Kurir,</div>
                        <div class="border-top pt-2 text-muted mt-2">...................................</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="signature-box">
                        <div>Penerima (Cabang Tujuan),</div>
                        <div class="border-top pt-2 text-muted mt-2">@php
                            if ($branchTransfer->recipient) {
                                echo htmlspecialchars($branchTransfer->recipient->name);
                            } else {
                                echo '.......................';
                            }
                        @endphp</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
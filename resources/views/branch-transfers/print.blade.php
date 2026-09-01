<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $branchTransfer->transfer_code }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            background-color: #fff;
            padding: 20px;
        }
        .header-title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .table th {
            background-color: #f2f2f2 !important;
            color: #000 !important;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-box {
            text-align: center;
            height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Print Header -->
        <div class="row align-items-center mb-4">
            <div class="col-8">
                <div class="header-title">SURAT JALAN (TRANSFER STOK)</div>
                <div class="text-muted">UTE PARTS</div>
            </div>
            <div class="col-4 text-end no-print">
                <button onclick="window.print();" class="btn btn-primary btn-sm me-2">
                    Cetak Halaman
                </button>
                <button onclick="window.close();" class="btn btn-outline-secondary btn-sm">
                    Tutup
                </button>
            </div>
        </div>

        <hr>

        <!-- Metadata -->
        <div class="row mb-4">
            <div class="col-6">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td style="width: 30%;">No. Dokumen</td>
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
                        <td style="width: 35%;">Tanggal Buat</td>
                        <td style="width: 5%;">:</td>
                        <td>{{ $branchTransfer->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Kirim</td>
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
        <div class="row mb-4">
            <div class="col-12">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 20%;">Kode Produk</th>
                            <th style="width: 45%;">Nama Produk</th>
                            <th style="width: 15%;" class="text-end">Jumlah Kirim</th>
                            <th style="width: 15%;">Catatan</th>
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
            <div class="row mb-4">
                <div class="col-12">
                    <strong>Catatan:</strong>
                    <p class="p-2 border bg-light small rounded mt-1">{{ $branchTransfer->notes }}</p>
                </div>
            </div>
        @endif

        <!-- Signatures -->
        <div class="row signature-section">
            <div class="col-4">
                <div class="signature-box">
                    <div>Pengirim (Cabang Asal),</div>
                    <div class="border-top pt-1 text-muted">{{ $branchTransfer->creator?->name ?? '.......................' }}</div>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-box">
                    <div>Sopir / Kurir,</div>
                    <div class="border-top pt-1 text-muted">...................................</div>
                </div>
            </div>
            <div class="col-4">
                <div class="signature-box">
                    <div>Penerima (Cabang Tujuan),</div>
                    <div class="border-top pt-1 text-muted">{{ $branchTransfer->recipient?->name ?? '.......................' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

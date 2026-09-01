@extends('layouts.sneat')

@section('title', 'Import Produk')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">File import belum valid.</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">Import Produk</h4>
                <div class="text-muted">Upload Excel, cek preview, lalu simpan produk.</div>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Produk
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Template</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Download template Excel, isi data produk, lalu upload kembali untuk preview.</p>
                        <a href="{{ route('products.import.template') }}" class="btn btn-outline-primary w-100 mb-3">
                            <i class="bx bx-download me-1"></i> Download Template Excel
                        </a>
                        <div class="alert alert-label-info mb-0">
                            Kolom wajib: <strong>nama</strong>, <strong>kategori</strong>, dan <strong>harga_jual</strong>.
                            Jika <strong>stok_awal</strong> diisi lebih dari 0, <strong>lokasi_default</strong> wajib diisi.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Upload File</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('products.import.preview') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">File Excel <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                <div class="form-text">Gunakan template Excel dari sistem. Format .xlsx atau .xls, maksimal 5 MB.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search-alt me-1"></i> Preview Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($preview)
            @php
                $editableFields = [
                    'product_code' => 'Kode',
                    'nama' => 'Nama',
                    'kategori' => 'Kategori',
                    'sub_kategori' => 'Sub Kategori',
                    'brand' => 'Brand',
                    'merek' => 'Merek',
                    'tipe_hp' => 'Tipe HP',
                    'supplier' => 'Supplier',
                    'lokasi_default' => 'Lokasi',
                    'quality' => 'Quality',
                    'barcode' => 'Barcode',
                    'sku_internal' => 'SKU',
                    'satuan_beli' => 'Sat. Beli',
                    'satuan_jual' => 'Sat. Jual',
                    'harga_beli' => 'Harga Beli',
                    'harga_jual' => 'Harga Jual',
                    'stok_awal' => 'Stok Awal',
                    'stok_min' => 'Stok Min',
                    'stok_max' => 'Stok Max',
                    'rak' => 'Rak',
                    'aktif' => 'Aktif',
                    'member_only' => 'Member Only',
                ];
                $numberFields = ['harga_beli', 'harga_jual', 'stok_awal', 'stok_min', 'stok_max'];
                $booleanFields = ['aktif', 'member_only'];
            @endphp
            <div class="card mt-4">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-1">Preview Import</h5>
                        <div class="text-muted small">Dipreview: {{ $preview['previewed_at'] }}. Data bisa diedit lalu update preview sebelum disimpan.</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-label-secondary">Total: {{ $preview['total'] }}</span>
                        <span class="badge bg-label-success">Valid: {{ $preview['valid'] }}</span>
                        <span class="badge bg-label-danger">Error: {{ $preview['invalid'] }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Field Asal</label>
                            <select class="form-select" id="swapSourceField">
                                @foreach ($editableFields as $field => $label)
                                    <option value="{{ $field }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Field Tujuan</label>
                            <select class="form-select" id="swapTargetField">
                                @foreach ($editableFields as $field => $label)
                                    <option value="{{ $field }}" @selected($field === 'kategori')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary w-100" id="swapFieldsBtn">
                                <i class="bx bx-transfer me-1"></i> Tukar
                            </button>
                            <button type="button" class="btn btn-outline-primary w-100" id="moveFieldBtn">
                                <i class="bx bx-right-arrow-alt me-1"></i> Pindah
                            </button>
                        </div>
                        <div class="col-12">
                            <div class="form-text">
                                Tukar menukar isi dua field untuk semua baris. Pindah mengisi field tujuan dari field asal lalu mengosongkan field asal.
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('products.import.preview.update') }}" method="POST" id="productImportPreviewForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap">Baris</th>
                                        <th>Status</th>
                                        @foreach ($editableFields as $label)
                                            <th class="text-nowrap">{{ $label }}</th>
                                        @endforeach
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($preview['rows'] as $rowIndex => $row)
                                        <tr>
                                            <td class="fw-semibold">
                                                {{ $row['row_number'] }}
                                                <input type="hidden" name="rows[{{ $rowIndex }}][row_number]" value="{{ $row['row_number'] }}">
                                            </td>
                                            <td>
                                                @if ($row['status'] === 'valid')
                                                    <span class="badge bg-label-success">Valid</span>
                                                @else
                                                    <span class="badge bg-label-danger">Error</span>
                                                @endif
                                            </td>
                                            @foreach ($editableFields as $field => $label)
                                                <td style="min-width: {{ in_array($field, $numberFields, true) ? '130px' : '170px' }};">
                                                    @if (in_array($field, $booleanFields, true))
                                                        <select name="rows[{{ $rowIndex }}][data][{{ $field }}]" class="form-select form-select-sm js-import-field" data-import-field="{{ $field }}">
                                                            <option value="ya" @selected((bool) ($row['data'][$field] ?? false))>Ya</option>
                                                            <option value="tidak" @selected(! (bool) ($row['data'][$field] ?? false))>Tidak</option>
                                                        </select>
                                                    @else
                                                        <input type="text"
                                                            name="rows[{{ $rowIndex }}][data][{{ $field }}]"
                                                            class="form-control form-control-sm js-import-field"
                                                            data-import-field="{{ $field }}"
                                                            value="{{ $row['data'][$field] ?? '' }}"
                                                            placeholder="{{ $label }}">
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td style="min-width: 240px;">
                                                @foreach ($row['errors'] as $error)
                                                    <div class="text-danger small"><i class="bx bx-error-circle me-1"></i>{{ $error }}</div>
                                                @endforeach
                                                @foreach ($row['notes'] as $note)
                                                    <div class="text-muted small"><i class="bx bx-info-circle me-1"></i>{{ $note }}</div>
                                                @endforeach
                                                @if (empty($row['errors']) && empty($row['notes']))
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4">
                        <button type="submit" form="productImportPreviewForm" class="btn btn-outline-primary">
                            <i class="bx bx-refresh me-1"></i> Update Preview
                        </button>
                        <form action="{{ route('products.import.store') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success" {{ $preview['has_errors'] ? 'disabled' : '' }} onclick="return confirm('Import {{ $preview['valid'] }} produk sekarang?')">
                                <i class="bx bx-check me-1"></i> Simpan Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const source = document.getElementById('swapSourceField');
            const target = document.getElementById('swapTargetField');
            const swapBtn = document.getElementById('swapFieldsBtn');
            const moveBtn = document.getElementById('moveFieldBtn');

            function controls(field) {
                return Array.from(document.querySelectorAll(`.js-import-field[data-import-field="${field}"]`));
            }

            function getValue(control) {
                return control ? control.value : '';
            }

            function setValue(control, value) {
                if (!control) return;
                control.value = value;
                control.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function emptyValue(control) {
                if (!control) return '';
                return control.tagName === 'SELECT' ? 'tidak' : '';
            }

            function selectedFields() {
                const from = source?.value || '';
                const to = target?.value || '';
                if (!from || !to || from === to) {
                    alert('Pilih dua field yang berbeda.');
                    return null;
                }
                return { from, to };
            }

            swapBtn?.addEventListener('click', function() {
                const fields = selectedFields();
                if (!fields) return;

                const fromControls = controls(fields.from);
                const toControls = controls(fields.to);
                const max = Math.max(fromControls.length, toControls.length);

                for (let i = 0; i < max; i++) {
                    const fromControl = fromControls[i];
                    const toControl = toControls[i];
                    const fromValue = getValue(fromControl);
                    const toValue = getValue(toControl);
                    setValue(fromControl, toValue);
                    setValue(toControl, fromValue);
                }
            });

            moveBtn?.addEventListener('click', function() {
                const fields = selectedFields();
                if (!fields) return;

                if (!confirm('Pindahkan isi field asal ke field tujuan untuk semua baris?')) {
                    return;
                }

                const fromControls = controls(fields.from);
                const toControls = controls(fields.to);
                const max = Math.max(fromControls.length, toControls.length);

                for (let i = 0; i < max; i++) {
                    const fromControl = fromControls[i];
                    const toControl = toControls[i];
                    setValue(toControl, getValue(fromControl));
                    setValue(fromControl, emptyValue(fromControl));
                }
            });
        });
    </script>
@endpush

@extends('layouts.sneat')

@section('title', 'Buat Transfer Stok Cabang')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Buat Transfer Stok Cabang</h4>
            <div class="text-muted">Buat draf pengiriman stok antar cabang/toko.</div>
        </div>
        <a href="{{ route('branch-transfers.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <form id="transfer-form" action="{{ route('branch-transfers.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Header Info -->
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Source Branch & Location -->
                            <div class="col-md-3">
                                <label class="form-label" for="source_branch_id">Cabang Asal <span class="text-danger">*</span></label>
                                <select name="source_branch_id" id="source_branch_id" class="form-select" required {{ $userBranchId ? 'disabled' : '' }}>
                                    <option value="">Pilih Cabang Asal</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ ($userBranchId == $branch->id || old('source_branch_id') == $branch->id) ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($userBranchId)
                                    <input type="hidden" name="source_branch_id" value="{{ $userBranchId }}">
                                @endif
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="source_location_id">Lokasi Asal <span class="text-danger">*</span></label>
                                <select name="source_location_id" id="source_location_id" class="form-select" required>
                                    <option value="">Pilih Lokasi Asal</option>
                                </select>
                            </div>

                            <!-- Target Branch & Location -->
                            <div class="col-md-3">
                                <label class="form-label" for="target_branch_id">Cabang Tujuan <span class="text-danger">*</span></label>
                                <select name="target_branch_id" id="target_branch_id" class="form-select" required>
                                    <option value="">Pilih Cabang Tujuan</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('target_branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="target_location_id">Lokasi Tujuan <span class="text-danger">*</span></label>
                                <select name="target_location_id" id="target_location_id" class="form-select" required>
                                    <option value="">Pilih Lokasi Tujuan</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="notes">Catatan Pengiriman</label>
                                <textarea name="notes" id="notes" rows="2" class="form-control" placeholder="Contoh: Stok mingguan cabang, kirim via kurir">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daftar Produk</h5>
                    </div>
                    <div class="card-body">
                        <!-- Add Product Panel -->
                        <div class="row g-3 mb-4 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label" for="product_lookup">Cari Produk (Pilih dari daftar)</label>
                                <select id="product_lookup" class="form-select select2-basic">
                                    <option value="">Cari berdasarkan nama atau kode...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-code="{{ $product->product_code }}" data-name="{{ $product->name }}">
                                            [{{ $product->product_code }}] {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="button" id="btn-add-item" class="btn btn-primary w-100">
                                    <i class="bx bx-plus me-1"></i> Tambah ke Tabel
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive text-nowrap">
                            <table class="table table-bordered align-middle" id="items-table">
                                <thead>
                                    <tr>
                                        <th style="width: 35%;">Produk</th>
                                        <th style="width: 20%;">Rak Asal</th>
                                        <th style="width: 20%;">Rak Tujuan</th>
                                        <th style="width: 15%;">Jumlah Kirim</th>
                                        <th style="width: 15%;">Catatan</th>
                                        <th style="width: 5%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="items-container">
                                    <tr id="empty-row">
                                        <td colspan="6" class="text-center text-muted py-4">Belum ada produk yang ditambahkan. Silakan pilih produk di atas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="bx bx-save me-1"></i> Simpan Draf
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .btn-remove-row {
        padding: 0.25rem 0.5rem;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Embedded Branches & Locations & Racks Data
    const branches = @json($branches);
    let rowIdx = 0;

    document.addEventListener('DOMContentLoaded', function () {
        $('.select2-basic').select2({
            width: '100%'
        });

        const sourceBranchSelect = document.getElementById('source_branch_id');
        const sourceLocationSelect = document.getElementById('source_location_id');
        const targetBranchSelect = document.getElementById('target_branch_id');
        const targetLocationSelect = document.getElementById('target_location_id');
        const productLookup = document.getElementById('product_lookup');
        const btnAddItem = document.getElementById('btn-add-item');
        const itemsContainer = document.getElementById('items-container');
        const emptyRow = document.getElementById('empty-row');
        const form = document.getElementById('transfer-form');

        // Function to update locations dropdown when branch changes
        function updateLocationsForBranch(type, branchId, selectedLocationId = null) {
            const selectEl = document.getElementById(`${type}_location_id`);
            selectEl.innerHTML = `<option value="">Pilih Lokasi ${type === 'source' ? 'Asal' : 'Tujuan'}</option>`;
            
            if (!branchId) return;

            const branch = branches.find(b => b.id == branchId);
            if (branch && branch.locations) {
                branch.locations.forEach(loc => {
                    const opt = document.createElement('option');
                    opt.value = loc.id;
                    opt.textContent = `${loc.name} (${loc.code})`;
                    if (selectedLocationId == loc.id) {
                        opt.selected = true;
                    }
                    selectEl.appendChild(opt);
                });
            }
        }

        // On branch change, update location dropdown
        sourceBranchSelect.addEventListener('change', function () {
            updateLocationsForBranch('source', this.value);
            updateAllRacksForLocation('source', '');
            validateSameBranches();
        });

        targetBranchSelect.addEventListener('change', function () {
            updateLocationsForBranch('target', this.value);
            updateAllRacksForLocation('target', '');
            validateSameBranches();
        });

        // On location change, update rack dropdowns in table
        sourceLocationSelect.addEventListener('change', function () {
            updateAllRacksForLocation('source', this.value);
        });

        targetLocationSelect.addEventListener('change', function () {
            updateAllRacksForLocation('target', this.value);
        });

        function validateSameBranches() {
            if (sourceBranchSelect.value && targetBranchSelect.value && sourceBranchSelect.value === targetBranchSelect.value) {
                alert('Cabang asal dan cabang tujuan tidak boleh sama.');
                targetBranchSelect.value = '';
                updateLocationsForBranch('target', '');
                updateAllRacksForLocation('target', '');
            }
        }

        // Initialize locations for pre-selected branches
        if (sourceBranchSelect.value) {
            updateLocationsForBranch('source', sourceBranchSelect.value, "{{ old('source_location_id') }}");
            if (sourceLocationSelect.value || "{{ old('source_location_id') }}") {
                updateAllRacksForLocation('source', sourceLocationSelect.value || "{{ old('source_location_id') }}");
            }
        }
        if (targetBranchSelect.value) {
            updateLocationsForBranch('target', targetBranchSelect.value, "{{ old('target_location_id') }}");
            if (targetLocationSelect.value || "{{ old('target_location_id') }}") {
                updateAllRacksForLocation('target', targetLocationSelect.value || "{{ old('target_location_id') }}");
            }
        }

        // Helpers to find location/racks
        function findLocationRacks(locationId) {
            for (let b of branches) {
                if (b.locations) {
                    let loc = b.locations.find(l => l.id == locationId);
                    if (loc) return loc.racks || [];
                }
            }
            return [];
        }

        // Add Product Event
        btnAddItem.addEventListener('click', function () {
            const productId = productLookup.value;
            if (!productId) {
                alert('Silakan pilih produk terlebih dahulu.');
                return;
            }

            const selectedOption = productLookup.options[productLookup.selectedIndex];
            const pCode = selectedOption.getAttribute('data-code');
            const pName = selectedOption.getAttribute('data-name');

            // Prevent duplicates
            const existingRow = document.querySelector(`.item-row[data-product-id="${productId}"]`);
            if (existingRow) {
                alert('Produk tersebut sudah ditambahkan ke tabel.');
                return;
            }

            // Remove empty row text
            if (emptyRow) {
                emptyRow.style.display = 'none';
            }

            addRow(productId, pCode, pName);
            
            // Reset select lookup
            $(productLookup).val('').trigger('change');
        });

        function addRow(productId, code, name) {
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.setAttribute('data-product-id', productId);
            tr.setAttribute('id', `row-${rowIdx}`);

            // Product column
            let tdProduct = `
                <td>
                    <input type="hidden" name="items[${rowIdx}][product_id]" value="${productId}">
                    <span class="fw-semibold">[${code}]</span> ${name}
                </td>
            `;

            // Source Rack
            let tdSourceRack = `
                <td>
                    <select name="items[${rowIdx}][source_location_rack_id]" class="form-select form-select-sm source-rack-select">
                        <option value="">Pilih Rak</option>
                    </select>
                </td>
            `;

            // Target Rack
            let tdTargetRack = `
                <td>
                    <select name="items[${rowIdx}][target_location_rack_id]" class="form-select form-select-sm target-rack-select">
                        <option value="">Pilih Rak</option>
                    </select>
                </td>
            `;

            // Quantity
            let tdQty = `
                <td>
                    <input type="number" step="0.01" min="0.01" name="items[${rowIdx}][quantity_sent]" class="form-control form-control-sm" placeholder="Qty" required>
                </td>
            `;

            // Notes
            let tdNotes = `
                <td>
                    <input type="text" name="items[${rowIdx}][notes]" class="form-control form-control-sm" placeholder="Catatan opsional">
                </td>
            `;

            // Remove Button
            let tdAction = `
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-remove-row btn-sm" onclick="removeRow(${rowIdx})">
                        <i class="bx bx-trash"></i>
                    </button>
                </td>
            `;

            tr.innerHTML = tdProduct + tdSourceRack + tdTargetRack + tdQty + tdNotes + tdAction;
            itemsContainer.appendChild(tr);

            // Populate racks for this new row based on current selected locations
            populateRowRacks(rowIdx, 'source', sourceLocationSelect.value);
            populateRowRacks(rowIdx, 'target', targetLocationSelect.value);

            rowIdx++;
        }

        // Helper to update rack selects for a newly added row
        function populateRowRacks(rowId, type, locationId) {
            const row = document.getElementById(`row-${rowId}`);
            if (!row) return;

            const select = row.querySelector(`.${type}-rack-select`);
            select.innerHTML = '<option value="">Pilih Rak</option>';

            if (!locationId) return;

            const racks = findLocationRacks(locationId);
            racks.forEach(rack => {
                const opt = document.createElement('option');
                opt.value = rack.id;
                opt.textContent = rack.name;
                select.appendChild(opt);
            });
        }

        // Helper to update all rows for source or target rack selects
        function updateAllRacksForLocation(type, locationId) {
            const selects = document.querySelectorAll(`.${type}-rack-select`);
            selects.forEach(select => {
                select.innerHTML = '<option value="">Pilih Rak</option>';
                if (!locationId) return;

                const racks = findLocationRacks(locationId);
                racks.forEach(rack => {
                    const opt = document.createElement('option');
                    opt.value = rack.id;
                    opt.textContent = rack.name;
                    select.appendChild(opt);
                });
            });
        }

        // Form Validation on submit
        form.addEventListener('submit', function (e) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length === 0) {
                e.preventDefault();
                alert('Tabel produk kosong. Harap tambahkan minimal satu produk.');
            }
        });
    });

    // Global function to remove row
    function removeRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (row) {
            row.remove();
        }

        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 0) {
            const emptyRow = document.getElementById('empty-row');
            if (emptyRow) {
                emptyRow.style.display = 'table-row';
            }
        }
    }
</script>
@endpush

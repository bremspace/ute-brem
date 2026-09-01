@php
    $selectedRacks = collect(old('racks', $location?->racks?->where('is_active', true)->pluck('name')->all() ?? []))
        ->map(fn ($name) => trim((string) $name))
        ->filter()
        ->values();
@endphp

<div class="mb-3">
    <label class="form-label">Nama Lokasi</label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $location?->name) }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Kode</label>
    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
        value="{{ old('code', $location?->code) }}" required>
    @error('code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if(isset($branches))
<div class="mb-3">
    <label class="form-label">Cabang Toko <span class="text-danger">*</span></label>
    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
        <option value="">Pilih Cabang</option>
        @foreach($branches as $branch)
            <option value="{{ $branch->id }}" {{ old('branch_id', $location?->branch_id) == $branch->id ? 'selected' : '' }}>
                {{ $branch->name }} ({{ $branch->code }})
            </option>
        @endforeach
    </select>
    @error('branch_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
@endif

<div class="mb-3">
    <label class="form-label" for="location_racks">Rak</label>
    <div class="d-flex gap-2">
        <select id="location_racks" name="racks[]" class="form-select js-location-racks" multiple
            data-placeholder="Pilih / tambah rak">
            @foreach ($selectedRacks as $rackName)
                <option value="{{ $rackName }}" selected>{{ $rackName }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-outline-secondary flex-shrink-0" data-bs-toggle="modal"
            data-bs-target="#rackMasterModal">+</button>
    </div>
    <div class="form-text">Contoh: Rak 1, Rak 2, Rak 3. Gunakan tombol + untuk menambah rak baru.</div>
    @error('racks')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('racks.*')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

<div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" name="is_active" value="1"
        {{ old('is_active', $location?->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label">Lokasi aktif</label>
</div>

<button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
<a href="{{ route('locations.index') }}" class="btn btn-secondary"><i class="bx bx-x me-1"></i>Batal</a>

<div class="modal fade" id="rackMasterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Rak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger d-none" id="rack-master-error"></div>
                <label class="form-label" for="rack_master_name">Nama Rak <span class="text-danger">*</span></label>
                <input type="text" id="rack_master_name" class="form-control" placeholder="Contoh: Rak 1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="save-rack-master">Tambah Rak</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container .select2-selection--multiple {
            min-height: calc(2.25rem + 2px);
            border: 1px solid #d9dee3;
            border-radius: 0.375rem;
            padding: 0.2rem 0.5rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: #f2f3f5;
            border: 0;
            border-radius: 999px;
            padding: 0.12rem 1.35rem 0.12rem 0.55rem;
            font-size: 0.82rem;
            position: relative;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: absolute;
            right: 0.45rem;
            left: auto;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            margin: 0;
            padding: 0;
            background: transparent;
            color: #8592a3;
            font-size: 1rem;
            line-height: 1;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover,
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus {
            background: transparent;
            color: #566a7f;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rackSelect = $('#location_racks');

            rackSelect.select2({
                width: '100%',
                tags: true,
                placeholder: rackSelect.data('placeholder'),
                tokenSeparators: [',']
            });

            const rackModalEl = document.getElementById('rackMasterModal');
            const rackModal = rackModalEl ? new bootstrap.Modal(rackModalEl) : null;

            rackModalEl?.addEventListener('shown.bs.modal', function() {
                document.getElementById('rack_master_name')?.focus();
            });

            rackModalEl?.addEventListener('hidden.bs.modal', function() {
                document.getElementById('rack_master_name').value = '';
                document.getElementById('rack-master-error').classList.add('d-none');
            });

            function addRackOption(name) {
                const normalizedName = name.trim();

                if (!normalizedName) {
                    return;
                }

                const exists = rackSelect.find('option').toArray().some(option => {
                    return option.value.toLowerCase() === normalizedName.toLowerCase();
                });

                if (!exists) {
                    rackSelect.append(new Option(normalizedName, normalizedName, true, true));
                }

                const values = rackSelect.val() || [];

                if (!values.includes(normalizedName)) {
                    values.push(normalizedName);
                }

                rackSelect.val(values).trigger('change');
            }

            document.getElementById('save-rack-master')?.addEventListener('click', function() {
                const input = document.getElementById('rack_master_name');
                const errorBox = document.getElementById('rack-master-error');
                const name = input.value.trim();

                if (!name) {
                    errorBox.textContent = 'Nama rak wajib diisi.';
                    errorBox.classList.remove('d-none');
                    return;
                }

                addRackOption(name);
                rackModal?.hide();
            });
        });
    </script>
@endpush

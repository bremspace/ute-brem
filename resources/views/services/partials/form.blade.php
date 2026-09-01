@php($service = $service ?? null)
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Kode Jasa</label>
        <input type="text" name="service_code" class="form-control @error('service_code') is-invalid @enderror"
            value="{{ old('service_code', $service?->service_code) }}" placeholder="Auto jika kosong">
        @error('service_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Nama Jasa <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control js-slug-source @error('name') is-invalid @enderror"
            value="{{ old('name', $service?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Slug</label>
        <input type="text" name="slug" class="form-control js-slug-target @error('slug') is-invalid @enderror"
            value="{{ old('slug', $service?->slug) }}">
        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Kategori</label>
        <input type="text" name="category" class="form-control" value="{{ old('category', $service?->category) }}"
            placeholder="Contoh: Service HP">
    </div>
    <div class="col-md-6">
        <label class="form-label">Golongan</label>
        <input type="text" name="group" class="form-control" value="{{ old('group', $service?->group) }}"
            placeholder="Contoh: Software / Hardware">
    </div>

    <div class="col-md-4">
        <label class="form-label">Harga Toko <span class="text-danger">*</span></label>
        <input type="number" min="0" step="1" name="price_toko" class="form-control @error('price_toko') is-invalid @enderror"
            value="{{ old('price_toko', $service?->price_toko ?? 0) }}" required>
        @error('price_toko')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Harga Partai</label>
        <input type="number" min="0" step="1" name="price_partai" class="form-control"
            value="{{ old('price_partai', $service?->price_partai ?? 0) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Harga Cabang</label>
        <input type="number" min="0" step="1" name="price_cabang" class="form-control"
            value="{{ old('price_cabang', $service?->price_cabang ?? 0) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">Gambar</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if($service?->image_path)
            <div class="form-text"><a href="{{ asset('storage/' . $service->image_path) }}" target="_blank">Lihat gambar saat ini</a></div>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label">Keterangan Gambar</label>
        <input type="text" name="image_note" class="form-control" value="{{ old('image_note', $service?->image_note) }}">
    </div>

    <div class="col-12">
        <div class="row g-2">
            @foreach ([
                'is_taxable' => 'Kena Pajak',
                'is_open_price' => 'Open Price Harga Jual',
                'allow_discount_override' => 'Open Price Diskon Jual',
                'is_published' => 'Tampilkan di webreport',
                'is_active' => 'Aktif',
            ] as $field => $label)
                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" name="{{ $field }}" value="1"
                            @checked((bool) old($field, $service?->{$field} ?? ($field === 'is_active')))>
                        <label class="form-check-label">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2">
        <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
    </div>
</div>

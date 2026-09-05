<div class="mb-3">
    <label class="form-label">Nama Group</label>
    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $customerGroup?->name) }}" required data-slug-source data-slug-target="#slug">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label" for="slug">Slug</label>
    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
        value="{{ old('slug', $customerGroup?->slug) }}" placeholder="Kosongkan untuk otomatis">
    <div class="form-text">Slug otomatis mengikuti nama, tapi bisa Anda ubah manual.</div>
    @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Urutan</label>
    <input type="number" min="0" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror"
        value="{{ old('sort_order', $customerGroup?->sort_order ?? 0) }}">
    @error('sort_order')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" name="is_active" value="1"
        {{ old('is_active', $customerGroup?->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label">Group aktif</label>
</div>

<button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
<a href="{{ route('customer-groups.index') }}" class="btn btn-outline-secondary"><i class="bx bx-x me-1"></i>Batal</a>

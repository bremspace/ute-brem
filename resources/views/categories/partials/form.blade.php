<div class="mb-3">
    <label for="name" class="form-label">Nama Kategori</label>
    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $category?->name) }}" required data-slug-source data-slug-target="#slug">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror"
        value="{{ old('slug', $category?->slug) }}" placeholder="Kosongkan untuk otomatis">
    <div class="form-text">Slug otomatis mengikuti nama, tapi bisa Anda ubah manual.</div>
    @error('slug')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Deskripsi</label>
    <textarea id="description" name="description" rows="4"
        class="form-control @error('description') is-invalid @enderror">{{ old('description', $category?->description) }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-check form-switch mb-4">
    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
        {{ old('is_active', $category?->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label" for="is_active">Kategori aktif</label>
</div>

<button type="submit" class="btn btn-primary">
    <i class="bx bx-save me-1"></i> Simpan
</button>
<a href="{{ route('categories.index') }}" class="btn btn-secondary">
    <i class="bx bx-x me-1"></i> Batal
</a>

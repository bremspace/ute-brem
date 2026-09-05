@php($unit = $unit ?? null)

<div class="row g-3">
    <div class="col-md-6"><label class="form-label">Nama Satuan <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit?->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Kode</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $unit?->code) }}">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $unit?->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Satuan aktif</label></div></div>
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary"><i class="bx bx-x me-1"></i>Batal</a>
</div>

@php($supplier = $supplier ?? null)

<div class="row g-3">
    <div class="col-md-8"><label class="form-label">Nama Supplier</label><input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier?->name) }}" required data-slug-source data-slug-target="#slug">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-4"><label class="form-label">Kode</label><input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $supplier?->code) }}">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label" for="slug">Slug</label><input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $supplier?->slug) }}" placeholder="Kosongkan untuk otomatis"><div class="form-text">Slug otomatis mengikuti nama, tapi bisa Anda ubah manual.</div>@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier?->phone) }}">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier?->email) }}">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">PIC / Contact Person</label><input type="text" name="contact_person" class="form-control @error('contact_person') is-invalid @enderror" value="{{ old('contact_person', $supplier?->contact_person) }}">@error('contact_person')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-md-6"><label class="form-label">Catatan</label><input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes', $supplier?->notes) }}">@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12"><label class="form-label">Alamat</label><textarea name="address" rows="4" class="form-control @error('address') is-invalid @enderror">{{ old('address', $supplier?->address) }}</textarea>@error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $supplier?->is_active ?? true) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Supplier aktif</label></div></div>
</div>
<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-x me-1"></i>Batal</a>
</div>

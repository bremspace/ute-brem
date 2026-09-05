<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Kode Member</label>
        <input type="text" name="member_code" class="form-control @error('member_code') is-invalid @enderror"
            value="{{ old('member_code', $customer?->member_code) }}" placeholder="Otomatis jika dikosongkan">
        <div class="form-text">Khusus customer member. Kosongkan untuk generate otomatis.</div>
        @error('member_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer?->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Tipe Customer <span class="text-danger">*</span></label>
        <select name="type" id="customer_type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="regular" @selected(old('type', $customer?->type ?? 'regular') === 'regular')>Biasa</option>
            <option value="member" @selected(old('type', $customer?->type) === 'member')>Member</option>
        </select>
        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">No HP</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer?->phone) }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer?->email) }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Customer Group</label>
        <select name="customer_group_id" class="form-select @error('customer_group_id') is-invalid @enderror">
            <option value="">Tanpa group</option>
            @foreach($customerGroups as $group)
                <option value="{{ $group->id }}" @selected((string) old('customer_group_id', $customer?->customer_group_id) === (string) $group->id)>{{ $group->name }}</option>
            @endforeach
        </select>
        @error('customer_group_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Password Member @if(empty($customer))<span class="text-danger password-required-mark d-none">*</span>@endif</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
        <div class="form-text">Wajib untuk customer member baru. Kosongkan saat edit jika tidak diganti.</div>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $customer?->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Customer aktif</label>
        </div>
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan</button>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-x me-1"></i>Batal</a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('customer_type');
    const mark = document.querySelector('.password-required-mark');
    const memberCodeWrap = document.querySelector('input[name="member_code"]')?.closest('.col-md-6');
    const syncMark = () => mark?.classList.toggle('d-none', type?.value !== 'member');
    const syncMemberCode = () => memberCodeWrap?.classList.toggle('d-none', type?.value !== 'member');
    type?.addEventListener('change', syncMark);
    type?.addEventListener('change', syncMemberCode);
    syncMark();
    syncMemberCode();
});
</script>
@endpush

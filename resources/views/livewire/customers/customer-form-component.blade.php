@extends('layouts.app')

@section('title', '{{ $customer ? $customer->name . " - Edit" : "Tambah Customer" }}')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $customer ? 'Edit Customer' : 'Tambah Customer' }}</h4>
            <div class="text-muted">
                @if($customer)
                    Edit data customer {{ $customer->name }}.
                @else
                    Isi form di bawah untuk menambahkan customer baru.
                @endif
            </div>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="{{ $customer ? 'update' : 'store' }}">
                <!-- Customer Group -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Customer Group <span class="text-danger">*</span></label>
                        <select name="customer_group_id" wire:model="customerGroupId" class="form-select" required>
                            <option value="">Pilih Group</option>
                            @foreach($customerGroups as $group)
                                <option value="{{ $group['id'] }}" {{ $customerGroupId == $group['id'] ? 'selected' : '' }}>
                                    {{ $group['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Name -->
                    <div class="col-md-4">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" wire:model="name" class="form-control" required placeholder="Nama lengkap">
                    </div>

                    <!-- Phone -->
                    <div class="col-md-4">
                        <label class="form-label">No HP <span class="text-danger">*</span></label>
                        <input type="text" name="phone" wire:model="phone" class="form-control" required placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>

                <!-- Email, Type, Password -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" wire:model="email" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipe</label>
                        <select name="type" wire:model="type" class="form-select">
                            <option value="regular">Regular</option>
                            <option value="member">Member</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" wire:model="password" class="form-control" 
                               placeholder="Kosongkan jika tidak ingin mengubah">
                        @if(! $customer && $type === 'member')
                            <div class="form-text">Password default: Member123!</div>
                        @endif
                    </div>
                </div>

                <!-- Status -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="isActive" id="isActive">
                            <label class="form-check-label" for="isActive">Aktif</label>
                        </div>
                    </div>
                </div>

                <!-- Member Code -->
                @if($customer && $customer->member_code)
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i>
                        Kode Member: <strong>{{ $customer->member_code }}</strong>
                    </div>
                @endif

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> {{ $customer ? 'Simpan Perubahan' : 'Simpan Customer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@livewireScripts
@endsection
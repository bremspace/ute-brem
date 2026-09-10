@extends('layouts.app')

@section('title', '{{ $user ? $user->name . " - Edit" : "Tambah User" }}')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $user ? 'Edit User' : 'Tambah User' }}</h4>
            <div class="text-muted">
                @if($user)
                    Edit data user {{ $user->name }}.
                @else
                    Isi form di bawah untuk menambahkan user baru.
                @endif
            </div>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="{{ $user ? 'update' : 'store' }}">
                <!-- Name, Username -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" wire:model="name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" wire:model="username" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" wire:model="email" class="form-control" required>
                    </div>
                </div>

                <!-- Password, Branch -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" wire:model="password" class="form-control" 
                               placeholder="{{ $user ? 'Kosongkan jika tidak ingin mengubah' : 'Min 8 karakter' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" wire:model="passwordConfirmation" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Branch</label>
                        <select name="branch_id" wire:model="branchId" class="form-select">
                            <option value="">Pilih Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch['id'] }}" {{ $branchId == $branch['id'] ? 'selected' : '' }}>
                                    {{ $branch['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Roles -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <div class="d-flex flex-column gap-2">
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model="selectedRoles" value="{{ $role['id'] }}"
                                           id="role-{{ $role['id'] }}">
                                    <label class="form-check-label" for="role-{{ $role['id'] }}">
                                        {{ $role['display_name'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
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

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> {{ $user ? 'Simpan Perubahan' : 'Simpan User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@livewireScripts
@endsection
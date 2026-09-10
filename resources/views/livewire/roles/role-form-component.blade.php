@extends('layouts.app')

@section('title', 'Tambah Role')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $role ? 'Edit Role' : 'Tambah Role' }}</h4>
            <div class="text-muted">{{ $role ? 'Edit role ' . $role->display_name : 'Buat role baru dengan permissions.' }}</div>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="{{ $role ? 'update' : 'store' }}">
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Nama Role (Kode) <span class="text-danger">*</span></label>
                        <input type="text" name="name" wire:model="name" class="form-control" required placeholder="contoh: user_manager">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" wire:model="displayName" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Deskripsi</label>
                        <input type="text" name="description" wire:model="description" class="form-control">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Permissions</label>
                    @foreach($permissionsByModule as $module => $modulePermissions)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">{{ ucfirst($module) }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:click="togglePermission({{ $permission['id'] }})"
                                                       @if(in_array($permission['id'], $selectedPermissions)) checked @endif
                                                       id="perm-{{ $permission['id'] }}">
                                                <label class="form-check-label" for="perm-{{ $permission['id'] }}">
                                                    {{ $permission['display_name'] }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> {{ $role ? 'Simpan Perubahan' : 'Simpan Role' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@livewireScripts
@endsection
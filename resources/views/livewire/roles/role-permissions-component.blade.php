@extends('layouts.app')
@section('title', 'Role Permissions')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Permissions: {{ $role->display_name }}</h4>
            <div class="text-muted">Kelola permissions untuk role ini.</div>
        </div>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="save">
                @foreach($permissionsByModule as $module => $modulePermissions)
                    <div class="card mb-3">
                        <div class="card-header"><h6 class="mb-0">{{ ucfirst($module) }}</h6></div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($modulePermissions as $perm)
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   wire:click="togglePermission({{ $perm['id'] }})"
                                                   @if(in_array($perm['id'], $selectedPermissionIds)) checked @endif
                                                   id="perm-{{ $perm['id'] }}">
                                            <label class="form-check-label" for="perm-{{ $perm['id'] }}">{{ $perm['display_name'] }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="text-end">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>
@livewireScripts
@endsection
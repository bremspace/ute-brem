@extends('layouts.app')
@section('title', 'Kelola Role User')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4">
        <h4 class="mb-1">Role User: {{ $user?->name }}</h4>
        <div class="text-muted">Kelola role assignment untuk user ini.</div>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    <div class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Role Tersedia</h5></div>
                <div class="card-body">
                    <div class="row">
                        @foreach($availableRoles as $role)
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 {{ in_array($role['id'], $selectedRoleIds) ? 'border-success bg-success bg-opacity-10' : '' }}">
                                    <span>{{ $role['display_name'] }}</span>
                                    @if(in_array($role['id'], $selectedRoleIds))
                                        <button wire:click="removeRole({{ $role['id'] }})" class="btn btn-sm btn-outline-danger"><i class="bx bx-x"></i></button>
                                    @else
                                        <button wire:click="assignRole({{ $role['id'] }})" class="btn btn-sm btn-outline-success"><i class="bx bx-plus"></i></button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Template</h5></div>
                <div class="card-body">
                    @forelse($roleTemplates as $tpl)
                        <button wire:click="applyTemplate({{ $tpl['id'] }})" class="btn btn-outline-primary w-100 mb-2">
                            {{ $tpl['display_name'] }}
                        </button>
                    @empty
                        <p class="text-muted">Belum ada template role.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@livewireScripts
@endsection
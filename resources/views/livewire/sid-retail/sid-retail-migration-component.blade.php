@extends('layouts.app')
@section('title', 'Migrasi SidRetail')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4"><h4 class="mb-1">Migrasi SidRetail</h4><div class="text-muted">Konfigurasi dan jalankan migrasi data dari SidRetail.</div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Konfigurasi</h5></div><div class="card-body">
                <form wire:submit.prevent="testConnection">
                    <div class="mb-3"><label class="form-label">Host</label><input type="text" wire:model="host" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Port</label><input type="number" wire:model="port" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Database</label><input type="text" wire:model="database" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" wire:model="username" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Password</label><input type="password" wire:model="password" class="form-control"></div>
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="bx bx-link me-1"></i> Test Koneksi</button>
                </form>
            </div></div>
        </div>
        <div class="col-md-8">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Status</h5></div><div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Status Saat Ini</label>
                    <div class="fs-5 fw-bold {{ $status === 'connected' ? 'text-success' : ($status === 'running' ? 'text-warning' : 'text-muted') }}">
                        {{ ucfirst($status) }}
                    </div>
                </div>
                <button wire:click="startMigration" class="btn btn-primary w-100" {{ $status !== 'connected' ? 'disabled' : '' }}>
                    <i class="bx bx-play me-1"></i> Mulai Migrasi
                </button>
            </div></div>
        </div>
    </div>
</div>
@livewireScripts
@endsection
@extends('layouts.app')

@section('title', 'Export Customer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Export Customer</h4>
            <div class="text-muted">Pilih scope export dan unduh data customer.</div>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <!-- Export Form -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="export">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Scope Export</label>
                        <select wire:model="scope" class="form-select">
                            <option value="all">Semua Customer</option>
                            <option value="selected">Customer Terpilih</option>
                        </select>
                    </div>
                    @if($scope === 'selected')
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Terpilih</label>
                            <input type="number" wire:model="selectedCount" class="form-control" placeholder="Masukkan jumlah" min="1">
                        </div>
                    @endif
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i> Export CSV
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@livewireScripts
@endsection
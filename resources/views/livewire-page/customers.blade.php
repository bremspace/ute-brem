@extends('layouts.sneat')

@section('title', 'Customer & Member (Livewire)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Pelanggan & Member</h4>
            <div class="text-muted">Daftar seluruh pelanggan dan data member.</div>
        </div>
        <div>
            @if (auth()->user()->hasPermission('master.customer_groups.create'))
                <a href="{{ route('customers.import') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bx bx-upload me-1"></i> Import
                </a>
                <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Tambah Pelanggan
                </a>
            @endif
        </div>
    </div>

    @livewire('customer-list')
</div>
@endsection

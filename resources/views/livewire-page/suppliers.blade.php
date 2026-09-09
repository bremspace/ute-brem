@extends('layouts.sneat')

@section('title', 'Supplier (Livewire)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Master Supplier</h4>
            <div class="text-muted">Daftar seluruh supplier atau vendor barang.</div>
        </div>
        <div>
            @if (auth()->user()->hasPermission('master.products.create'))
                <a href="{{ route('suppliers.import') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bx bx-upload me-1"></i> Import
                </a>
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Tambah Supplier
                </a>
            @endif
        </div>
    </div>

    @livewire('supplier-list')
</div>
@endsection

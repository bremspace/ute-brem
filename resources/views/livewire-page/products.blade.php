@extends('layouts.sneat')

@section('title', 'Master Produk (Livewire)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Master Produk & Stok</h4>
            <div class="text-muted">Kelola seluruh data barang, harga, dan informasi stok.</div>
        </div>
        <div>
            @if (auth()->user()->hasPermission('master.products.create'))
                <a href="{{ route('products.import') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bx bx-upload me-1"></i> Import
                </a>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Tambah Produk
                </a>
            @endif
        </div>
    </div>

    @livewire('product-list')
</div>
@endsection

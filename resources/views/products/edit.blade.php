@extends('layouts.sneat')

@section('title', 'Edit Produk')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Produk</h5>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.product_stocks.view'))
                        <a href="{{ route('products.stocks.index', $product) }}" class="btn btn-outline-primary">
                            <i class="bx bx-layer me-1"></i> Stok Lokasi
                        </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('products.partials.form', ['product' => $product])
                </form>
            </div>
        </div>
    </div>
@endsection

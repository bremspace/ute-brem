@extends('layouts.sneat')

@section('title', 'Tambah Produk')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tambah Produk</h5>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @include('products.partials.form', ['product' => null])
                </form>
            </div>
        </div>
    </div>
@endsection

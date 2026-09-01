@extends('layouts.sneat')

@section('title', 'Tambah Supplier')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tambah Supplier</h5>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                @include('suppliers.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection

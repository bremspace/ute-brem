@extends('layouts.sneat')

@section('title', 'Edit Supplier')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Supplier</h5>
            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('suppliers.update', $supplier) }}" method="POST">
                @csrf
                @method('PUT')
                @include('suppliers.partials.form', ['supplier' => $supplier])
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.sneat')

@section('title', 'Edit Merek')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Merek</h5>
            <a href="{{ route('product-makers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('product-makers.update', $productMaker) }}" method="POST">
                @csrf
                @method('PUT')
                @include('product-makers.partials.form', ['productMaker' => $productMaker])
            </form>
        </div>
    </div>
</div>
@endsection

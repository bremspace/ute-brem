@extends('layouts.sneat')

@section('title', 'Edit Customer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Customer</h5>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')
                @include('customers.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection

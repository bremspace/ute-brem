@extends('layouts.sneat')

@section('title', 'Edit Customer Group')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Customer Group</h5>
                <a href="{{ route('customer-groups.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
            <div class="card-body">
                <form action="{{ route('customer-groups.update', $customerGroup) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('customer-groups.partials.form', ['customerGroup' => $customerGroup])
                </form>
            </div>
        </div>
    </div>
@endsection

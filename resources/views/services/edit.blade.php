@extends('layouts.sneat')

@section('title', 'Edit Jasa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Jasa</h5>
                <a href="{{ route('services.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
            </div>
            <div class="card-body">
                <form action="{{ route('services.update', $service) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('services.partials.form')
                </form>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.sneat')

@section('title', 'Edit Satuan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Satuan</h5>
            <a href="{{ route('units.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('units.update', $unit) }}" method="POST">
                @csrf
                @method('PUT')
                @include('units.partials.form', ['unit' => $unit])
            </form>
        </div>
    </div>
</div>
@endsection

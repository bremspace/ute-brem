@extends('layouts.sneat')

@section('title', 'Tambah Satuan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tambah Satuan</h5>
            <a href="{{ route('units.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                @include('units.partials.form')
            </form>
        </div>
    </div>
</div>
@endsection

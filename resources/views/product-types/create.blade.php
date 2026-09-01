@extends('layouts.sneat')

@section('title', 'Tambah Tipe HP')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Tambah Tipe HP</h5><a href="{{ route('product-types.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a></div><div class="card-body"><form action="{{ route('product-types.store') }}" method="POST">@csrf @include('product-types.partials.form',['productType'=>null])</form></div></div></div>
@endsection

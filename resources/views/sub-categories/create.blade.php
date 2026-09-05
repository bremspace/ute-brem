@extends('layouts.sneat')

@section('title', 'Tambah Sub Kategori')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Tambah Sub Kategori</h5><a href="{{ route('sub-categories.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a></div><div class="card-body"><form action="{{ route('sub-categories.store') }}" method="POST">@csrf @include('sub-categories.partials.form',['subCategory'=>null])</form></div></div></div>
@endsection

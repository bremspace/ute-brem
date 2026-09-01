@extends('layouts.sneat')

@section('title', 'Edit Sub Kategori')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Edit Sub Kategori</h5><a href="{{ route('sub-categories.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a></div><div class="card-body"><form action="{{ route('sub-categories.update',$subCategory) }}" method="POST">@csrf @method('PUT') @include('sub-categories.partials.form',['subCategory'=>$subCategory])</form></div></div></div>
@endsection

@extends('layouts.sneat')

@section('title', 'Tambah Customer Group')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Tambah Customer Group</h5><a href="{{ route('customer-groups.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a></div><div class="card-body"><form action="{{ route('customer-groups.store') }}" method="POST">@csrf @include('customer-groups.partials.form',['customerGroup'=>null])</form></div></div></div>
@endsection

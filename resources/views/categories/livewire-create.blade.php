<!-- Wrapper view for category create using Livewire --
<@extends('layouts.sneat')

<@section('title', 'Tambah Kategori')

<@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @livewire('category-form-component')
    </div>
<@endsection

<!-- Wrapper view for category edit using Livewire --
<@extends('layouts.sneat')

<@section('title', 'Edit Kategori')

<@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @livewire('category-form-component', ['categoryId' => request()->route('category')])
    </div>
<@endsection

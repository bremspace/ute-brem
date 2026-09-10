<!-- wrapper view for purchase order form using Livewire -->
@extends('layouts.sneat')
@section('title', 'Buat Purchase Order')
@section('content')
    @livewire('po-form-component', ['productId' => request('product_id'), 'locationId' => request('location_id')])
@endsection

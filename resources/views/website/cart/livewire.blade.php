@extends('website.layout')

@section('title', 'Keranjang')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@section('content')
    @livewire('cart-component')
@endsection

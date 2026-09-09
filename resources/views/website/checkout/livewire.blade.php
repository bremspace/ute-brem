@extends('website.layout')

@section('title', 'Checkout')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@section('content')
    @livewire('checkout-component')
@endsection

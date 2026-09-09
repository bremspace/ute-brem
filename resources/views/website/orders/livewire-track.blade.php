@extends('website.layout')

@section('title', 'Lacak Pesanan — UTE Parts')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@section('content')
    @livewire('order-track-component', ['code' => $code])
@endsection
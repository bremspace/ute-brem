@extends('website.layout')

@section('title', 'Pesanan Berhasil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/website-shop.css') }}">
@endpush

@section('content')
    @livewire('order-detail-component', ['code' => $code])
@endsection
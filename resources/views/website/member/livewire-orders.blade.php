@extends('website.layout')

@section('title', 'Pesanan Saya')

@section('content')
    @livewire('member-dashboard-component', ['tab' => 'orders'])
@endsection

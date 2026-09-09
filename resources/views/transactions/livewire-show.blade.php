@extends('layouts.sneat')
@section('title', 'Detail Transaksi')
@section('content')
    @livewire('sales-index-component', ['viewSale' => $sale->id])
@endsection
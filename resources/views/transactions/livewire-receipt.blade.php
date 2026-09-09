@extends('layouts.sneat')
@section('title', 'Struk Transaksi')
@section('content')
    @livewire('sales-receipt-component', ['saleId' => $sale->id])
@endsection
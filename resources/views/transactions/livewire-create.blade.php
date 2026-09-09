@extends('layouts.sneat')
@section('title', 'POS - Transaksi Baru')
@section('content')
    @livewire('sales-form-component', ['saleChannel' => $saleChannel ?? 'toko'])
@endsection
@extends('layouts.sneat')
@section('title', 'Edit Lokasi')
@section('content')
    @livewire('location-form-component', ['locationId' => $location->id])
@endsection
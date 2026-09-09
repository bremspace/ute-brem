@extends('website.layout')

@section('title', 'Daftar Member')

@section('content')
    @livewire('auth-component', ['mode' => 'register'])
@endsection

@extends('website.layout')

@section('title', 'Ganti Password')

@section('content')
    @livewire('auth-component', ['mode' => 'password', 'returnUrl' => request('return')])
@endsection

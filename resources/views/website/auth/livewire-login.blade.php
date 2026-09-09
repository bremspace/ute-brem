@extends('website.layout')

@section('title', 'Masuk Member')

@section('content')
    @livewire('auth-component', ['mode' => 'login', 'returnUrl' => request('return')])
@endsection

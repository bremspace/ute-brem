@extends('website.layout')

@section('title', 'Poin Saya')

@section('content')
    @livewire('member-dashboard-component', ['tab' => 'points'])
@endsection
<?php
/**
 * Livewire wrapper for Settings page.
 * Extends admin layout (sneat) and mounts PrinterSettingsComponent.
 */
?>
@extends('layouts.sneat')

@section('title', 'Setting')

@section('content')
    @livewire('printer-settings-component')
@endsection

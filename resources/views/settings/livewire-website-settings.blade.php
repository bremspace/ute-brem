<?php
/**
 * Livewire wrapper for Website Settings page.
 * Extends admin layout (sneat) and mounts WebsiteSettingsComponent.
 */
?>
@extends('layouts.sneat')

@section('title', 'Pengaturan Website')

@section('content')
    @livewire('website-settings-component')
@endsection

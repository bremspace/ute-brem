@extends('layouts.sneat')

@section('title', 'Ganti Password')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errorMessage)
            <div class="alert alert-danger" role="alert">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ganti Password</h5>
                        <small class="text-muted">Gunakan password minimal 8 karakter, kombinasi huruf besar/kecil dan angka.</small>
                    </div>
                    <div class="card-body">
                        @livewire('profile-settings-component')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
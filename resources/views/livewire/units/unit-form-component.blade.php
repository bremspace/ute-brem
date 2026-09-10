@extends('layouts.app')
@section('title', 'Tambah Unit')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">{{ $unit ? 'Edit Unit' : 'Tambah Unit' }}</h4></div>
        <a href="{{ route('units.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
    </div>
    <div class="card"><div class="card-body">
        <form wire:submit.prevent="{{ $unit ? 'update' : 'store' }}">
            <div class="mb-3"><label class="form-label">Kode</label><input type="text" wire:model="code" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" wire:model="name" class="form-control" required></div>
            <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
        </form>
    </div></div>
</div>
@livewireScripts
@endsection
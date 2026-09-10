@extends('layouts.app')
@section('title', 'Kategori Biaya')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Kategori Biaya</h4><div class="text-muted">Kelola kategori pemasukan dan pengeluaran.</div></div>
        <button wire:click="openFormModal" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Tambah</button>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @foreach($this->categories as $cat)
                        <tr>
                            <td class="fw-semibold">{{ $cat->code }}</td>
                            <td>{{ $cat->name }}</td>
                            <td><span class="badge {{ $cat->type === 'income' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($cat->type) }}</span></td>
                            <td><span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="text-end"><button wire:click="toggleStatus({{ $cat->id }})" class="btn btn-sm btn-outline-warning">{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
<div class="modal fade" x-show="showFormModal" x-transition.opacity role="dialog" tabindex="-1"><div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Tambah Kategori</h5><button type="button" class="btn-close" @click="showFormModal = false"></button></div><div class="modal-body">
    <form wire:submit.prevent="store">
        <div class="mb-3"><label class="form-label">Kode <span class="text-danger">*</span></label><input type="text" wire:model="code" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nama <span class="text-danger">*</span></label><input type="text" wire:model="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Tipe <span class="text-danger">*</span></label><select wire:model="type" class="form-select"><option value="expense">Pengeluaran</option><option value="income">Pemasukan</option></select></div>
        <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan</button>
    </form>
</div></div></div></div>
@livewireScripts
@endsection
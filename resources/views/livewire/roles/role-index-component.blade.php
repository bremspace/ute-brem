@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Manajemen Role</h4>
            <div class="text-muted">Kelola role dan permissions.</div>
        </div>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Tambah Role
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="$refresh">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Cari</label>
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model="search" placeholder="Nama / Display Name">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tampilkan</label>
                        <select class="form-select" wire:model="perPage">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Role</h5>
            @if($roles->total() > 0)
                <span class="text-muted">Total: {{ $roles->total() }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($roles->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Display Name</th>
                                <th>Users</th>
                                <th>Permissions</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td class="fw-semibold">{{ $role->name }}</td>
                                    <td>{{ $role->display_name }}</td>
                                    <td>{{ $role->users_count ?? 0 }}</td>
                                    <td>{{ $role->permissions_count ?? 0 }}</td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button wire:click="openDetailModal({{ $role->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-detail"></i>
                                            </button>
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @if($role->users_count === 0)
                                                <button wire:click="confirmDelete({{ $role->id }})" class="btn btn-sm btn-outline-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $roles->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-inbox fs-1"></i></div>
                    <p>Belum ada role.</p>
                    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Tambah Role
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" x-show="showDetailModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Role</h5>
                <button type="button" class="btn-close" @click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedRoleId)
                    @livewire('roles.role-form', ['roleId' => $selectedRoleId], key('role-form-' . $selectedRoleId))
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" x-show="showDeleteConfirm" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Penghapusan</h5>
                <button type="button" class="btn-close" @click="cancelDelete()"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bx bx-error me-1"></i>
                    Role tidak dapat dihapus karena masih digunakan oleh user.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="cancelDelete()">Batal</button>
                <button class="btn btn-danger" @click="executeDelete()">Hapus</button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection
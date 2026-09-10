@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Daftar User</h4>
            <div class="text-muted">Kelola akun user dan akses.</div>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Tambah User
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
                            <input type="text" class="form-control" wire:model="search" placeholder="Nama / Username / Email">
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
            <h5 class="mb-0">Data User</h5>
            @if($users->total() > 0)
                <span class="text-muted">Total: {{ $users->total() }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($users->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Branch</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td class="fw-semibold">{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td class="text-muted small">{{ $user->email }}</td>
                                    <td>{{ $user->branch?->name ?? '-' }}</td>
                                    <td>{{ $user->roles->pluck('display_name')->join(', ') ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $user->is_active ? 'bg-label-success' : 'bg-label-secondary' }} fs-6">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button wire:click="openDetailModal({{ $user->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-detail"></i>
                                            </button>
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $users->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-inbox fs-1"></i></div>
                    <p>Belum ada data user.</p>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Tambah User
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
                <h5 class="modal-title">Detail User</h5>
                <button type="button" class="btn-close" @click="closeDetailModal()"></button>
            </div>
            <div class="modal-body">
                @if($selectedUserId)
                    @livewire('users.user-form', ['userId' => $selectedUserId], key('user-form-' . $selectedUserId))
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Restore Confirm Modal -->
<div class="modal fade" x-show="showRestoreModal" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pemulihan</h5>
                <button type="button" class="btn-close" @click="cancelRestore()"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin memulihkan user ini?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="cancelRestore()">Batal</button>
                <button class="btn btn-success" @click="executeRestore()">Pulihkan</button>
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
                    User akan dihapus secara permanen. Data tidak dapat dipulihkan.
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
@extends('layouts.app')
@section('title', 'Tempat Sampah User')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Tempat Sampah User</h4>
            <div class="text-muted">User yang dihapus (soft delete).</div>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form wire:submit.prevent="$refresh">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <input type="text" class="form-control" wire:model="search" placeholder="Nama / Username / Email">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($trashedUsers->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Dihapus</th>
                                <th>Oleh</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trashedUsers as $user)
                                <tr>
                                    <td class="fw-semibold">{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-muted small">{{ $user->deleted_at?->format('d/m/Y H:i') }}</td>
                                    <td>{{ $user->deleter?->name ?? '-' }}</td>
                                    <td class="text-end">
                                        <button wire:click="confirmRestore({{ $user->id }})" class="btn btn-sm btn-outline-success">
                                            <i class="bx bx-refresh me-1"></i> Pulihkan
                                        </button>
                                        <button wire:click="confirmForceDelete({{ $user->id }})" class="btn btn-sm btn-outline-danger"
                                                onclick="event.preventDefault(); return confirm('Hapus permanen?');">
                                            <i class="bx bx-trash me-1"></i> Hapus Permanen
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $trashedUsers->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-trash fs-1"></i></div>
                    <p>Tempat sampah kosong.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" x-show="showRestoreConfirm" x-transition.opacity role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pemulihan</h5>
                <button type="button" class="btn-close" @click="cancelRestore()"></button>
            </div>
            <div class="modal-body">
                <p>Yakin ingin memulihkan user ini?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="cancelRestore()">Batal</button>
                <button class="btn btn-success" @click="executeRestore()">Pulihkan</button>
            </div>
        </div>
    </div>
</div>

@livewireScripts
@endsection
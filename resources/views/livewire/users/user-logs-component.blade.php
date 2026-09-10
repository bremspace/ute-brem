@extends('layouts.app')

@section('title', 'User Activity Logs')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">User Activity Logs</h4>
            <div class="text-muted">Riwayat aktivitas user.</div>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
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
                            <input type="text" class="form-control" wire:model="search" placeholder="Deskripsi / User">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Aksi</label>
                        <select class="form-select" wire:model="actionFilter">
                            <option value="">Semua</option>
                            <option value="CREATE_USER">Create User</option>
                            <option value="UPDATE_USER">Update User</option>
                            <option value="DELETE_USER">Delete User</option>
                            <option value="RESTORE_USER">Restore User</option>
                            <option value="CREATE_ROLE">Create Role</option>
                            <option value="UPDATE_ROLE">Update Role</option>
                            <option value="DELETE_ROLE">Delete Role</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Data Logs</h5>
            @if($logs->total() > 0)
                <span class="text-muted">Total: {{ $logs->total() }}</span>
            @endif
        </div>
        <div class="card-body">
            @if($logs->total() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Aksi</th>
                                <th>Target User</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td class="text-muted small">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                    <td>{{ $log->user?->name ?? 'System' }}</td>
                                    <td>
                                        @php
                                            $badgeMap = [
                                                'CREATE_USER' => 'success',
                                                'UPDATE_USER' => 'warning',
                                                'DELETE_USER' => 'danger',
                                                'RESTORE_USER' => 'info',
                                                'FORCE_DELETE_USER' => 'dark',
                                                'VIEW_USER' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $badgeMap[$log->action] ?? 'primary' }}">
                                            {{ str_replace('_', ' ', $log->action) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->targetUser?->name ?? '-' }}</td>
                                    <td class="small">{{ $log->description }}</td>
                                    <td class="text-muted small">{{ $log->ip_address }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $logs->links() }}
            @else
                <div class="text-center py-5">
                    <div class="text-muted mb-3"><i class="bx bx-history fs-1"></i></div>
                    <p>Belum ada activity logs.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@livewireScripts
@endsection
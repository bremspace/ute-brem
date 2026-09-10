@extends('layouts.app')

@section('title', 'Import Customer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Import Customer</h4>
            <div class="text-muted">Upload Excel, cek preview, lalu simpan import.</div>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($showResult)
        <div class="alert alert-success alert-dismissible" role="alert">
            <strong>Import berhasil!</strong> {{ $createdCount }} customer ditambahkan.
            @if($skippedCount > 0)
                <span class="text-danger">{{ $skippedCount }} customer dilewati (duplikat/error).</span>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Template Info -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Template Import</h5>
                    <div class="text-muted small">Kolom wajib: name dan type. Untuk type member, password wajib diisi.</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.import.template') }}" class="btn btn-outline-primary">
                        <i class="bx bx-download me-1"></i> Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card">
        <div class="card-body">
            <form wire:submit.prevent="previewImport" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label">File Excel</label>
                        <input type="file" wire:model="file" class="form-control" accept=".xlsx,.xls" required>
                        <div class="form-text">Format .xlsx atau .xls, maksimal 5 MB.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Customer Group Default</label>
                        <select wire:model="defaultGroupId" class="form-select">
                            @foreach($customerGroups as $group)
                                <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary" {{ !$file ? 'disabled' : '' }}>
                        <i class="bx bx-search-alt me-1"></i> Preview Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview -->
    @if($showPreview && !empty($preview['rows']))
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Preview Import</h5>
                <div>
                    <span class="badge bg-label-success">Valid: {{ $preview['valid'] }}</span>
                    <span class="badge bg-label-danger">Error: {{ $preview['invalid'] }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Baris</th>
                                <th>Status</th>
                                <th>Nama</th>
                                <th>Tipe</th>
                                <th>Email</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['rows'] as $row)
                                <tr class="{{ $row['status'] === 'error' ? 'table-danger' : '' }}">
                                    <td>{{ $row['row_number'] }}</td>
                                    <td>
                                        <span class="badge {{ $row['status'] === 'valid' ? 'bg-label-success' : 'bg-label-danger' }}">
                                            {{ ucfirst($row['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $row['data']['name'] ?? '-' }}</td>
                                    <td>{{ $row['data']['type'] ?? '-' }}</td>
                                    <td>{{ $row['data']['email'] ?? '-' }}</td>
                                    <td class="text-danger small">
                                        @foreach($row['errors'] as $error)
                                            <div>{{ $error }}</div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <form wire:submit.prevent="storeImport">
                        <input type="hidden" wire:model="defaultGroupId">
                        <button type="submit" class="btn btn-success" {{ $preview['has_errors'] ? 'disabled' : '' }}>
                            <i class="bx bx-check me-1"></i> Simpan Import ({{ $preview['valid'] }} data)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@livewireScripts
@endsection
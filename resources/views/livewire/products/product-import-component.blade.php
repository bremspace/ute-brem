@extends('layouts.app')
@section('title', 'Import Produk')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Import Produk</h4><div class="text-muted">Upload Excel, cek preview, lalu simpan import.</div></div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Kembali</a>
    </div>
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($showResult)
        <div class="alert alert-success alert-dismissible" role="alert"><strong>Berhasil!</strong> {{ $createdCount }} produk ditambahkan. @if($skippedCount > 0)<span class="text-danger">{{ $skippedCount }} dilewati.</span>@endif</div>
    @endif
    <div class="card mb-4"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div><h5 class="mb-1">Template Import</h5><div class="text-muted small">Kolom wajib: product_code, name.</div></div>
            <a href="{{ route('products.import.template') }}" class="btn btn-outline-primary"><i class="bx bx-download me-1"></i> Download Template</a>
        </div>
    </div></div>
    <div class="card"><div class="card-body">
        <form wire:submit.prevent="previewImport" enctype="multipart/form-data">
            <div class="mb-3"><label class="form-label">File Excel</label><input type="file" wire:model="file" class="form-control" accept=".xlsx,.xls" required></div>
            <div class="text-end"><button type="submit" class="btn btn-primary" {{ !$file ? 'disabled' : '' }}><i class="bx bx-search-alt me-1"></i> Preview</button></div>
        </form>
    </div></div>
    @if($showPreview && !empty($preview['rows']))
        <div class="card mt-4"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">Preview</h5><div><span class="badge bg-success">Valid: {{ $preview['valid'] }}</span> <span class="badge bg-danger">Error: {{ $preview['invalid'] }}</span></div></div>
            <div class="card-body">
                <div class="table-responsive"><table class="table table-striped"><thead><tr><th>Baris</th><th>Status</th><th>Kode</th><th>Nama</th><th>Catatan</th></tr></thead><tbody>
                    @foreach($preview['rows'] as $row)<tr class="{{ $row['status'] === 'error' ? 'table-danger' : '' }}"><td>{{ $row['row_number'] }}</td><td><span class="badge {{ $row['status'] === 'valid' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($row['status']) }}</span></td><td>{{ $row['data']['product_code'] ?? '-' }}</td><td>{{ $row['data']['name'] ?? '-' }}</td><td class="text-danger small">@foreach($row['errors'] as $e)<div>{{ $e }}</div>@endforeach</td></tr>@endforeach
                </tbody></table></div>
                <div class="text-end mt-3"><form wire:submit.prevent="storeImport"><button type="submit" class="btn btn-success" {{ $preview['has_errors'] ? 'disabled' : '' }}><i class="bx bx-check me-1"></i> Simpan Import ({{ $preview['valid'] }} data)</button></form></div>
            </div>
        </div>
    @endif
</div>
@livewireScripts
@endsection
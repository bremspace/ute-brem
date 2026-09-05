@extends('layouts.sneat')

@section('title', $title)

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <div class="fw-semibold mb-1">File import belum valid.</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $title }}</h4>
                <div class="text-muted">Upload Excel, cek preview, lalu simpan import.</div>
            </div>
            <a href="{{ $backRoute }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Template</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Download template Excel, isi data, lalu upload kembali untuk preview.</p>
                        <a href="{{ $templateRoute }}" class="btn btn-outline-primary w-100 mb-3">
                            <i class="bx bx-download me-1"></i> Download Template Excel
                        </a>
                        <div class="alert alert-label-info mb-0">
                            {{ $templateNote }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Upload File</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $previewRoute }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">File Excel <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                <div class="form-text">Format .xlsx atau .xls, maksimal 5 MB.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search-alt me-1"></i> Preview Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if ($preview)
            <div class="card mt-4">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h5 class="mb-1">Preview Import</h5>
                        <div class="text-muted small">Dipreview: {{ $preview['previewed_at'] }}</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-label-secondary">Total: {{ $preview['total'] }}</span>
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
                                    @foreach ($columns as $label)
                                        <th>{{ $label }}</th>
                                    @endforeach
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($preview['rows'] as $row)
                                    <tr>
                                        <td>{{ $row['row_number'] }}</td>
                                        <td>
                                            @if ($row['status'] === 'valid')
                                                <span class="badge bg-label-success">Valid</span>
                                            @else
                                                <span class="badge bg-label-danger">Error</span>
                                            @endif
                                        </td>
                                        @foreach ($columns as $field => $label)
                                            <td>{{ ($row['data'][$field] ?? '') !== '' ? $row['data'][$field] : '-' }}</td>
                                        @endforeach
                                        <td style="min-width: 220px;">
                                            @foreach ($row['errors'] as $error)
                                                <div class="text-danger small"><i class="bx bx-error-circle me-1"></i>{{ $error }}</div>
                                            @endforeach
                                            @foreach ($row['notes'] as $note)
                                                <div class="text-muted small"><i class="bx bx-info-circle me-1"></i>{{ $note }}</div>
                                            @endforeach
                                            @if (empty($row['errors']) && empty($row['notes']))
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <form action="{{ $storeRoute }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary" {{ $preview['has_errors'] ? 'disabled' : '' }}
                                onclick="return confirm('Import {{ $preview['valid'] }} data sekarang?')">
                                <i class="bx bx-check me-1"></i> Simpan Import
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

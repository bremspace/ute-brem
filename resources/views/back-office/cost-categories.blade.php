@extends('layouts.sneat')

@section('title', 'Master Data Biaya')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Master Data Biaya</h4><div class="text-muted">Kategori pemasukan dan pengeluaran Back Office.</div></div>
        <a href="{{ route('back-office.dashboard') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('back-office.cost-categories.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-2"><label class="form-label">Kode</label><input name="code" class="form-control" required></div>
                <div class="col-md-5"><label class="form-label">Nama</label><input name="name" class="form-control" required></div>
                <div class="col-md-3"><label class="form-label">Tipe</label><select name="type" class="form-select"><option value="expense">Pengeluaran</option><option value="income">Pemasukan</option></select></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bx bx-save me-1"></i>Simpan</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="fw-semibold">{{ $row->code }}</td>
                            <td>{{ $row->name }}</td>
                            <td><span class="badge bg-label-{{ $row->type === 'income' ? 'success' : 'danger' }}">{{ $row->type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}</span></td>
                            <td><span class="badge bg-label-{{ $row->is_active ? 'success' : 'secondary' }}">{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada data biaya.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

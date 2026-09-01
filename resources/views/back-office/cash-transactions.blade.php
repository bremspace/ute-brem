@extends('layouts.sneat')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">{{ $title }}</h4><div class="text-muted">Mencatat {{ strtolower($title) }} dan langsung mengubah saldo kas.</div></div>
        <a href="{{ route('back-office.dashboard') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('back-office.cash-transactions.store', $type) }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-2"><label class="form-label">Tanggal</label><input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-3"><label class="form-label">Kas</label><select name="cash_account_id" class="form-select" required>@foreach($cashAccounts as $account)<option value="{{ $account->id }}">{{ $account->name }} - Rp {{ number_format((float) $account->current_balance, 0, ',', '.') }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Jenis</label><select name="cost_category_id" class="form-select"><option value="">-</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label">Jumlah</label><input type="number" min="1" step="1" name="amount" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Ref</label><input name="reference" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><option value="">-</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Keterangan</label><input name="description" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bx bx-save me-1"></i>Simpan</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Jenis</th><th>Kas</th><th>Keterangan</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ optional($row->transaction_date)->format('d/m/Y') }}</td>
                            <td class="fw-semibold">{{ $row->transaction_code }}</td>
                            <td>{{ $row->costCategory?->name ?: '-' }}</td>
                            <td>{{ $row->cashAccount?->name ?: '-' }}</td>
                            <td>{{ $row->description ?: '-' }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format((float) $row->amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

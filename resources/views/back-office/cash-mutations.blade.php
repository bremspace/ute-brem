@extends('layouts.sneat')

@section('title', 'Mutasi Kas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h4 class="mb-1">Mutasi Kas</h4><div class="text-muted">Transfer saldo antar kas/bank.</div></div>
        <a href="{{ route('back-office.dashboard') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('back-office.cash-mutations.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-2"><label class="form-label">Tanggal</label><input type="date" name="transaction_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                <div class="col-md-3"><label class="form-label">Dari Kas</label><select name="cash_account_id" class="form-select" required>@foreach($cashAccounts as $account)<option value="{{ $account->id }}">{{ $account->name }} - Rp {{ number_format((float) $account->current_balance, 0, ',', '.') }}</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label">Ke Kas</label><select name="target_cash_account_id" class="form-select" required>@foreach($cashAccounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label">Jumlah</label><input type="number" min="1" step="1" name="amount" class="form-control" required></div>
                <div class="col-md-2"><label class="form-label">Ref</label><input name="reference" class="form-control"></div>
                <div class="col-md-10"><label class="form-label">Keterangan</label><input name="description" class="form-control"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bx bx-transfer me-1"></i>Mutasi</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead><tr><th>Tanggal</th><th>Kode</th><th>Dari</th><th>Ke</th><th>Keterangan</th><th class="text-end">Jumlah</th></tr></thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr><td>{{ optional($row->transaction_date)->format('d/m/Y') }}</td><td class="fw-semibold">{{ $row->transaction_code }}</td><td>{{ $row->cashAccount?->name ?: '-' }}</td><td>{{ $row->targetCashAccount?->name ?: '-' }}</td><td>{{ $row->description ?: '-' }}</td><td class="text-end fw-bold">Rp {{ number_format((float) $row->amount, 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Belum ada mutasi kas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Bagan Akun')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4"><h4 class="mb-1">Bagan Akun (Chart of Accounts)</h4><div class="text-muted">Daftar semua akun beserta saldo saat ini.</div></div>
    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Normal</th><th class="text-end">Saldo</th></tr></thead>
                <tbody>
                    @foreach($this->accounts as $acc)
                        @if(! $acc->parent_id)
                            <tr class="table-light fw-bold"><td colspan="3">{{ $acc->name }}</td><td></td><td></td></tr>
                        @endif
                        <tr>
                            <td class="{{ $acc->parent_id ? 'ps-4' : '' }} small">{{ $acc->code }}</td>
                            <td>{{ $acc->name }}</td>
                            <td><span class="badge bg-label-info">{{ ucfirst($acc->category) }}</span></td>
                            <td>{{ ucfirst($acc->normal_balance) }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($this->balances[$acc->code] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@livewireScripts
@endsection
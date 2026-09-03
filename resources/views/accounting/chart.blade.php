@extends('layouts.sneat')

@section('title', 'Bagan Akun')

@section('content')
@php $acctTab = 'chart'; @endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Bagan Akun (Chart of Accounts)</h4>
        <small class="text-muted">Struktur akun aset, kewajiban, ekuitas, pendapatan & beban beserta saldonya.</small>
    </div>
</div>

@include('accounting._nav', compact('acctTab'))

@foreach ([['Aset', 'asset', '#dee2e6', $assets], ['Kewajiban', 'liability', '#fff3cd', $liabilities], ['Ekuitas', 'equity', '#d1e7dd', $equity], ['Pendapatan', 'revenue', '#f8d7da', $revenue], ['Beban', 'expense', '#cfe2ff', $expenses]] as [$title, $cat, $color, $list])
    <div class="card mb-3">
        <div class="card-header py-2" style="background:{{ $color }};">
            <span class="fw-bold text-uppercase small">{{ $title }} ({{ $cat }})</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Kode</th><th>Akun</th><th>Saldo Normal</th><th class="text-end">Saldo</th></tr>
                </thead>
                <tbody>
                    @foreach ($list as $acc)
                        <tr>
                            <td class="text-muted">{{ $acc->code }}</td>
                            <td>{{ $acc->name }}
                                @if (!$acc->is_postable)<span class="badge bg-light text-muted">Header</span>@endif
                            </td>
                            <td class="text-muted">{{ ucfirst($acc->normal_balance) }}</td>
                            <td class="text-end fw-semibold">{{ isset($balance[$acc->code]) ? number_format($balance[$acc->code],0,',','.') : '-' }}</td>
                        </tr>
                    @endforeach
                    @if ($list->where('is_postable', true)->isEmpty())
                        <tr><td colspan="4" class="text-center text-muted py-3">Belum ada akun postable pada grup ini.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection

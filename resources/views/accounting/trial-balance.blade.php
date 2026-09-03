@extends('layouts.sneat')

@section('title', 'Neraca Saldo')

@section('content')
@php $acctTab = 'trial'; @endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Neraca Saldo (Trial Balance)</h4>
        <small class="text-muted">Saldo debit & kredit semua akun per periode — wajib seimbang.</small>
    </div>
</div>

@include('accounting._nav', compact('acctTab'))

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-auto"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th><th>Akun</th>
                    <th class="text-end">Debit</th><th class="text-end">Kredit</th>
                    <th class="text-end">Saldo Debit</th><th class="text-end">Saldo Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $r)
                    <tr>
                        <td class="text-muted">{{ $r->code }}</td>
                        <td>{{ $r->name }}
                            <span class="badge bg-light text-muted">{{ substr($r->category,0,4) }}</span>
                        </td>
                        <td class="text-end">{{ $r->debit ? number_format($r->debit,0,',','.') : '' }}</td>
                        <td class="text-end">{{ $r->credit ? number_format($r->credit,0,',','.') : '' }}</td>
                        <td class="text-end">{{ $r->balDebit ? number_format($r->balDebit,0,',','.') : '' }}</td>
                        <td class="text-end">{{ $r->balCredit ? number_format($r->balCredit,0,',','.') : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-dark">
                    <td colspan="2" class="fw-bold">TOTAL</td>
                    <td class="text-end fw-bold">{{ number_format($totals['debit'],0,',','.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($totals['credit'],0,',','.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($totals['balDebit'],0,',','.') }}</td>
                    <td class="text-end fw-bold">{{ number_format($totals['balCredit'],0,',','.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

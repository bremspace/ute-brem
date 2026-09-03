@extends('layouts.sneat')

@section('title', 'Jurnal Umum')

@section('content')
@php $acctTab = 'journal'; @endphp

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h4 class="fw-semibold mb-0">Jurnal Umum</h4>
        <small class="text-muted">Semua pencatatan double-entry — debit selalu sama dengan kredit.</small>
    </div>
</div>

@include('accounting._nav', compact('acctTab'))

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <div class="col-auto"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
            <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
            <div class="col-auto ms-auto d-flex gap-3">
                <span class="text-muted small">Total Debit: <b>{{ number_format($totalDebit,0,',','.') }}</b></span>
                <span class="text-muted small">Total Kredit: <b>{{ number_format($totalCredit,0,',','.') }}</b></span>
            </div>
        </form>
    </div>
</div>

@forelse ($entries as $date => $group)
    <div class="card mb-3">
        <div class="card-header bg-light fw-semibold">
            <span class="badge bg-primary me-2">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
            {{ $group->count() }} jurnal
        </div>
        <div class="card-body p-0">
            @foreach ($group as $entry)
                <table class="table table-sm table-borderless align-middle mb-0">
                    <thead>
                        <tr class="table-secondary">
                            <th colspan="3" class="small">
                                <span class="badge bg-dark">{{ $entry->journal_code }}</span>
                                <span class="ms-2">{{ $entry->description }}</span>
                                <span class="ms-2 text-muted">({{ $entry->source_reference }})</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entry->lines as $line)
                            <tr>
                                <td class="ps-4 text-muted">{{ $line->account->code }}</td>
                                <td>{{ $line->account->name }}</td>
                                <td class="text-end w-25">{{ $line->debit ? number_format($line->debit,0,',','.') : '' }}</td>
                                <td class="text-end w-25">{{ $line->credit ? number_format($line->credit,0,',','.') : '' }}</td>
                                <td class="small text-muted">{{ $line->memo }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-bottom">
                            <td colspan="4"></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        </div>
    </div>
@empty
    <div class="alert alert-info">Belum ada jurnal pada periode ini.</div>
@endforelse
@endsection

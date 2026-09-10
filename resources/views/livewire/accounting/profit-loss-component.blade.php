@extends('layouts.app')
@section('title', 'Laba Rugi')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @livewireStyles
    <div class="mb-4"><h4 class="mb-1">Laba Rugi (Income Statement)</h4><div class="text-muted">Ringkasan pendapatan dan pengeluaran.</div></div>
    <div class="card mb-4"><div class="card-body">
        <form wire:submit.prevent="$refresh" class="row g-3 align-items-end">
            <div class="col-md-4"><label class="form-label">Dari</label><input type="date" wire:model="from" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Sampai</label><input type="date" wire:model="to" class="form-control"></div>
            <div class="col-md-4"><button type="submit" class="btn btn-primary"><i class="bx bx-filter me-1"></i> Filter</button></div>
        </form>
    </div></div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Pendapatan (Revenue)</h5></div><div class="card-body">
                <table class="table table-sm"><thead><tr><th>Akun</th><th class="text-end">Jumlah</th></tr></thead><tbody>
                    @foreach($revenue['rows'] as $r)<tr><td>{{ $r->name }}</td><td class="text-end">Rp {{ number_format($r->amount, 0, ',', '.') }}</td></tr>@endforeach
                </tbody></table>
                <div class="text-end fw-bold mt-2">Total: Rp {{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">HPP / COGS</h5></div><div class="card-body">
                <table class="table table-sm"><thead><tr><th>Akun</th><th class="text-end">Jumlah</th></tr></thead><tbody>
                    @foreach($cogs['rows'] as $r)<tr><td>{{ $r->name }}</td><td class="text-end">Rp {{ number_format($r->amount, 0, ',', '.') }}</td></tr>@endforeach
                </tbody></table>
                <div class="text-end fw-bold mt-2">Total: Rp {{ number_format($totalCogs, 0, ',', '.') }}</div>
            </div></div>
        </div>
    </div>
    <div class="card mt-4"><div class="card-body">
        <div class="d-flex justify-content-between py-2 border-bottom"><span>Laba Kotor (Gross Profit)</span><strong class="text-success">Rp {{ number_format($grossProfit, 0, ',', '.') }}</strong></div>
        <div class="d-flex justify-content-between py-2 border-bottom"><span>Pengeluaran Operasional</span><strong class="text-danger">- Rp {{ number_format($totalExpense, 0, ',', '.') }}</strong></div>
        <div class="d-flex justify-content-between py-2"><span class="fs-5 fw-bold">Laba Bersih (Net Profit)</span><strong class="fs-5 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp {{ number_format($netProfit, 0, ',', '.') }}</strong></div>
    </div></div>
</div>
@livewireScripts
@endsection
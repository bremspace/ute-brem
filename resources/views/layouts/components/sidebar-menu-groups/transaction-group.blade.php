<!-- Transaction Menu Group -->
@php
    $activeRoute = request()->route()->getName();
@endphp

<div class="nav-item mb-2">
    <div class="text-uppercase text-muted fw-bold small px-2 mb-2">
        Transaksi
    </div>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'transactions') ? 'active' : '' }}"
       href="{{ route('transactions.index') }}">
        <i class="bi bi-cash-coin me-2"></i> POS / Sales
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'service-transactions') ? 'active' : '' }}"
       href="{{ route('service-transactions.index') }}">
        <i class="bi bi-wrench me-2"></i> Service Jobs
    </a>
</div>

<!-- Back Office Menu Group -->
<div class="nav-item mb-2">
    <div class="text-uppercase text-muted fw-bold small px-2 mb-2">
        Back Office
    </div>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'back-office.cash-accounts') ? 'active' : '' }}"
       href="{{ route('back-office.cash-accounts.index') }}">
        <i class="bi bi-building me-2"></i> Kas & Bank
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'back-office.cash-transactions') ? 'active' : '' }}"
       href="{{ route('back-office.cash-transactions.index', ['type' => 'expense']) }}">
        <i class="bi bi-arrow-repeat me-2"></i> Transaksi Kas
    </a>
</div>
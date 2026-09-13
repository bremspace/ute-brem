<!-- Dashboard Menu Group -->
@php
    $activeRoute = request()->route()->getName();
@endphp

<div class="nav-item mb-2">
    <div class="text-uppercase text-muted fw-bold small px-2 mb-2">
        Dashboard & Tools
    </div>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'home') || str_contains($activeRoute, 'dashboard') ? 'active' : '' }}"
       href="{{ route('home') }}">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'transactions') ? 'active' : '' }}"
       href="{{ route('transactions.index') }}">
        <i class="bi bi-cash-coin me-2"></i> POS / Transactions
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'service-transactions') ? 'active' : '' }}"
       href="{{ route('service-transactions.index') }}">
        <i class="bi bi-tools me-2"></i> Service Jobs
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'purchase-orders') ? 'active' : '' }}"
       href="{{ route('purchase-orders.index') }}">
        <i class="bi bi-cart4 me-2"></i> Purchase Orders
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'branch-transfers') ? 'active' : '' }}"
       href="{{ route('branch-transfers.index') }}">
        <i class="bi bi-box-seam me-2"></i> Branch Transfers
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'back-office') ? 'active' : '' }}"
       href="{{ route('back-office.dashboard') }}">
        <i class="bi bi-building me-2"></i> Back Office
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'accounting') ? 'active' : '' }}"
       href="{{ route('accounting.index') }}">
        <i class="bi bi-calculator me-2"></i> Accounting
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'reports') ? 'active' : '' }}"
       href="{{ route('reports.index') }}">
        <i class="bi bi-file-earmark-bar-graph me-2"></i> Reports
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'settings') ? 'active' : '' }}"
       href="{{ route('settings.index') }}">
        <i class="bi bi-gear me-2"></i> Settings
    </a>
</div>
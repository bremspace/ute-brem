<!-- Inventory Menu Group -->
@php
    $activeRoute = request()->route()->getName();
@endphp

<div class="nav-item mb-2">
    <div class="text-uppercase text-muted fw-bold small px-2 mb-2">
        Inventory
    </div>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'livewire.stocks') || str_contains($activeRoute, 'stocks') ? 'active' : '' }}"
       href="{{ route('livewire.stocks.index') }}">
        <i class="bi bi-box-seam me-2"></i> Stocks
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'purchase-orders') ? 'active' : '' }}"
       href="{{ route('purchase-orders.index') }}">
        <i class="bi bi-cart4 me-2"></i> Purchase Orders
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'stock-opname') ? 'active' : '' }}"
       href="{{ route('stock-opname.index') }}">
        <i class="bi bi-clipboard-data me-2"></i> Stock Opname
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'branch-transfers') ? 'active' : '' }}"
       href="{{ route('branch-transfers.index') }}">
        <i class="bi bi-box-seam me-2"></i> Branch Transfers
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'picking-requests') ? 'active' : '' }}"
       href="{{ route('picking-requests.index') }}">
        <i class="bi bi-truck me-2"></i> Picking Requests
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'item-serials') ? 'active' : '' }}"
       href="{{ route('item-serials.index') }}">
        <i class="bi bi-barcode me-2"></i> Serial Numbers
    </a>
</div>
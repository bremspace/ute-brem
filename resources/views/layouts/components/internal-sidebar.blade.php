<!-- Internal Dashboard Sidebar Component -->
@php
    $user = Auth::user();
    $role = $user->role?->name ?? 'guest';
    $isKasir = str_contains($role, 'kasir') || str_contains($role, 'cashier');
    $isManager = str_contains($role, 'manager') || str_contains($role, 'super');
@endphp

<div class="bg-white shadow-sm d-none d-lg-block"
     :class="sidebarExpanded ? '' : 'collapsed'"
     style="width: 250px; height: calc(100vh - 56px); top: 56px; z-index: 1030; overflow-y: auto; transition: width 0.3s ease;">
    <div class="p-3 border-bottom">
        <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3"
                 style="width: 40px; height: 40px; min-width: 40px;">
                <i class="bi bi-shop"></i>
            </div>
            <div>
                <div class="fw-bold small">UTE Parts POS</div>
                <div class="text-muted small">{{ Auth::user()->role->name ?? 'User' }}</div>
            </div>
        </div>
    </div>

    <div class="nav flex-pills nav-pills-custom px-2 py-3" role="tablist">
        @include('layouts.components.sidebar-menu-groups.dashboard-group')
        @include('layouts.components.sidebar-menu-groups.master-data-group')
        @include('layouts.components.sidebar-menu-groups.inventory-group')
        @include('layouts.components.sidebar-menu-groups.transaction-group')
        @include('layouts.components.sidebar-menu-groups.settings-group')
    </div>
</div>
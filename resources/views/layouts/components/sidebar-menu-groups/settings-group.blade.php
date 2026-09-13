<!-- Settings Menu Group -->
@php
    $activeRoute = request()->route()->getName();
@endphp

<div class="nav-item mb-2">
    <div class="text-uppercase text-muted fw-bold small px-2 mb-2">
        Pengaturan
    </div>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'settings') ? 'active' : '' }}"
       href="{{ route('settings.index') }}">
        <i class="bi bi-gear me-2"></i> Printer Settings
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'profile') ? 'active' : '' }}"
       href="{{ route('profile.password.edit') }}">
        <i class="bi bi-person-circle me-2"></i> Profile
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'users') ? 'active' : '' }}"
       href="{{ route('users.index') }}">
        <i class="bi bi-people me-2"></i> Users & Roles
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'reports') ? 'active' : '' }}"
       href="{{ route('reports.index') }}">
        <i class="bi bi-file-earmark-bar-graph me-2"></i> Reports
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'accounting') ? 'active' : '' }}"
       href="{{ route('accounting.index') }}">
        <i class="bi bi-calculator me-2"></i> Accounting
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'back-office') ? 'active' : '' }}"
       href="{{ route('back-office.dashboard') }}">
        <i class="bi bi-building me-2"></i> Back Office
    </a>
</div>
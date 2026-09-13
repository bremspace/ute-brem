<!-- Master Data Menu Group -->
@php
    $activeRoute = request()->route()->getName();
@endphp

<div class="nav-item mb-2">
    <div class="text-uppercase text-muted fw-bold small px-2 mb-2">
        Master Data
    </div>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'categories') ? 'active' : '' }}"
       href="{{ route('categories.index') }}">
        <i class="bi bi-folder me-2"></i> Categories
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'products') ? 'active' : '' }}"
       href="{{ route('products.index') }}">
        <i class="bi bi-box-seam me-2"></i> Products
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'sub-categories') ? 'active' : '' }}"
       href="{{ route('sub-categories.index') }}">
        <i class="bi bi-folder2 me-2"></i> Sub-Categories
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'brands') ? 'active' : '' }}"
       href="{{ route('brands.index') }}">
        <i class="bi bi-tag me-2"></i> Brands
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'product-types') ? 'active' : '' }}"
       href="{{ route('product-types.index') }}">
        <i class="bi bi-list-task me-2"></i> Product Types
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'product-makers') ? 'active' : '' }}"
       href="{{ route('product-makers.index') }}">
        <i class="bi bi-person-badge me-2"></i> Product Makers
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'locations') ? 'active' : '' }}"
       href="{{ route('locations.index') }}">
        <i class="bi bi-geo-alt me-2"></i> Locations
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'units') ? 'active' : '' }}"
       href="{{ route('units.index') }}">
        <i class="bi bi-ruler me-2"></i> Units
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'customers') ? 'active' : '' }}"
       href="{{ route('customers.index') }}">
        <i class="bi bi-people me-2"></i> Customers
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'customer-groups') ? 'active' : '' }}"
       href="{{ route('customer-groups.index') }}">
        <i class="bi bi-people-fill me-2"></i> Customer Groups
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'suppliers') ? 'active' : '' }}"
       href="{{ route('suppliers.index') }}">
        <i class="bi bi-truck me-2"></i> Suppliers
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'branches') ? 'active' : '' }}"
       href="{{ route('branches.index') }}">
        <i class="bi bi-building me-2"></i> Branches
    </a>
    <a x-data="{ active: '{{ $activeRoute }}' }"
       class="nav-link mb-1 {{ str_contains($activeRoute, 'services') ? 'active' : '' }}"
       href="{{ route('services.index') }}">
        <i class="bi bi-wrench me-2"></i> Services
    </a>
</div>
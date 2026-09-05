 @php
     $lowStockProducts = collect();
     $lowStockLocations = collect();
     if (auth()->check() && auth()->user()->hasPermission('master.product_stocks.view')) {
         $lowStockProducts = \App\Models\Product::query()
             ->where('is_active', true)
             ->whereNotNull('stock_min')
             ->whereColumn('stock_global', '<=', 'stock_min')
             ->orderBy('stock_global')
             ->limit(5)
             ->get(['id', 'name', 'product_code', 'stock_global', 'stock_min', 'sale_unit']);
         $lowStockLocations = \App\Models\ProductStock::query()
             ->with(['product:id,name,product_code,sale_unit', 'location:id,name'])
             ->whereNotNull('stock_min')
             ->whereColumn('quantity', '<=', 'stock_min')
             ->orderBy('quantity')
             ->limit(5)
             ->get();
     }
     $lowStockCount = $lowStockProducts->count() + $lowStockLocations->count();
 @endphp

 <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
      id="layout-navbar">
     {{-- Mobile Sidebar Toggle --}}
     <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
         <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
             <i class="icon-base bx bx-menu icon-md"></i>
         </a>
     </div>

     {{-- Desktop Sidebar Toggle --}}
     <div class="sidebar-toggle-desktop navbar-nav align-items-xl-center me-4 me-xl-0 d-none d-xl-block">
         <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)" id="sidebar-toggle-btn"
             title="Toggle Sidebar">
             <i class="icon-base bx bx-menu icon-md"></i>
         </a>
     </div>

     <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
         {{-- Search --}}
         <div class="navbar-nav align-items-center me-auto">
             <div class="nav-item d-flex align-items-center position-relative">
                 <span class="w-px-22 h-px-22"><i class="icon-base bx bx-search icon-md"></i></span>
                 <input type="text" id="menu-search"
                     class="form-control border-0 shadow-none ps-1 ps-sm-2 d-md-block d-none rounded-lg"
                     placeholder="Search menu..." aria-label="Search menu..." autocomplete="off" autofocus />

                 {{-- Search Results Dropdown --}}
                 <div id="search-results" class="dropdown-menu search-dropdown d-none"
                     style="width: 320px; max-height: 400px; overflow-y: auto;">
                     <div class="dropdown-header d-flex justify-content-between align-items-center">
                         <small class="text-muted fw-semibold">Search Results</small>
                         <small class="text-muted">
                             <i class="bx bx-info-circle me-1"></i>
                             <span class="d-none d-sm-inline">Use arrow keys to navigate</span>
                         </small>
                     </div>
                     <div id="search-results-content">
                         {{-- Results populated by JS --}}
                     </div>
                     <div id="no-results" class="dropdown-item-text text-center text-muted py-3 d-none">
                         <i class="bx bx-search-alt-2 me-2"></i>No results found
                         <div class="mt-1">
                             <small>Try searching for: users, roles, dashboard, logs</small>
                         </div>
                     </div>
                     <div class="dropdown-divider my-2"></div>
                     <div class="dropdown-item-text">
                         <small class="text-muted">
                             <i class="bx bx-key me-1"></i>
                             Press <kbd class="bg-light border px-1 rounded">Ctrl+K</kbd> to focus search
                         </small>
                     </div>
                 </div>
             </div>
         </div>

         {{-- Right Actions --}}
         <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            {{-- Home --}}
            <li class="nav-item me-2">
                <a class="nav-link px-2" href="{{ route('home') }}" title="Dashboard">
                    <i class="icon-base bx bx-home icon-md"></i>
                </a>
            </li>

            {{-- Quick Add Product (contextual) --}}
            @if (request()->routeIs('products.*') && !request()->routeIs('products.create') && auth()->user()->hasPermission('master.products.create'))
                <li class="nav-item me-2">
                    <a class="nav-link px-2" href="{{ route('products.create') }}" title="Tambah Produk">
                        <i class="icon-base bx bx-plus icon-md"></i>
                    </a>
                </li>
            @endif

            {{-- Stock Notifications --}}
             @if(auth()->user()->hasPermission('master.product_stocks.view'))
                 <li class="nav-item navbar-dropdown dropdown me-3">
                     <a class="nav-link dropdown-toggle hide-arrow position-relative px-2" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Notifikasi stok minimum">
                         <i class="icon-base bx bx-bell icon-md"></i>
                         @if($lowStockCount > 0)
                             <span class="badge rounded-pill bg-danger position-absolute stock-notification-count">
                                 {{ $lowStockCount > 99 ? '99+' : $lowStockCount }}
                             </span>
                         @endif
                     </a>
                     <ul class="dropdown-menu dropdown-menu-end" style="width: 360px;">
                         <li class="px-3 py-2 border-bottom">
                             <div class="fw-semibold">Notifikasi Stok Minimum</div>
                             <small class="text-muted">
                                 {{ auth()->user()->hasPermission('master.product_stocks.edit') ? 'Klik item untuk buat purchase order.' : 'Klik item untuk melihat stok lokasi.' }}
                             </small>
                         </li>
                         @foreach($lowStockLocations as $lowStockLocation)
                             <li>
                                 <a class="dropdown-item py-3" href="{{ auth()->user()->hasPermission('master.product_stocks.edit') ? route('purchase-orders.create', ['product_id' => $lowStockLocation->product_id, 'location_id' => $lowStockLocation->location_id]) : route('products.stocks.index', $lowStockLocation->product_id) }}">
                                     <div class="d-flex align-items-start gap-3">
                                         <div class="avatar flex-shrink-0">
                                             <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-map"></i></span>
                                         </div>
                                         <div class="flex-grow-1">
                                             <div class="fw-semibold text-wrap">{{ $lowStockLocation->product?->name }}</div>
                                             <small class="text-muted d-block">{{ $lowStockLocation->location?->name }} - {{ $lowStockLocation->product?->product_code }}</small>
                                             <small class="text-danger">
                                                 Stok {{ rtrim(rtrim(number_format((float) $lowStockLocation->quantity, 2, ',', '.'), '0'), ',') }}
                                                 / Min {{ rtrim(rtrim(number_format((float) $lowStockLocation->stock_min, 2, ',', '.'), '0'), ',') }}
                                                 {{ $lowStockLocation->product?->sale_unit ?: 'PCS' }}
                                             </small>
                                         </div>
                                     </div>
                                 </a>
                             </li>
                         @endforeach
                         @foreach($lowStockProducts as $lowStockProduct)
                             <li>
                                 <a class="dropdown-item py-3" href="{{ auth()->user()->hasPermission('master.product_stocks.edit') ? route('purchase-orders.create', ['product_id' => $lowStockProduct->id]) : route('products.stocks.index', $lowStockProduct->id) }}">
                                     <div class="d-flex align-items-start gap-3">
                                         <div class="avatar flex-shrink-0">
                                             <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-package"></i></span>
                                         </div>
                                         <div class="flex-grow-1">
                                             <div class="fw-semibold text-wrap">{{ $lowStockProduct->name }}</div>
                                             <small class="text-muted d-block">{{ $lowStockProduct->product_code }}</small>
                                             <small class="text-danger">
                                                 Stok {{ rtrim(rtrim(number_format((float) $lowStockProduct->stock_global, 2, ',', '.'), '0'), ',') }}
                                                 / Min {{ rtrim(rtrim(number_format((float) $lowStockProduct->stock_min, 2, ',', '.'), '0'), ',') }}
                                                 {{ $lowStockProduct->sale_unit ?: 'PCS' }}
                                             </small>
                                         </div>
                                     </div>
                                 </a>
                             </li>
                         @endforeach
                         @if($lowStockCount === 0)
                             <li>
                                 <div class="dropdown-item-text text-center text-muted py-4">
                                     Tidak ada stok di bawah minimum.
                                 </div>
                             </li>
                         @endif
                         @if($lowStockCount > 0)
                             <li><div class="dropdown-divider my-1"></div></li>
                             <li>
                                 <a class="dropdown-item text-center" href="{{ route('products.index') }}">Lihat produk</a>
                             </li>
                         @endif
                     </ul>
                 </li>
             @endif

             {{-- Theme Toggle --}}
             <li class="nav-item me-3">
                 <button type="button" class="btn theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                     <i class="theme-toggle-icon bx bx-moon"></i>
                 </button>
             </li>

             {{-- User Dropdown --}}
             <li class="nav-item navbar-dropdown dropdown-user dropdown">
                 <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                     data-bs-toggle="dropdown">
                     <div class="avatar avatar-online">
                         <img src="{{ Avatar::create(Auth::user()->name) }}" alt
                             class="w-px-40 h-auto rounded-circle" />
                     </div>
                 </a>
                 <ul class="dropdown-menu dropdown-menu-end">
                     <li>
                         <a class="dropdown-item" href="#">
                             <div class="d-flex">
                                 <div class="flex-shrink-0 me-3">
                                     <div class="avatar avatar-online">
                                         <img src="{{ Avatar::create(Auth::user()->name) }}" alt
                                             class="w-px-40 h-auto rounded-circle" />
                                     </div>
                                 </div>
                                 <div class="flex-grow-1">
                                     <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                     <small class="text-body-secondary">
                                         @forelse (Auth::user()->roles as $role)
                                             <span class="badge bg-label-primary">{{ $role->display_name }}</span>
                                             <br>
                                         @empty
                                         @endforelse
                                     </small>
                                 </div>
                             </div>
                         </a>
                     </li>
                     <li>
                         <div class="dropdown-divider my-1"></div>
                     </li>
                     <li>
                         <a class="dropdown-item" href="{{ route('profile.password.edit') }}">
                             <i class="icon-base bx bx-key icon-md me-3"></i><span>Ganti Password</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item d-none" href="#">
                             <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span>
                         </a>
                     </li>
                     <li>
                         <a class="dropdown-item d-none" href="#">
                             <span class="d-flex align-items-center align-middle">
                                 <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i><span
                                     class="flex-grow-1 align-middle">Billing Plan</span>
                                 <span class="flex-shrink-0 badge rounded-pill bg-danger">4</span>
                             </span>
                         </a>
                     </li>
                     <li>
                         <div class="dropdown-divider my-1 d-none"></div>
                     </li>
                     <li>
                         <a class="dropdown-item" href="{{ route('logout') }}"
                             onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                             <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span>
                         </a>
                         <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                             @csrf
                         </form>
                     </li>
                 </ul>
             </li>
         </ul>
     </div>
 </nav>
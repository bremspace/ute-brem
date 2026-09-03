 <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
     <div class="app-brand demo">
         <a href="{{ route('home') }}" class="app-brand-link">
             <span class="app-brand-text fw-bold text-uppercase text-primary" style="letter-spacing: 0.08em;">
                 {{ $appCompanyName ?? 'UTE Parts' }}
             </span>
         </a>

         <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
             <i class="bx bx-chevron-left d-block d-xl-none align-middle"></i>
         </a>
     </div>

     <div class="menu-divider mt-0"></div>

     <div class="menu-inner-shadow"></div>

     <ul class="menu-inner py-1">
         <!-- Dashboard -->
         <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
             <a href="{{ route('home') }}" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-home-smile"></i>
                 <div class="text-truncate" data-i18n="Dashboard">Dashboard</div>
             </a>
         </li>

         <li class="menu-item">
             <a href="{{ route('website.products.index') }}" target="_blank" class="menu-link">
                 <i class="menu-icon tf-icons bx bx-globe"></i>
                 <div class="text-truncate">Lihat Website</div>
             </a>
         </li>

         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Master Transaksi</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('transactions.view') || auth()->user()->hasPermission('transactions.create'))
             @php
                 $canViewTransactions = auth()->user()->hasPermission('transactions.view');
                 $saleChannel = request()->route('saleChannel') ?: 'toko';
                 $isTransactionOpen = request()->routeIs('transactions.*') || request()->routeIs('service-transactions.*');
                 $saleChannelHref = fn (string $channel) => $canViewTransactions
                     ? route('transactions.index.channel', $channel)
                     : route('transactions.create.channel', $channel);
             @endphp
             <li class="menu-item {{ $isTransactionOpen ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-receipt"></i>
                     <div class="text-truncate">Transaksi</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ (request()->routeIs('transactions.index', 'transactions.show', 'transactions.receipt', 'transactions.create') && $saleChannel === 'toko') || (request()->routeIs('transactions.index.channel', 'transactions.create.channel') && $saleChannel === 'toko') ? 'active' : '' }}">
                         <a href="{{ $saleChannelHref('toko') }}" class="menu-link">
                             <div class="text-truncate">Penjualan Toko</div>
                         </a>
                     </li>
                     @if (auth()->user()->hasPermission('transactions.view') || auth()->user()->hasPermission('transactions.create'))
                         <li class="menu-item {{ request()->routeIs('transactions.index.channel', 'transactions.create.channel') && $saleChannel === 'cabang' ? 'active' : '' }}">
                             <a href="{{ $saleChannelHref('cabang') }}" class="menu-link">
                                 <div class="text-truncate">Penjualan Cabang</div>
                             </a>
                         </li>
                         <li class="menu-item {{ request()->routeIs('transactions.index.channel', 'transactions.create.channel') && $saleChannel === 'partai' ? 'active' : '' }}">
                             <a href="{{ $saleChannelHref('partai') }}" class="menu-link">
                                 <div class="text-truncate">Penjualan Partai</div>
                             </a>
                         </li>
                     @endif
                     <li class="menu-item {{ request()->routeIs('service-transactions.*') ? 'active' : '' }}">
                         <a href="{{ route('service-transactions.index') }}" class="menu-link">
                             <div class="text-truncate">Transaksi Services</div>
                         </a>
                     </li>
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.product_stocks.view'))
             <li class="menu-item {{ request()->routeIs('purchase-orders.*') ? 'active' : '' }}">
                 <a href="{{ route('purchase-orders.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-receipt"></i>
                     <div class="text-truncate">Purchase Order</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('branch-transfers.*') ? 'active' : '' }}">
                 <a href="{{ route('branch-transfers.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-transfer-alt"></i>
                     <div class="text-truncate">Transfer Cabang</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
                 <a href="{{ route('stock-opname.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-clipboard"></i>
                     <div class="text-truncate">Stock Opname</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('picking-requests.*') ? 'active' : '' }}">
                 <a href="{{ route('picking-requests.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-cart-add"></i>
                     <div class="text-truncate">Picking Request</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('item-serials.*') ? 'active' : '' }}">
                 <a href="{{ route('item-serials.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                     <div class="text-truncate">Serial & Bin Tracking</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                 <a href="{{ route('reports.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                     <div class="text-truncate">Laporan</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('purchase-orders.restock') ? 'active' : '' }}">
                 <a href="{{ route('purchase-orders.restock') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-cart-download"></i>
                     <div class="text-truncate">Rekomendasi Restock</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('accounting.access'))
             <li class="menu-item {{ request()->routeIs('accounting.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-book-open"></i>
                     <div class="text-truncate">Akuntansi</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('accounting.ledger', 'accounting.index') ? 'active' : '' }}">
                         <a href="{{ route('accounting.ledger') }}" class="menu-link"><div class="text-truncate">Buku Besar</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.journal') ? 'active' : '' }}">
                         <a href="{{ route('accounting.journal') }}" class="menu-link"><div class="text-truncate">Jurnal Umum</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.chart') ? 'active' : '' }}">
                         <a href="{{ route('accounting.chart') }}" class="menu-link"><div class="text-truncate">Bagan Akun</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.trial_balance') ? 'active' : '' }}">
                         <a href="{{ route('accounting.trial_balance') }}" class="menu-link"><div class="text-truncate">Neraca Saldo</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.profit_loss') ? 'active' : '' }}">
                         <a href="{{ route('accounting.profit_loss') }}" class="menu-link"><div class="text-truncate">Laba Rugi</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.balance_sheet') ? 'active' : '' }}">
                         <a href="{{ route('accounting.balance_sheet') }}" class="menu-link"><div class="text-truncate">Neraca</div></a>
                     </li>
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Back Office</span>
             </li>

             <li class="menu-item {{ request()->routeIs('back-office.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-briefcase"></i>
                     <div class="text-truncate">Back Office</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('back-office.dashboard') ? 'active' : '' }}">
                         <a href="{{ route('back-office.dashboard') }}" class="menu-link">
                             <div class="text-truncate">Ringkasan</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-transactions.*') && request()->route('type') === 'income' ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-transactions.index', 'income') }}" class="menu-link">
                             <div class="text-truncate">Pemasukan</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-transactions.*') && request()->route('type') === 'expense' ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-transactions.index', 'expense') }}" class="menu-link">
                             <div class="text-truncate">Pengeluaran</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.employee-advances.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.employee-advances.index') }}" class="menu-link">
                             <div class="text-truncate">Kasbon Karyawan</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-mutations.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-mutations.index') }}" class="menu-link">
                             <div class="text-truncate">Mutasi Kas</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.stock-documents.*') && request()->route('type') === 'correction' ? 'active' : '' }}">
                         <a href="{{ route('back-office.stock-documents.index', 'correction') }}" class="menu-link">
                             <div class="text-truncate">Koreksi Stok</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.stock-documents.*') && request()->route('type') === 'usage' ? 'active' : '' }}">
                         <a href="{{ route('back-office.stock-documents.index', 'usage') }}" class="menu-link">
                             <div class="text-truncate">Pemakaian Barang</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-accounts.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-accounts.index') }}" class="menu-link">
                             <div class="text-truncate">Master Data Kas</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cost-categories.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.cost-categories.index') }}" class="menu-link">
                             <div class="text-truncate">Master Data Biaya</div>
                         </a>
                     </li>
                     @if (auth()->user()->hasPermission('master.products.view'))
                         <li class="menu-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                             <a href="{{ route('services.index') }}" class="menu-link">
                                 <div class="text-truncate">Master Jasa</div>
                             </a>
                         </li>
                     @endif
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Master Data</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                 <a href="{{ route('products.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-package"></i>
                     <div class="text-truncate" data-i18n="Products">Produk</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                 <a href="{{ route('services.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-wrench"></i>
                     <div class="text-truncate">Jasa</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.branches.view'))
             <li class="menu-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                 <a href="{{ route('branches.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-store-alt"></i>
                     <div class="text-truncate">Cabang</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.suppliers.view') || auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                 <a href="{{ route('suppliers.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-store"></i>
                     <div class="text-truncate">Supplier</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.categories.view'))
             <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                 <a href="{{ route('categories.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-category"></i>
                     <div class="text-truncate">Kategori</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.sub_categories.view'))
             <li class="menu-item {{ request()->routeIs('sub-categories.*') ? 'active' : '' }}">
                 <a href="{{ route('sub-categories.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-category-alt"></i>
                     <div class="text-truncate">Sub Kategori</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.brands.view'))
             <li class="menu-item {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                 <a href="{{ route('brands.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-tag"></i>
                     <div class="text-truncate">Brand</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('product-makers.*') ? 'active' : '' }}">
                 <a href="{{ route('product-makers.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-cog"></i>
                     <div class="text-truncate">Product Maker</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('units.*') ? 'active' : '' }}">
                 <a href="{{ route('units.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-move-horizontal"></i>
                     <div class="text-truncate">Satuan (Unit)</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.product_types.view'))
             <li class="menu-item {{ request()->routeIs('product-types.*') ? 'active' : '' }}">
                 <a href="{{ route('product-types.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-mobile-alt"></i>
                     <div class="text-truncate">Tipe Produk</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.locations.view'))
             <li class="menu-item {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                 <a href="{{ route('locations.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-map-pin"></i>
                     <div class="text-truncate">Lokasi & Rak</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.customer_groups.view'))
             <li class="menu-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                 <a href="{{ route('customers.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-user-pin"></i>
                     <div class="text-truncate">Customer</div>
                 </a>
             </li>

             <li class="menu-item {{ request()->routeIs('customer-groups.*') ? 'active' : '' }}">
                 <a href="{{ route('customer-groups.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-group"></i>
                     <div class="text-truncate">Customer Group</div>
                 </a>
             </li>
         @endif

         <!-- Management -->
         <li class="menu-header small text-uppercase">
             <span class="menu-header-text">Management</span>
         </li>

         <!-- Users -->
         @if (auth()->user()->hasPermission('management.users.view'))
             <li class="menu-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                 <a href="{{ route('users.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-user"></i>
                     <div class="text-truncate" data-i18n="Users">Users</div>
                 </a>
             </li>
         @endif

         <!-- Roles (RBAC) -->
         @if (auth()->user()->hasPermission('management.roles.view'))
             <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                 <a href="{{ route('roles.index') }}" class="menu-link">
                     <i class="menu-icon tf-icons bx bx-shield"></i>
                     <div class="text-truncate" data-i18n="Roles">Role Templates</div>
                 </a>
             </li>
         @endif

        @if (auth()->user()->hasPermission('management.settings.printer'))
            <li class="menu-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                <a href="{{ route('settings.printer.edit') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div class="text-truncate">Setting</div>
                </a>
            </li>
        @endif

     </ul>
 </aside>

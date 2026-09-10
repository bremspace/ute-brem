 <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
     {{-- Brand Header --}}
     <div class="app-brand demo">
         <a href="{{ route('home') }}" wire:navigate class="app-brand-link">
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

         {{-- ═══════ NAVIGASI ═══════ --}}
         <li class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
             <a href="{{ route('home') }}" wire:navigate class="menu-link">
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

         {{-- ═══════ PENJUALAN & LAYANAN ═══════ --}}
         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Penjualan & Layanan</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('transactions.view') || auth()->user()->hasPermission('transactions.create'))
             @php
                 $canViewTx = auth()->user()->hasPermission('transactions.view');
                 $saleChannel = request()->route('saleChannel') ?: 'toko';
                 $isTxOpen = request()->routeIs('transactions.*') || request()->routeIs('service-transactions.*');
                 $saleChannelHref = fn (string $ch) => $canViewTx
                     ? route('transactions.index.channel', $ch)
                     : route('transactions.create.channel', $ch);
             @endphp
             <li class="menu-item {{ $isTxOpen ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-receipt"></i>
                     <div class="text-truncate">Transaksi Penjualan</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ (request()->routeIs('transactions.create') || request()->routeIs('transactions.create.channel')) && $saleChannel === 'toko' ? 'active' : '' }}">
                         <a href="{{ route('transactions.create.channel', 'toko') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Kasir Toko</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('transactions.index.channel') && $saleChannel === 'toko' ? 'active' : '' }}">
                         <a href="{{ $saleChannelHref('toko') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Riwayat Toko</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('transactions.create.channel') && $saleChannel === 'cabang' ? 'active' : '' }}">
                         <a href="{{ route('transactions.create.channel', 'cabang') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Kasir Cabang</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('transactions.index.channel') && $saleChannel === 'cabang' ? 'active' : '' }}">
                         <a href="{{ $saleChannelHref('cabang') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Riwayat Cabang</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('transactions.create.channel') && $saleChannel === 'partai' ? 'active' : '' }}">
                         <a href="{{ route('transactions.create.channel', 'partai') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Kasir Partai</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('transactions.index.channel') && $saleChannel === 'partai' ? 'active' : '' }}">
                         <a href="{{ $saleChannelHref('partai') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Riwayat Partai</div>
                         </a>
                     </li>
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('transactions.view') || auth()->user()->hasPermission('transactions.create'))
             <li class="menu-item {{ request()->routeIs('service-transactions.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-wrench"></i>
                     <div class="text-truncate">Service Center</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('service-transactions.create') ? 'active' : '' }}">
                         <a href="{{ route('service-transactions.create') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Buat Servis Baru</div>
                         </a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('service-transactions.index') ? 'active' : '' }}">
                         <a href="{{ route('service-transactions.index') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Riwayat Servis</div>
                         </a>
                     </li>
                 </ul>
             </li>
         @endif

         {{-- ═══════ MASTER PRODUK & SUPPLIER ═══════ --}}
         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Master Data</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('products.*') || request()->routeIs('livewire.products.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-package"></i>
                     <div class="text-truncate">Produk</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('livewire.products.*') ? 'active' : '' }}">
                         <a href="{{ route('livewire.products.index') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Daftar Produk</div>
                         </a>
                     </li>
                     @if (auth()->user()->hasPermission('master.products.create'))
                         <li class="menu-item {{ request()->routeIs('products.create') ? 'active' : '' }}">
                             <a href="{{ route('products.create') }}" wire:navigate class="menu-link">
                                 <div class="text-truncate">Tambah Produk</div>
                             </a>
                         </li>
                         <li class="menu-item {{ request()->routeIs('products.import') ? 'active' : '' }}">
                             <a href="{{ route('products.import') }}" wire:navigate class="menu-link">
                                 <div class="text-truncate">Import Produk</div>
                             </a>
                         </li>
                     @endif
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.suppliers.view') || auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('suppliers.*') || request()->routeIs('livewire.suppliers.*') ? 'active' : '' }}">
                 <a href="{{ route('livewire.suppliers.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-store"></i>
                     <div class="text-truncate">Supplier</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                 <a href="{{ route('services.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-wrench"></i>
                     <div class="text-truncate">Master Jasa</div>
                 </a>
             </li>
         @endif

         {{-- ═══════ PELENGKAP PRODUK ═══════ --}}
         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Pelengkap Produk</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.categories.view'))
             <li class="menu-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                 <a href="{{ route('categories.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-category"></i>
                     <div class="text-truncate">Kategori</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.sub_categories.view'))
             <li class="menu-item {{ request()->routeIs('sub-categories.*') ? 'active' : '' }}">
                 <a href="{{ route('sub-categories.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-category-alt"></i>
                     <div class="text-truncate">Sub Kategori</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.brands.view'))
             <li class="menu-item {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                 <a href="{{ route('brands.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-tag"></i>
                     <div class="text-truncate">Brand</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.product_types.view'))
             <li class="menu-item {{ request()->routeIs('product-types.*') ? 'active' : '' }}">
                 <a href="{{ route('product-types.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-mobile-alt"></i>
                     <div class="text-truncate">Tipe HP</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.products.view'))
             <li class="menu-item {{ request()->routeIs('product-makers.*') ? 'active' : '' }}">
                 <a href="{{ route('product-makers.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-cog"></i>
                     <div class="text-truncate">Merek (Maker)</div>
                 </a>
             </li>
             <li class="menu-item {{ request()->routeIs('units.*') ? 'active' : '' }}">
                 <a href="{{ route('units.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-move-horizontal"></i>
                     <div class="text-truncate">Satuan</div>
                 </a>
             </li>
         @endif

         {{-- ═══════ PELANGGAN ═══════ --}}
         @if (auth()->user()->hasPermission('master.customer_groups.view'))
             <li class="menu-item {{ request()->routeIs('customers.*') || request()->routeIs('livewire.customers.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-user"></i>
                     <div class="text-truncate">Pelanggan</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('livewire.customers.*') ? 'active' : '' }}">
                         <a href="{{ route('livewire.customers.index') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Daftar Pelanggan</div>
                         </a>
                     </li>
                     @if (auth()->user()->hasPermission('master.customer_groups.create'))
                         <li class="menu-item {{ request()->routeIs('customers.create') ? 'active' : '' }}">
                             <a href="{{ route('customers.create') }}" wire:navigate class="menu-link">
                                 <div class="text-truncate">Tambah Pelanggan</div>
                             </a>
                         </li>
                     @endif
                     <li class="menu-item {{ request()->routeIs('customer-groups.*') ? 'active' : '' }}">
                         <a href="{{ route('customer-groups.index') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Customer Group</div>
                         </a>
                     </li>
                 </ul>
             </li>
         @endif

         {{-- ═══════ INVENTARIS & GUDANG ═══════ --}}
         @if (auth()->user()->hasPermission('master.product_stocks.view'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Inventaris & Gudang</span>
             </li>

             <li class="menu-item {{ request()->routeIs('purchase-orders.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-cart-download"></i>
                     <div class="text-truncate">Pembelian</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('purchase-orders.index') ? 'active' : '' }}">
                         <a href="{{ route('purchase-orders.index') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Daftar PO</div>
                         </a>
                     </li>
                     @if (auth()->user()->hasPermission('master.product_stocks.edit'))
                         <li class="menu-item {{ request()->routeIs('purchase-orders.create') ? 'active' : '' }}">
                             <a href="{{ route('purchase-orders.create') }}" wire:navigate class="menu-link">
                                 <div class="text-truncate">Buat PO</div>
                             </a>
                         </li>
                     @endif
                     <li class="menu-item {{ request()->routeIs('purchase-orders.restock') ? 'active' : '' }}">
                         <a href="{{ route('purchase-orders.restock') }}" wire:navigate class="menu-link">
                             <div class="text-truncate">Rekomendasi Restock</div>
                         </a>
                     </li>
                 </ul>
             </li>

             <li class="menu-item {{ request()->routeIs('branch-transfers.*') ? 'active' : '' }}">
                 <a href="{{ route('branch-transfers.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-transfer-alt"></i>
                     <div class="text-truncate">Transfer Cabang</div>
                 </a>
             </li>

             <li class="menu-item {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
                 <a href="{{ route('stock-opname.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-clipboard"></i>
                     <div class="text-truncate">Stock Opname</div>
                 </a>
             </li>

             <li class="menu-item {{ request()->routeIs('picking-requests.*') ? 'active' : '' }}">
                 <a href="{{ route('picking-requests.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-cart"></i>
                     <div class="text-truncate">Picking Request</div>
                 </a>
             </li>

             <li class="menu-item {{ request()->routeIs('item-serials.*') ? 'active' : '' }}">
                 <a href="{{ route('item-serials.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-qr-scan"></i>
                     <div class="text-truncate">Serial Number</div>
                 </a>
             </li>
         @endif

         {{-- ═══════ MULTI-LOKASI ═══════ --}}
         @if (auth()->user()->hasPermission('master.branches.view'))
             <li class="menu-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
                 <a href="{{ route('branches.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-store-alt"></i>
                     <div class="text-truncate">Cabang</div>
                 </a>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.locations.view'))
             <li class="menu-item {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                 <a href="{{ route('locations.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-map-pin"></i>
                     <div class="text-truncate">Lokasi & Rak</div>
                 </a>
             </li>
         @endif

         {{-- ═══════ LAPORAN ═══════ --}}
         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Laporan & Keuangan</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-item {{ request()->routeIs('reports.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-bar-chart-alt-2"></i>
                     <div class="text-truncate">Laporan</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
                         <a href="{{ route('reports.index') }}" wire:navigate class="menu-link"><div class="text-truncate">Ringkasan</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                         <a href="{{ route('reports.sales') }}" wire:navigate class="menu-link"><div class="text-truncate">Penjualan</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">
                         <a href="{{ route('reports.purchases') }}" wire:navigate class="menu-link"><div class="text-truncate">Pembelian</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.stocks') ? 'active' : '' }}">
                         <a href="{{ route('reports.stocks') }}" wire:navigate class="menu-link"><div class="text-truncate">Stok</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.cash') ? 'active' : '' }}">
                         <a href="{{ route('reports.cash') }}" wire:navigate class="menu-link"><div class="text-truncate">Kas & Cashflow</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.receivables') ? 'active' : '' }}">
                         <a href="{{ route('reports.receivables') }}" wire:navigate class="menu-link"><div class="text-truncate">Piutang</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.profit_loss') ? 'active' : '' }}">
                         <a href="{{ route('reports.profit_loss') }}" wire:navigate class="menu-link"><div class="text-truncate">Laba Rugi</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('reports.services') ? 'active' : '' }}">
                         <a href="{{ route('reports.services') }}" wire:navigate class="menu-link"><div class="text-truncate">Layanan</div></a>
                     </li>
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('accounting.access'))
             <li class="menu-item {{ request()->routeIs('accounting.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-book-open"></i>
                     <div class="text-truncate">Akuntansi</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('accounting.chart') ? 'active' : '' }}">
                         <a href="{{ route('accounting.chart') }}" wire:navigate class="menu-link"><div class="text-truncate">Bagan Akun</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.journal') ? 'active' : '' }}">
                         <a href="{{ route('accounting.journal') }}" wire:navigate class="menu-link"><div class="text-truncate">Jurnal Umum</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.ledger', 'accounting.index') ? 'active' : '' }}">
                         <a href="{{ route('accounting.ledger') }}" wire:navigate class="menu-link"><div class="text-truncate">Buku Besar</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.trial_balance') ? 'active' : '' }}">
                         <a href="{{ route('accounting.trial_balance') }}" wire:navigate class="menu-link"><div class="text-truncate">Neraca Saldo</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.profit_loss') ? 'active' : '' }}">
                         <a href="{{ route('accounting.profit_loss') }}" wire:navigate class="menu-link"><div class="text-truncate">Laba Rugi</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('accounting.balance_sheet') ? 'active' : '' }}">
                         <a href="{{ route('accounting.balance_sheet') }}" wire:navigate class="menu-link"><div class="text-truncate">Neraca</div></a>
                     </li>
                 </ul>
             </li>
         @endif

         {{-- ═══════ BACK OFFICE ═══════ --}}
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
                         <a href="{{ route('back-office.dashboard') }}" wire:navigate class="menu-link"><div class="text-truncate">Ringkasan</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-transactions.*') && request()->route('type') === 'income' ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-transactions.index', 'income') }}" wire:navigate class="menu-link"><div class="text-truncate">Pemasukan</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-transactions.*') && request()->route('type') === 'expense' ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-transactions.index', 'expense') }}" wire:navigate class="menu-link"><div class="text-truncate">Pengeluaran</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.employee-advances.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.employee-advances.index') }}" wire:navigate class="menu-link"><div class="text-truncate">Kasbon Karyawan</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-mutations.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-mutations.index') }}" wire:navigate class="menu-link"><div class="text-truncate">Mutasi Kas</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.stock-documents.*') && request()->route('type') === 'correction' ? 'active' : '' }}">
                         <a href="{{ route('back-office.stock-documents.index', 'correction') }}" wire:navigate class="menu-link"><div class="text-truncate">Koreksi Stok</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.stock-documents.*') && request()->route('type') === 'usage' ? 'active' : '' }}">
                         <a href="{{ route('back-office.stock-documents.index', 'usage') }}" wire:navigate class="menu-link"><div class="text-truncate">Pemakaian Barang</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cash-accounts.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.cash-accounts.index') }}" wire:navigate class="menu-link"><div class="text-truncate">Akun Kas & Bank</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('back-office.cost-categories.*') ? 'active' : '' }}">
                         <a href="{{ route('back-office.cost-categories.index') }}" wire:navigate class="menu-link"><div class="text-truncate">Kategori Biaya</div></a>
                     </li>
                 </ul>
             </li>
         @endif

         {{-- ═══════ PENGELOLAAN ═══════ --}}
         @if (auth()->user()->hasPermission('management.users.view') || auth()->user()->hasPermission('management.roles.view') || auth()->user()->hasPermission('management.settings.printer'))
             <li class="menu-header small text-uppercase">
                 <span class="menu-header-text">Pengelolaan</span>
             </li>
         @endif

         @if (auth()->user()->hasPermission('management.users.view'))
             <li class="menu-item {{ request()->routeIs('users.*') ? 'active open' : '' }}">
                 <a href="javascript:void(0);" class="menu-link menu-toggle">
                     <i class="menu-icon tf-icons bx bx-user"></i>
                     <div class="text-truncate">User & Hak Akses</div>
                 </a>
                 <ul class="menu-sub">
                     <li class="menu-item {{ request()->routeIs('users.index') ? 'active' : '' }}">
                         <a href="{{ route('users.index') }}" wire:navigate class="menu-link"><div class="text-truncate">Daftar User</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('users.logs') ? 'active' : '' }}">
                         <a href="{{ route('users.logs') }}" wire:navigate class="menu-link"><div class="text-truncate">Log Aktivitas</div></a>
                     </li>
                     <li class="menu-item {{ request()->routeIs('users.trash') ? 'active' : '' }}">
                         <a href="{{ route('users.trash') }}" wire:navigate class="menu-link"><div class="text-truncate">Sampah User</div></a>
                     </li>
                 </ul>
             </li>
         @endif

         @if (auth()->user()->hasPermission('management.roles.view'))
             <li class="menu-item {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                 <a href="{{ route('roles.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-shield"></i>
                     <div class="text-truncate">Role & Permission</div>
                 </a>
             </li>
         @endif

          @if (auth()->user()->hasPermission('management.settings.printer'))
              <li class="menu-item {{ request()->routeIs('settings.*') && !request()->routeIs('settings.website') ? 'active' : '' }}">
                  <a href="{{ route('settings.printer.edit') }}" wire:navigate class="menu-link">
                      <i class="menu-icon tf-icons bx bx-cog"></i>
                      <div class="text-truncate">Pengaturan</div>
                  </a>
              </li>
              <li class="menu-item {{ request()->routeIs('settings.website') ? 'active' : '' }}">
                  <a href="{{ route('settings.website') }}" wire:navigate class="menu-link">
                      <i class="menu-icon tf-icons bx bx-globe"></i>
                      <div class="text-truncate">Pengaturan Website</div>
                  </a>
              </li>
          @endif

         @if (auth()->user()->hasPermission('master.access'))
             <li class="menu-item {{ request()->routeIs('sid-retail.*') ? 'active' : '' }}">
                 <a href="{{ route('sid-retail.index') }}" wire:navigate class="menu-link">
                     <i class="menu-icon tf-icons bx bx-data"></i>
                     <div class="text-truncate">Migrasi SID Retail</div>
                 </a>
             </li>
         @endif

     </ul>
 </aside>
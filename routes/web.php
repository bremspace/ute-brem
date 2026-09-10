<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BackOfficeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMakerController;
use App\Http\Controllers\ProductQualityController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\BranchTransferController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TablePreferenceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\WebsiteCustomerAuthController;
use App\Http\Controllers\WebsiteProductController;
use App\Http\Controllers\Website\CartController;
use App\Http\Controllers\Website\CheckoutController;
use App\Http\Controllers\Website\OrderController;
use App\Http\Controllers\Website\PaymentController;
use App\Http\Controllers\PrinterSettingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceTransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\PickingRequestController;
use App\Http\Controllers\SidRetail\SidRetailImportController;
use App\Http\Controllers\ItemSerialController;

Route::get('/', [WebsiteProductController::class, 'index'])->name('website.products.index');
Route::get('/products/{slug}', [WebsiteProductController::class, 'show'])->name('website.products.show');
Route::get('/member/login', fn () => view('website.auth.livewire-login'))->name('website.member.login');
Route::post('/member/login', [WebsiteCustomerAuthController::class, 'login'])->name('website.member.login.submit');
Route::get('/member/register', fn () => view('website.auth.livewire-register'))->name('website.member.register');
Route::post('/member/register', [WebsiteCustomerAuthController::class, 'register'])->name('website.member.register.submit');
Route::get('/member/orders', fn () => view('website.member.livewire-orders'))->name('website.member.orders');
Route::get('/member/points', fn () => view('website.member.livewire-points'))->name('website.member.points');
Route::get('/member/password', fn () => view('website.auth.livewire-password'))->name('website.member.password.edit');
Route::post('/member/password', [WebsiteCustomerAuthController::class, 'updatePassword'])->name('website.member.password.update');
Route::post('/member/logout', [WebsiteCustomerAuthController::class, 'logout'])->name('website.member.logout');

// Cart routes (guest cart, no auth middleware)
// GET /cart renders the Livewire CartComponent via a wrapper view.
// The add/update/remove/clear AJAX endpoints are preserved (controllers kept)
// so the route names still resolve; the Livewire component handles these
// operations client-side via component methods.
Route::get('/cart', fn () => view('website.cart.livewire'))->name('website.cart');
Route::post('/cart/add', [CartController::class, 'add'])->name('website.cart.add');
Route::put('/cart/{item}', [CartController::class, 'update'])->name('website.cart.update');
Route::patch('/cart/{item}', [CartController::class, 'update']);
Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('website.cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('website.cart.clear');

// Checkout routes
// GET /checkout renders the Livewire CheckoutComponent via a wrapper view.
// POST /checkout is preserved (controller kept) so the route name still resolves;
// the Livewire component submits orders via CartService::convertToOrder().
Route::get('/checkout', fn () => view('website.checkout.livewire'))->name('website.checkout');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('website.checkout.store');

// Order routes - rendering Livewire wrapper views
Route::get('/orders/{code}', fn ($code) => view('website.orders.livewire-show', ['code' => $code]))->name('website.order.show');
Route::get('/orders/{code}/track', fn ($code) => view('website.orders.livewire-track', ['code' => $code]))->name('website.order.track');

// Payment routes (Phase 2 - DuitKu integration)
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('website.payment.callback');
Route::get('/payment/return', [PaymentController::class, 'return'])->name('website.payment.return');
Route::get('/payment/{code}/status', [PaymentController::class, 'status'])->name('website.payment.status');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Route group middleware for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/profile/password', fn () => view('profile.livewire-password'))->name('profile.password.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // User management routes with permissions
    Route::middleware(['permission:management.users.create'])->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware(['permission:management.users.delete'])->group(function () {
        Route::get('/users/trash', [UserController::class, 'trash'])->name('users.trash');
        Route::get('/users/trash/data', [UserController::class, 'getTrashData'])->name('users.trash.data');
    });

    Route::middleware(['permission:management.users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
        Route::get('/users/logs', [UserController::class, 'logs'])->name('users.logs');
        Route::get('/users/logs/data', [UserController::class, 'getLogsData'])->name('users.logs.data');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/logs', [UserController::class, 'userLogs'])->name('users.user-logs');
        Route::get('/users/{user}/logs/data', [UserController::class, 'getUserLogsData'])->name('users.user-logs.data');
    });

    Route::middleware(['permission:management.users.edit'])->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}', [UserController::class, 'update']);
    });

    Route::middleware(['permission:management.users.permissions'])->group(function () {
        Route::put('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.update-permissions');
        Route::post('/users/{user}/apply-template', [UserController::class, 'applyTemplate'])->name('users.apply-template');
        Route::get('/roles/{role}/permissions', [UserController::class, 'getTemplatePermissions'])->name('roles.permissions');
    });

    Route::middleware(['permission:management.users.delete'])->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    });

    Route::middleware(['permission:management.settings.printer'])->group(function () {
        Route::get('/settings', fn () => view('settings.livewire-settings'))->name('settings.index');
        Route::post('/settings', [PrinterSettingController::class, 'update'])->name('settings.update');
        Route::get('/settings/printer', fn () => redirect()->route('settings.index', ['tab' => 'printer']))->name('settings.printer.edit');
        Route::post('/settings/printer', [PrinterSettingController::class, 'update'])->name('settings.printer.update');
        Route::get('/settings/printer/test-print', [PrinterSettingController::class, 'testPrint'])->name('settings.printer.test-print');
        // Backup and Restore Routes
        Route::post('/settings/backup', [PrinterSettingController::class, 'createBackup'])->name('settings.backup.create');
        Route::post('/settings/backup/restore/{filename}', [PrinterSettingController::class, 'restoreBackup'])->name('settings.backup.restore');
        Route::get('/settings/backup/download/{filename}', [PrinterSettingController::class, 'downloadBackup'])->name('settings.backup.download');
        Route::delete('/settings/backup/delete/{filename}', [PrinterSettingController::class, 'deleteBackup'])->name('settings.backup.delete');
        Route::get('/settings/website', fn () => view('settings.livewire-website-settings'))->name('settings.website');
    });

    Route::middleware(['permission:management.users.restore'])->group(function () {
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    });

    // Role management routes with permissions
    Route::middleware(['permission:management.roles.create'])->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware(['permission:management.roles.view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'getData'])->name('roles.data');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });

    Route::middleware(['permission:management.roles.edit'])->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::patch('/roles/{role}', [RoleController::class, 'update']);
    });

    Route::middleware(['permission:management.roles.delete'])->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // User role assignment routes
    Route::middleware(['permission:management.users.edit'])->group(function () {
        Route::get('/users/{user}/roles', [UserRoleController::class, 'show'])->name('users.roles');
        Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::post('/users/{user}/roles/assign', [UserRoleController::class, 'assignRole'])->name('users.roles.assign');
        Route::delete('/users/{user}/roles/remove', [UserRoleController::class, 'removeRole'])->name('users.roles.remove');
    });

    Route::middleware(['permission:master.categories.view'])->group(function () {
        Route::get('/categories', function () { return view('categories.livewire-index'); })->name('categories.index');
        Route::get('/categories/data', [CategoryController::class, 'getData'])->name('categories.data');
    });

    Route::middleware(['permission:master.categories.create'])->group(function () {
        Route::get('/categories/create', function () { return view('categories.livewire-create'); })->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });

    Route::middleware(['permission:master.categories.edit'])->group(function () {
        Route::get('/categories/{category}/edit', function (\App\Models\Category $category) { return view('categories.livewire-edit', ['category' => $category]); })->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    });

    Route::middleware(['permission:master.categories.delete'])->group(function () {
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware(['permission:master.products.view'])->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/data', [ProductController::class, 'getData'])->name('products.data');
        Route::post('/products/share-text', [ProductController::class, 'shareText'])->name('products.share-text');
        Route::post('/products/export', [ProductController::class, 'export'])->name('products.export');
        Route::post('/products/barcodes/print', [ProductController::class, 'printBarcodes'])->name('products.barcodes.print');
        Route::get('/table-preferences/{tableKey}', [TablePreferenceController::class, 'show'])->name('table-preferences.show');
        Route::put('/table-preferences/{tableKey}', [TablePreferenceController::class, 'store'])->name('table-preferences.store');
        Route::get('/products/meta/categories/{category}/sub-categories', [ProductController::class, 'getSubCategories'])->name('products.meta.sub-categories');
        Route::get('/products/meta/brands/{brand}/types', [ProductController::class, 'getProductTypes'])->name('products.meta.product-types');
        Route::get('/product-makers', [ProductMakerController::class, 'index'])->name('product-makers.index');
        Route::get('/product-makers/data', [ProductMakerController::class, 'getData'])->name('product-makers.data');
    });

    Route::middleware(['permission:master.products.create'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/import', [ProductController::class, 'importForm'])->name('products.import');
        Route::get('/products/import/template', [ProductController::class, 'downloadImportTemplate'])->name('products.import.template');
        Route::post('/products/import/preview', [ProductController::class, 'previewImport'])->name('products.import.preview');
        Route::post('/products/import/preview/update', [ProductController::class, 'updateImportPreview'])->name('products.import.preview.update');
        Route::post('/products/import/store', [ProductController::class, 'storeImport'])->name('products.import.store');
        Route::get('/product-makers/create', [ProductMakerController::class, 'create'])->name('product-makers.create');
        Route::post('/product-makers', [ProductMakerController::class, 'store'])->name('product-makers.store');
        Route::post('/product-qualities', [ProductQualityController::class, 'store'])->name('product-qualities.store');
    });

    Route::middleware(['permission:master.products.edit'])->group(function () {
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::get('/product-makers/{productMaker}/edit', [ProductMakerController::class, 'edit'])->name('product-makers.edit');
        Route::put('/product-makers/{productMaker}', [ProductMakerController::class, 'update'])->name('product-makers.update');
    });

    Route::middleware(['permission:master.product_stocks.view'])->group(function () {
        Route::get('/products/{product}/stocks', [ProductStockController::class, 'index'])->name('products.stocks.index');
        Route::get('/purchase-orders', fn () => view('purchase-orders.livewire-index'))->name('purchase-orders.index');
        Route::get('/purchase-orders/data', [PurchaseOrderController::class, 'getData'])->name('purchase-orders.data');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show')->whereNumber('purchaseOrder');
        Route::get('/branch-transfers', [BranchTransferController::class, 'index'])->name('branch-transfers.index');
        Route::get('/branch-transfers/{branchTransfer}', [BranchTransferController::class, 'show'])->name('branch-transfers.show')->whereNumber('branchTransfer');
        Route::get('/branch-transfers/{branchTransfer}/print', [BranchTransferController::class, 'print'])->name('branch-transfers.print')->whereNumber('branchTransfer');
    });

    Route::middleware(['permission:master.product_stocks.edit'])->group(function () {
        Route::put('/products/{product}/stocks', [ProductStockController::class, 'update'])->name('products.stocks.update');
        Route::post('/products/{product}/stock-movements', [ProductStockController::class, 'storeMovement'])->name('products.stock-movements.store');
        Route::post('/products/{product}/stock-transfers', [ProductStockController::class, 'storeTransfer'])->name('products.stock-transfers.store');
        Route::get('/purchase-orders/create', fn () => view('purchase-orders.livewire-create'))->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::post('/purchase-orders/{purchaseOrder}/payment', [PurchaseOrderController::class, 'storePayment'])->name('purchase-orders.payment')->whereNumber('purchaseOrder');
        Route::get('/branch-transfers/create', [BranchTransferController::class, 'create'])->name('branch-transfers.create');
        Route::post('/branch-transfers', [BranchTransferController::class, 'store'])->name('branch-transfers.store');
        Route::post('/branch-transfers/{branchTransfer}/ship', [BranchTransferController::class, 'ship'])->name('branch-transfers.ship')->whereNumber('branchTransfer');
        Route::get('/branch-transfers/{branchTransfer}/receive', [BranchTransferController::class, 'receiveForm'])->name('branch-transfers.receive.form')->whereNumber('branchTransfer');
        Route::post('/branch-transfers/{branchTransfer}/receive', [BranchTransferController::class, 'receive'])->name('branch-transfers.receive')->whereNumber('branchTransfer');
        Route::post('/branch-transfers/{branchTransfer}/cancel', [BranchTransferController::class, 'cancel'])->name('branch-transfers.cancel')->whereNumber('branchTransfer');
    });

    Route::middleware(['permission:master.branches.view'])->group(function () {
        Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/branches/data', [BranchController::class, 'getData'])->name('branches.data');
    });

    Route::middleware(['permission:master.branches.create'])->group(function () {
        Route::get('/branches/create', [BranchController::class, 'create'])->name('branches.create');
        Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
    });

    Route::middleware(['permission:master.branches.edit'])->group(function () {
        Route::get('/branches/{branch}/edit', [BranchController::class, 'edit'])->name('branches.edit');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->name('branches.update');
    });

    Route::middleware(['permission:master.branches.delete'])->group(function () {
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    Route::middleware(['permission:master.products.delete'])->group(function () {
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/product-makers/{productMaker}', [ProductMakerController::class, 'destroy'])->name('product-makers.destroy');
    });

    Route::middleware(['permission:master.sub_categories.view'])->group(function () {
        Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
        Route::get('/sub-categories/data', [SubCategoryController::class, 'getData'])->name('sub-categories.data');
    });

    Route::middleware(['permission:master.sub_categories.create'])->group(function () {
        Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
        Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
    });

    Route::middleware(['permission:master.sub_categories.edit'])->group(function () {
        Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
        Route::put('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
    });

    Route::middleware(['permission:master.sub_categories.delete'])->group(function () {
        Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');
    });

    Route::middleware(['permission:master.brands.view'])->group(function () {
        Route::get('/brands', function () { return view('brands.livewire-index'); })->name('brands.index');
        Route::get('/brands/data', [BrandController::class, 'getData'])->name('brands.data');
    });

    Route::middleware(['permission:master.brands.create'])->group(function () {
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    });

    Route::middleware(['permission:master.brands.edit'])->group(function () {
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    });

    Route::middleware(['permission:master.brands.delete'])->group(function () {
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
    });

    Route::middleware(['permission:master.product_types.view'])->group(function () {
        Route::get('/product-types', [ProductTypeController::class, 'index'])->name('product-types.index');
        Route::get('/product-types/data', [ProductTypeController::class, 'getData'])->name('product-types.data');
    });

    Route::middleware(['permission:master.product_types.create'])->group(function () {
        Route::get('/product-types/create', [ProductTypeController::class, 'create'])->name('product-types.create');
        Route::post('/product-types', [ProductTypeController::class, 'store'])->name('product-types.store');
    });

    Route::middleware(['permission:master.product_types.edit'])->group(function () {
        Route::get('/product-types/{productType}/edit', [ProductTypeController::class, 'edit'])->name('product-types.edit');
        Route::put('/product-types/{productType}', [ProductTypeController::class, 'update'])->name('product-types.update');
    });

    Route::middleware(['permission:master.product_types.delete'])->group(function () {
        Route::delete('/product-types/{productType}', [ProductTypeController::class, 'destroy'])->name('product-types.destroy');
    });

    Route::middleware(['permission:master.locations.view'])->group(function () {
        Route::get('/locations', fn () => view('locations.livewire-index'))->name('locations.index');
        Route::get('/locations/data', [LocationController::class, 'getData'])->name('locations.data');
    });

    Route::middleware(['permission:master.locations.create'])->group(function () {
        Route::get('/locations/create', fn () => view('locations.livewire-create'))->name('locations.create');
        Route::post('/locations', [LocationController::class, 'store'])->name('locations.store');
    });

    Route::middleware(['permission:master.locations.edit'])->group(function () {
        Route::get('/locations/{location}/edit', function (\App\Models\Location $location) { return view('locations.livewire-edit', ['location' => $location]); })->name('locations.edit');
        Route::put('/locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    });

    Route::middleware(['permission:master.locations.delete'])->group(function () {
        Route::delete('/locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
    });

    Route::middleware(['permission:master.customer_groups.view'])->group(function () {
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/data', [CustomerController::class, 'getData'])->name('customers.data');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->whereNumber('customer')->name('customers.show');
        Route::get('/customer-groups', [CustomerGroupController::class, 'index'])->name('customer-groups.index');
        Route::get('/customer-groups/data', [CustomerGroupController::class, 'getData'])->name('customer-groups.data');
        Route::match(['get', 'post'], '/customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::match(['get', 'post'], '/customer-groups/export', [CustomerGroupController::class, 'export'])->name('customer-groups.export');
    });

    Route::middleware(['permission:master.customer_groups.create'])->group(function () {
        Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/customers/import', [CustomerController::class, 'importForm'])->name('customers.import');
        Route::get('/customers/import/template', [CustomerController::class, 'downloadImportTemplate'])->name('customers.import.template');
        Route::post('/customers/import/preview', [CustomerController::class, 'previewImport'])->name('customers.import.preview');
        Route::post('/customers/import/store', [CustomerController::class, 'storeImport'])->name('customers.import.store');
        Route::get('/customer-groups/create', [CustomerGroupController::class, 'create'])->name('customer-groups.create');
        Route::post('/customer-groups', [CustomerGroupController::class, 'store'])->name('customer-groups.store');
        Route::get('/customer-groups/import', [CustomerGroupController::class, 'importForm'])->name('customer-groups.import');
        Route::get('/customer-groups/import/template', [CustomerGroupController::class, 'downloadImportTemplate'])->name('customer-groups.import.template');
        Route::post('/customer-groups/import/preview', [CustomerGroupController::class, 'previewImport'])->name('customer-groups.import.preview');
        Route::post('/customer-groups/import/store', [CustomerGroupController::class, 'storeImport'])->name('customer-groups.import.store');
    });

    Route::middleware(['permission:master.customer_groups.edit'])->group(function () {
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::post('/customers/{customer}/redeem-points', [CustomerController::class, 'redeemPoints'])->name('customers.redeem-points');
        Route::get('/customer-groups/{customerGroup}/edit', [CustomerGroupController::class, 'edit'])->name('customer-groups.edit');
        Route::put('/customer-groups/{customerGroup}', [CustomerGroupController::class, 'update'])->name('customer-groups.update');
    });

    Route::middleware(['permission:master.customer_groups.delete'])->group(function () {
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::delete('/customer-groups/{customerGroup}', [CustomerGroupController::class, 'destroy'])->name('customer-groups.destroy');
    });

    Route::middleware(['permission:transactions.create'])->group(function () {
        Route::get('/transactions/create', fn () => view('transactions.livewire-create'))->name('transactions.create');
        Route::get('/transactions/{saleChannel}/create', fn ($saleChannel) => view('transactions.livewire-create', ['saleChannel' => $saleChannel]))
            ->whereIn('saleChannel', ['toko', 'cabang', 'partai'])
            ->name('transactions.create.channel');
        Route::get('/transactions/create/{saleChannel}', fn ($saleChannel) => view('transactions.livewire-create', ['saleChannel' => $saleChannel]))
            ->whereIn('saleChannel', ['cabang', 'partai']);
        Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
        Route::get('/transactions/lookup/product', [TransactionController::class, 'lookupProduct'])->name('transactions.lookup.product');
        Route::get('/transactions/search/products', [TransactionController::class, 'searchProducts'])->name('transactions.search.products');
        Route::get('/transactions/search/customers', [TransactionController::class, 'searchCustomers'])->name('transactions.search.customers');
        Route::get('/transactions/cash/status', [TransactionController::class, 'cashSessionStatus'])->name('transactions.cash.status');
        Route::post('/transactions/cash/open', [TransactionController::class, 'openCashSession'])->name('transactions.cash.open');
        Route::get('/transactions/cash/summary', [TransactionController::class, 'cashSessionSummary'])->name('transactions.cash.summary');
        Route::post('/transactions/cash/close', [TransactionController::class, 'closeCashSession'])->name('transactions.cash.close');
        Route::get('/service-transactions/create', fn () => view('service-transactions.livewire-create'))->name('service-transactions.create');
        Route::post('/service-transactions', [ServiceTransactionController::class, 'store'])->name('service-transactions.store');
    });

    Route::middleware(['permission:transactions.view'])->group(function () {
        Route::get('/transactions', fn () => view('transactions.livewire-index'))->name('transactions.index');
        Route::get('/transactions/data', [TransactionController::class, 'getData'])->name('transactions.data');
        Route::get('/transactions/{saleChannel}', [TransactionController::class, 'index'])
            ->whereIn('saleChannel', ['toko', 'cabang', 'partai'])
            ->name('transactions.index.channel');
        Route::get('/transactions/{saleChannel}/data', [TransactionController::class, 'getData'])
            ->whereIn('saleChannel', ['toko', 'cabang', 'partai'])
            ->name('transactions.data.channel');
        Route::get('/transactions/{sale}', [TransactionController::class, 'show'])->whereNumber('sale')->name('transactions.show');
        Route::get('/transactions/{sale}/receipt', fn (\App\Models\Sale $sale) => view('transactions.livewire-receipt', ['sale' => $sale]))->whereNumber('sale')->name('transactions.receipt');
        Route::post('/transactions/{sale}/serial-numbers', [TransactionController::class, 'updateSerialNumbers'])->whereNumber('sale')->name('transactions.serial-numbers.update');
        Route::post('/transactions/{sale}/payments', [TransactionController::class, 'storePayment'])->whereNumber('sale')->name('transactions.payments.store');
        Route::post('/transactions/{sale}/void', [TransactionController::class, 'void'])->whereNumber('sale')->name('transactions.void');
        Route::get('/service-transactions', fn () => view('service-transactions.livewire-index'))->name('service-transactions.index');
        Route::get('/service-transactions/data', [ServiceTransactionController::class, 'getData'])->name('service-transactions.data');
        Route::get('/service-transactions/{serviceTransaction}', [ServiceTransactionController::class, 'show'])->whereNumber('serviceTransaction')->name('service-transactions.show');
        Route::post('/service-transactions/{serviceTransaction}/payments', [ServiceTransactionController::class, 'storePayment'])->whereNumber('serviceTransaction')->name('service-transactions.payments.store');
        Route::post('/service-transactions/{serviceTransaction}/void', [ServiceTransactionController::class, 'void'])->whereNumber('serviceTransaction')->name('service-transactions.void');
        Route::post('/customers/{customer}/credit-payments', [CustomerController::class, 'storeCreditPayment'])->whereNumber('customer')->name('customers.credit-payments.store');
    });

    Route::middleware(['permission:transactions.settings'])->group(function () {
        Route::post('/transactions/settings/member-points', [TransactionController::class, 'updateMemberPointSetting'])->name('transactions.settings.member-points');
    });

    Route::middleware(['permission:master.products.view'])->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/data', [SupplierController::class, 'getData'])->name('suppliers.data');
        Route::match(['get', 'post'], '/suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/data', [ServiceController::class, 'getData'])->name('services.data');
        Route::get('/services/search', [ServiceController::class, 'search'])->name('services.search');
    });

    Route::middleware(['permission:master.products.create'])->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/import', [SupplierController::class, 'importForm'])->name('suppliers.import');
        Route::get('/suppliers/import/template', [SupplierController::class, 'downloadImportTemplate'])->name('suppliers.import.template');
        Route::post('/suppliers/import/preview', [SupplierController::class, 'previewImport'])->name('suppliers.import.preview');
        Route::post('/suppliers/import/store', [SupplierController::class, 'storeImport'])->name('suppliers.import.store');
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    });

    Route::middleware(['permission:master.products.edit'])->group(function () {
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    });

    Route::middleware(['permission:master.products.delete'])->group(function () {
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    });

    Route::middleware(['permission:master.access'])->group(function () {
        Route::get('/back-office', [BackOfficeController::class, 'dashboard'])->name('back-office.dashboard');
        Route::get('/back-office/cash-accounts', [BackOfficeController::class, 'cashAccounts'])->name('back-office.cash-accounts.index');
        Route::post('/back-office/cash-accounts', [BackOfficeController::class, 'storeCashAccount'])->name('back-office.cash-accounts.store');
        Route::get('/back-office/cost-categories', [BackOfficeController::class, 'costCategories'])->name('back-office.cost-categories.index');
        Route::post('/back-office/cost-categories', [BackOfficeController::class, 'storeCostCategory'])->name('back-office.cost-categories.store');
        Route::get('/back-office/cash-transactions/{type}', [BackOfficeController::class, 'cashTransactions'])
            ->whereIn('type', ['income', 'expense'])
            ->name('back-office.cash-transactions.index');
        Route::post('/back-office/cash-transactions/{type}', [BackOfficeController::class, 'storeCashTransaction'])
            ->whereIn('type', ['income', 'expense'])
            ->name('back-office.cash-transactions.store');
        Route::get('/back-office/cash-mutations', [BackOfficeController::class, 'cashMutations'])->name('back-office.cash-mutations.index');
        Route::post('/back-office/cash-mutations', [BackOfficeController::class, 'storeCashMutation'])->name('back-office.cash-mutations.store');
        Route::get('/back-office/employee-advances', [BackOfficeController::class, 'employeeAdvances'])->name('back-office.employee-advances.index');
        Route::post('/back-office/employee-advances', [BackOfficeController::class, 'storeEmployeeAdvance'])->name('back-office.employee-advances.store');
        Route::get('/back-office/stock-documents/{type}', [BackOfficeController::class, 'stockDocuments'])
            ->whereIn('type', ['correction', 'usage'])
            ->name('back-office.stock-documents.index');
        Route::post('/back-office/stock-documents/{type}', [BackOfficeController::class, 'storeStockDocument'])
            ->whereIn('type', ['correction', 'usage'])
            ->name('back-office.stock-documents.store');

        // SID Retail Migration Routes
        Route::prefix('sid-retail')->name('sid-retail.')->group(function () {
            Route::get('/', [SidRetailImportController::class, 'index'])->name('index');
            Route::post('/migrate', [SidRetailImportController::class, 'migrateNow'])->name('migrate');
            Route::get('/status', [SidRetailImportController::class, 'status'])->name('status');
            Route::get('/config', [SidRetailImportController::class, 'config'])->name('config');
            Route::post('/config', [SidRetailImportController::class, 'saveConfig'])->name('config.save');
        });

        // Laporan Routes
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/purchases', [ReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('/reports/stocks', [ReportController::class, 'stocks'])->name('reports.stocks');
        Route::get('/reports/cash', [ReportController::class, 'cash'])->name('reports.cash');
        Route::get('/reports/receivables', [ReportController::class, 'receivables'])->name('reports.receivables');
        Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss'])->name('reports.profit_loss');
        Route::get('/reports/services', [ReportController::class, 'services'])->name('reports.services');
    });

    // Pembukuan / Akuntansi (double-entry)
    Route::middleware(['permission:accounting.access'])->group(function () {
        Route::get('/accounting', [AccountingController::class, 'ledger'])->name('accounting.index');
        Route::get('/accounting/ledger', [AccountingController::class, 'ledger'])->name('accounting.ledger');
        Route::get('/accounting/journal', [AccountingController::class, 'journal'])->name('accounting.journal');
        Route::get('/accounting/chart', [AccountingController::class, 'chart'])->name('accounting.chart');
        Route::get('/accounting/trial-balance', [AccountingController::class, 'trialBalance'])->name('accounting.trial_balance');
        Route::get('/accounting/profit-loss', [AccountingController::class, 'profitLoss'])->name('accounting.profit_loss');
        Route::get('/accounting/balance-sheet', [AccountingController::class, 'balanceSheet'])->name('accounting.balance_sheet');
    });

    // WMS: Stock Opname (inventory / cycle count)
    Route::middleware(['permission:master.product_stocks.view'])->group(function () {
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');
        Route::get('/stock-opname/create', [StockOpnameController::class, 'create'])->name('stock-opname.create');
        Route::get('/stock-opname/{stockOpname}', [StockOpnameController::class, 'show'])->name('stock-opname.show');
        Route::post('/stock-opname/{stockOpname}/complete', [StockOpnameController::class, 'complete'])->name('stock-opname.complete');
        Route::delete('/stock-opname/{stockOpname}', [StockOpnameController::class, 'destroy'])->name('stock-opname.destroy');
    });

    Route::middleware(['permission:master.product_stocks.edit'])->group(function () {
        Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');
    });

    // WMS: Internal Picking Request (teknisi servis)
    Route::middleware(['permission:master.product_stocks.view'])->group(function () {
        Route::get('/picking-requests', [PickingRequestController::class, 'index'])->name('picking-requests.index');
        Route::get('/picking-requests/create', [PickingRequestController::class, 'create'])->name('picking-requests.create');
        Route::get('/picking-requests/{pickingRequest}', [PickingRequestController::class, 'show'])->name('picking-requests.show');
        Route::delete('/picking-requests/{pickingRequest}', [PickingRequestController::class, 'destroy'])->name('picking-requests.destroy');
    });
    Route::middleware(['permission:master.product_stocks.edit'])->group(function () {
        Route::post('/picking-requests', [PickingRequestController::class, 'store'])->name('picking-requests.store');
        Route::post('/picking-requests/{pickingRequest}/fulfill', [PickingRequestController::class, 'fulfill'])->name('picking-requests.fulfill');
        Route::post('/picking-requests/{pickingRequest}/cancel', [PickingRequestController::class, 'cancel'])->name('picking-requests.cancel');
    });

    // WMS: Rekomendasi Restock + Auto PO
    Route::middleware(['permission:master.access'])->group(function () {
        Route::get('/purchase-orders/restock', [PurchaseOrderController::class, 'restockRecommendation'])->name('purchase-orders.restock');
        Route::post('/purchase-orders/generate-auto-po', [PurchaseOrderController::class, 'generateAutoPO'])->name('purchase-orders.generate_auto_po');
    });

    // WMS: Serial & Bin Tracking
    Route::middleware(['permission:master.product_stocks.view'])->group(function () {
        Route::get('/item-serials', [ItemSerialController::class, 'index'])->name('item-serials.index');
        Route::get('/item-serials/create', [ItemSerialController::class, 'create'])->name('item-serials.create');
        Route::post('/item-serials', [ItemSerialController::class, 'store'])->name('item-serials.store');
    });



    Route::middleware(['permission:master.products.view'])->group(function () {
        Route::get('/units', fn () => view('units.livewire-index'))->name('units.index');
        Route::get('/units/data', [UnitController::class, 'getData'])->name('units.data');
    });

    Route::middleware(['permission:master.products.create'])->group(function () {
        Route::get('/units/create', fn () => view('units.livewire-create'))->name('units.create');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    });

    Route::middleware(['permission:master.products.edit'])->group(function () {
        Route::get('/units/{unit}/edit', fn ($unit) => view('units.livewire-edit', ['unit' => $unit]))->name('units.edit');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
    });

    Route::middleware(['permission:master.products.delete'])->group(function () {
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    // Modern Livewire Routes - using view wrapper pattern
    Route::middleware(['permission:master.products.view'])->group(function () {
        Route::get('/livewire/products', function () {
            return view('livewire-page.products');
        })->name('livewire.products.index');

        Route::get('/livewire/customers', function () {
            return view('livewire-page.customers');
        })->name('livewire.customers.index');

        Route::get('/livewire/suppliers', function () {
            return view('livewire-page.suppliers');
        })->name('livewire.suppliers.index');
    });

});

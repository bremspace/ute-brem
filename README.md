# UTE Parts POS (Uteparts)

POS/Kasir application for UTE Parts (HP sparepart retail) built with Laravel 12 + TALL Stack (Tailwind CSS + Alpine.js + Laravel Livewire v3).!

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend Admin | Livewire v3 + Tailwind CSS 3 + Alpine.js |
| Frontend Website | React 18 + TypeScript + Tailwind CSS 3 |
| Build Tool | Vite 6 |
| Database | MySQL 8+ / MariaDB 10.5+ |
| Payment | DuitKu Payment Gateway |
| Accounting | Double-entry bookkeeping (Ledger, Journal, Trial Balance, P&L, Balance Sheet) |

## Features

### Master Data
- **Products**: Full CRUD with categories, sub-categories, brands, product makers, product types, quality levels, units, locations, and racks
- **Multi-location Stock**: Stock tracked per location/rack with full movement history
- **Product Import/Export**: Excel template import with preview validation, CSV export
- **Barcode Management**: Primary barcode, multiple barcodes per product, barcode printing (Remi pattern)
- **Price Tiers**: Selling price with margin calculation, tiered pricing, customer group pricing, open price option

### Sales & Transactions (POS)
- **Cash Session**: Open/close daily cash session with opening balance
- **Barcode Scanner**: Scanner-friendly POS interface, qty pre-fill before scan
- **Payment Methods**: Cash, Transfer, QRIS, Tempo (credit)
- **Customer Points**: Auto-earn points on purchase, redeem points
- **Serial Number Tracking**: Item serial/IMEI tracking for serialized products
- **Void Transaction**: With reason logging
- **Receipt Generation**: Printable receipt with company branding

### Service Transactions
- **Service Jobs**: Create service transactions with labor + spare parts
- **Technician Assignment**: Assign technicians to service jobs
- **Payment Processing**: Full payment or partial/tempo

### Purchase Orders (PO)
- **PO Creation**: Create POs with supplier selection, location, and items
- **Stock Projection**: View projected stock after PO receipt
- **PO Payment**: Record payments against POs

### Branch Transfers
- **Multi-step Workflow**: Draft → Ship → Receive (with status tracking)
- **Stock Deduction**: Automatic stock deduction on ship, addition on receive
- **Discrepancy Handling**: Record quantity discrepancies during receive
- **Print Surat Jalan**: Printable shipping document with signatures

### Stock Opname (Inventory Count)
- **Physical Count**: Select location, enter actual quantities per product
- **Difference Calculation**: Auto-calculate system vs actual difference
- **Auto Adjustment**: Process adjustments with accounting journal posting

### Picking Requests (Service Spare Parts)
- **Request Creation**: Technician requests spare parts from warehouse
- **Fulfill**: Warehouse cuts stock, serial numbers marked as "Reserved for Repair"
- **Status Tracking**: Open → Fulfilled → Used in service

### Back Office
- **Cash Accounts**: Manage cash, bank, and e-wallet accounts with balances
- **Income/Expense**: Record income and expense transactions with cost categories
- **Cash Mutations**: Transfer between cash accounts
- **Employee Advances (Kasbon)**: Record and track employee advances
- **Stock Documents**: Stock correction and usage recording from back office

### Accounting (Double-Entry)
- **Chart of Accounts**: Full COA with categories (asset, liability, equity, revenue, expense, COGS)
- **General Ledger**: View all transactions per account with running balance
- **General Journal**: View all journal entries by date
- **Trial Balance**: Period-end trial balance with debit/credit totals
- **Profit & Loss**: Revenue, COGS, expenses, gross profit, net profit
- **Balance Sheet**: Assets, liabilities, equity with period-to-date retained earnings

### Reporting
- **Sales Report**: Filterable by date, channel, payment method
- **Purchase Report**: Filterable by date, supplier
- **Stock Report**: Low stock alerts, stock by location, recent movements
- **Cash Report**: Income/expense summary with employee advances
- **Receivables Report**: Outstanding credit/tempo sales
- **Service Report**: Service transaction summary by technician/status
- **Profit & Loss Report**: Operational P&L (sales, services, HPP, expenses)

### Customer & Supplier Management
- **Customer CRUD**: Name, phone, email, type (regular/member), customer group
- **Member System**: Member code, points balance, points redeem
- **Customer Import/Export**: Excel import with preview, CSV export
- **Customer Groups**: Group management with import/export
- **Supplier CRUD**: Name, contact, import/export

### User & Role Management (RBAC)
- **User CRUD**: Create, edit, soft-delete, restore users
- **Roles**: Create roles with permission assignment
- **Permissions**: Granular permission system (50+ permissions across modules)
- **Role Templates**: Pre-built permission templates (Super Admin, Manager, Kasir, etc.)
- **Activity Logs**: Full audit trail of user actions

### Website / Katalog (Public)
- **Product Catalog**: Public product listing with search and categories
- **Member Login**: Customer authentication for member features
- **Cart & Checkout**: Add to cart, checkout with payment selection
- **Order Tracking**: Track order status after purchase
- **Member Dashboard**: View order history, points balance

### Settings
- **Printer Settings**: Configure receipt printer (mode, bridge URL, paper width, copies, header/footer)
- **Table Preferences**: Customizable table display settings
- **Profile Settings**: Change password, view profile

### SidRetail Migration
- **Data Import**: Migrate data from SidRetail POS system
- **Configuration**: Database connection setup
- **Status Monitoring**: Migration progress tracking

---

## Deployment Guide

### Local Development

#### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- MySQL 8+ or MariaDB 10.5+

#### Steps

```bash
# 1. Clone repository
git clone <repository-url>
cd ute-pos-main

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Create environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Configure database in .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=ute_parts_pos
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Create database and run migrations
php artisan migrate

# 8. Seed database with sample data
php artisan db:seed

# 9. Build frontend assets
npm run build

# 10. Start development server
php artisan serve
```

Open: http://localhost:8000

### Web Hosting Deployment

#### Prerequisites
- PHP 8.2+ with required extensions (mbstring, openssl, pdo, pdo_mysql, curl, xml, zip)
- MySQL 8+ or MariaDB 10.5+
- Composer (available via SSH or panel)
- Node.js 18+ (available via SSH)

#### Steps

```bash
# 1. Upload all project files to web hosting via FTP/SFTP or Git

# 2. SSH into hosting and navigate to project directory
cd /path/to/project

# 3. Install PHP dependencies (production mode)
composer install --no-dev --optimize-autoloader

# 4. Install Node dependencies
npm install

# 5. Create environment file
cp .env.example .env

# 6. Generate application key
php artisan key:generate

# 7. Configure .env with production settings
# - Set APP_URL to your domain
# - Set APP_ENV=production
# - Set APP_DEBUG=false
# - Configure database credentials
# - Configure mail settings (optional)
# - Configure DuitKu payment gateway (optional)

# 8. Create database
# Create MySQL database via hosting panel
# Update .env with credentials

# 9. Run migrations
php artisan migrate

# 10. Seed database
php artisan db:seed

# 11. Build frontend assets
npm run build

# 12. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 13. Configure web server (Apache/Nginx)

# Apache: Ensure .htaccess exists in public/ (already present in Laravel)
# DocumentRoot should point to: /path/to/project/public

# Nginx example config:
# server {
#     listen 80;
#     server_name yourdomain.com;
#     root /path/to/project/public;
#
#     add_header X-Frame-Options "SAMEORIGIN";
#     add_header X-Content-Type-Options "nosniff";
#
#     index index.php;
#
#     charset utf-8;
#
#     location / {
#         try_files $uri $uri/ /index.php?$query_string;
#     }
#
#     location = /favicon.ico { access_log off; log_not_found off; }
#     location = /robots.txt  { access_log off; log_not_found off; }
#
#     error_page 404 /index.php;
#
#     location ~ \.php$ {
#         fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
#         fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
#         include fastcgi_params;
#     }
#
#     location ~ /\.(?!well-known).* {
#         deny all;
#     }
# }

# 14. Restart services
# systemctl restart nginx   (Nginx)
# systemctl restart apache2  (Apache)
# systemctl restart php8.2-fpm  (PHP-FPM)
```

### Environment Variables (.env)

```env
APP_NAME="UTE Parts POS"
APP_ENV=production
APP_KEY=base64:...                    # Generated by php artisan key:generate
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ute_parts_pos
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# DuitKu Payment Gateway (optional)
# DUITKU_MERCHANT_CODE=your_merchant_code
# DUITKU_API_KEY=your_api_key
# DUITKU_ENVIRONMENT=production

# Mail (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@yourdomain.com"
MAIL_FROM_NAME="UTE Parts"
```

---

## Default Accounts

After running `php artisan db:seed`:

| Role | Email | Password |
|------|-------|----------|
| Owner | owner@uteparts.test | Owner123! |
| Manager | manager@uteparts.test | Manager123! |
| Kasir | kasir@uteparts.test | Kasir123! |

---

## Quick Access URLs

### Authentication
- `/login` - Login page
- `/register` - Member registration
- `/home` - Dashboard (after login)

### Sales & Transactions
- `/transactions` - Sales list (Kasir)
- `/transactions/create` - New transaction (POS)
- `/service-transactions` - Service transactions
- `/service-transactions/create` - New service transaction

### Inventory
- `/products` - Product management
- `/products/import` - Import products
- `/purchase-orders` - Purchase orders
- `/branch-transfers` - Branch transfers
- `/stock-opname` - Stock opname (physical count)
- `/picking-requests` - Picking requests
- `/item-serials` - Serial number tracking

### Master Data
- `/categories` - Categories
- `/brands` - Brands
- `/units` - Units
- `/locations` - Locations
- `/customers` - Customers
- `/suppliers` - Suppliers

### Back Office
- `/back-office` - Back office dashboard
- `/back-office/cash-accounts` - Cash accounts
- `/back-office/cash-transactions/income` - Income
- `/back-office/cash-transactions/expense` - Expense
- `/back-office/cash-mutations` - Cash mutations
- `/back-office/employee-advances` - Employee advances
- `/back-office/stock-documents/correction` - Stock correction
- `/back-office/stock-documents/usage` - Stock usage

### Accounting
- `/accounting/ledger` - General ledger
- `/accounting/journal` - General journal
- `/accounting/chart` - Chart of accounts
- `/accounting/trial-balance` - Trial balance
- `/accounting/profit-loss` - Profit & loss
- `/accounting/balance-sheet` - Balance sheet

### Reports
- `/reports` - Reports dashboard
- `/reports/sales` - Sales report
- `/reports/purchases` - Purchase report
- `/reports/stocks` - Stock report
- `/reports/cash` - Cash report
- `/reports/receivables` - Receivables report
- `/reports/profit-loss` - P&L report
- `/reports/services` - Service report

### User & Role Management
- `/users` - User management
- `/users/trash` - Deleted users
- `/users/logs` - Activity logs
- `/roles` - Role management

### Settings
- `/settings/printer` - Printer settings
- `/settings/table-preferences` - Table preferences

### Website (Public)
- `/website/products` - Product catalog

---

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/        # 15+ controllers (legacy, being migrated)
│   │   └── Livewire/           # 60+ Livewire components (TALL Stack)
│   ├── Models/                 # Eloquent models
│   └── Services/               # Business logic services
├── resources/
│   └── views/
│       ├── livewire/           # 60+ Livewire blade templates
│       │   ├── back-office/    # Cash accounts, transactions, etc.
│       │   ├── branches/       # Branch transfer views
│       │   ├── categories/     # Category management
│       │   ├── customers/      # Customer management
│       │   ├── item-serials/   # Serial number tracking
│       │   ├── picking-requests/ # Picking request views
│       │   ├── purchase-orders/ # PO views
│       │   ├── roles/          # Role management
│       │   ├── sales/          # POS & sales views
│       │   ├── service-transactions/ # Service views
│       │   ├── settings/       # Settings views
│       │   ├── stock-opname/   # Stock opname views
│       │   ├── units/          # Unit management
│       │   ├── users/          # User management
│       │   └── accounting/     # Accounting views
│       └── layouts/            # Blade layouts (sneat, app)
├── routes/
│   └── web.php                 # All route definitions
├── public/
│   └── build/                  # Compiled assets (Vite)
├── tailwind.config.js          # Tailwind CSS configuration
├── vite.config.ts              # Vite configuration
└── package.json                # Node dependencies
```

---

## Livewire Components

| Module | Components |
|--------|-----------|
| Website | Cart, Checkout, OrderDetail, OrderTrack, Auth, MemberDashboard |
| Settings | PrinterSettings, TablePreferences, ProfileSettings |
| Master Data | CategoryIndex/Form, BrandIndex/Form, UnitIndex/Form, LocationIndex/Form |
| Sales | SalesIndex/Form/Receipt, ServiceTransactionIndex/Form |
| Purchase Orders | POIndex/Form |
| Branch Transfer | TransferIndex/Form/Print |
| Stock Opname | StockOpnameIndex/Form |
| Picking Request | PickingRequestIndex/Form |
| Item Serial | SerialIndex/Form |
| Customer | CustomerIndex/Form/Import/Export |
| User RBAC | UserIndex/Form/Trash/Logs/RoleManage/Permissions, RoleIndex/Form/Permissions |
| Back Office | BackOfficeDashboard, CashAccounts, CashTransaction, CostCategories, EmployeeAdvances, StockDocuments |
| Accounting | Ledger, Journal, ChartOfAccounts, TrialBalance, ProfitLoss, BalanceSheet |
| Reports | ReportsIndex |
| Import/Export | ProductImport |
| SidRetail | SidRetailMigration |

---

## Troubleshooting

### Common Issues

**Tabel tidak ditemukan**
```bash
php artisan migrate
php artisan db:seed
```

**Mixed Content error (HTTPS)**
Pastikan `APP_URL` di `.env` memakai `https://...` dan reverse proxy mengirim header HTTPS.

**Vite build error**
```bash
npm run build
```
Pastikan `public/build/` ada dan berisi file hasil build.

**Livewire component not found**
Pastikan file PHP component di `app/Http/Livewire/` dan blade view di `resources/views/livewire/` memiliki nama yang cocok.

**Permission denied on storage**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

**Blank page after deployment**
- Cek `storage/logs/laravel.log` untuk error details
- Pastikan `APP_DEBUG=false` di production
- Pastikan `php artisan key:generate` sudah dijalankan

**Tailwind classes not applying**
```bash
npm run build
```
Pastikan `tailwind.config.js` content paths sudah benar.

---

## License

MIT

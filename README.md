# UTE Parts POS (Uteparts)

Aplikasi POS/Kasir untuk UTE Parts (sparepart HP) berbasis Laravel 12.

Fitur utama:
- Master data barang: Produk, Kategori/Sub Kategori, Brand, Merek (pabrik/producer), Tipe HP, Supplier, Satuan, Lokasi + Rak.
- Stok multi lokasi, mutasi stok, transfer antar lokasi (stok tidak diedit langsung sebagai saldo akhir).
- Notifikasi stok minimum, pembuatan Purchase Order (PO), riwayat PO + attachment.
- Penjualan (Kasir): barcode scanner friendly, serial number item, open price (harga bisa diedit di transaksi jika diaktifkan di produk), poin member.
- Customer (biasa/member): kode member unik, detail customer + riwayat transaksi, tukar poin + lampiran.
- Website katalog (untuk customer): harga member bisa terlihat setelah login member.
- Setting Printer & Test Print.

## Requirements

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL/MariaDB

## Setup (Local Development)

1. Install dependency
```bash
composer install
npm install
```

2. Buat file environment dan generate key
```bash
copy .env.example .env
php artisan key:generate
```

3. Buat database (contoh: `db_uteparts`) lalu sesuaikan `.env`:

`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

4. Jalankan migrasi dan seed
```bash
php artisan migrate
php artisan db:seed
```

Catatan: `DatabaseSeeder` akan memanggil `RolePermissionSeeder`, `UserSeeder`, `MasterBarangSeeder`, dan `CustomerSeeder`.
`MasterBarangSeeder` juga mengisi sample produk + stok ledger.

5. Jalankan aplikasi
```bash
composer run dev
```

Buka: `http://localhost:8000`

## Akun Default (Seeder)

Seeder user ada di `database/seeders/UserSeeder.php`:

- Owner: `owner@uteparts.test` / `Owner123!`
- Manager: `manager@uteparts.test` / `Manager123!`
- Kasir: `kasir@uteparts.test` / `Kasir123!`

## Menu Penting

- Produk: `/products`
- Supplier: `/suppliers`
- Customer: `/customers`
- Transaksi: `/transactions`
- Purchase Order: `/purchase-orders`
- Setting Printer: `/settings/printer`
- Website katalog: klik menu `Lihat Website` (atau `/website/products` jika route tersedia)

## Catatan Operasional Kasir

- Setiap hari perlu input kas awal (cash session) sebelum bisa simpan transaksi.
- Barcode scanner: fokus ke input scan di halaman penjualan.
- Qty cepat: bisa ketik angka dulu (mis. `3`), lalu scan/input barang, qty akan mengikuti.

## Printer

Menu: `/settings/printer`
- Setting mode/bridge url, lebar kertas, jumlah copy, header/footer, dan test print.

## Troubleshooting

- Jika ada error tabel tidak ditemukan (mis. `sales`, `customers`, `pos_settings`):
  Jalankan `php artisan migrate` lalu ulangi `php artisan db:seed`.
- Jika DataTables error Mixed Content di server HTTPS:
  Pastikan `APP_URL` di `.env` memakai `https://...` dan reverse proxy mengirim header HTTPS dengan benar.
$user->assignRole('admin');

# Check permission
if ($user->hasPermission('users.create')) {
    // User dapat membuat user baru
}

# Check role
if ($user->hasRole('admin')) {
    // User adalah admin
}
```

## Quick Access URLs

Setelah aplikasi berjalan di `http://localhost:8000`:

### Authentication
- **Login**: `/login` - Halaman login
- **Dashboard**: `/home` - Dashboard utama setelah login

### User Management
- **Daftar User**: `/users` - Kelola semua user
- **Tambah User**: `/users/create` - Buat user baru
- **User Logs**: `/users/logs` - Activity logs global
- **Trash**: `/users/trash` - User yang dihapus (restore)

### RBAC Management
- **Daftar Role**: `/roles` - Kelola role dan permission
- **Tambah Role**: `/roles/create` - Buat role baru
- **User Role**: `/users/{id}/roles` - Assign role ke user tertentu

### Features
- **Export Data**: Tersedia di semua tabel (CSV, Excel, PDF)
- **Search & Filter**: Advanced filtering di semua DataTables
- **Responsive**: Akses optimal di desktop dan mobile

## Testing RBAC

1. **Login** dengan user `stagingpupose` (Super Administrator)
2. **Akses** `/roles` untuk manajemen role
3. **Test** assignment role di `/users/{id}/roles`
4. **Verifikasi** permission dengan user role berbeda
5. **Cek** activity logs di `/users/logs`

## Support

<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\BackOfficeCashAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        // id => [code, name, normal_balance, category, parent_id, type]
        $accounts = [
            1  => ['1000', 'ASET LANCAR', 'debit', 'asset', null, null],
            2  => ['1100', 'Kas & Bank', 'debit', 'asset', 1, null],
            3  => ['1101', 'Kas Toko (Cash)', 'debit', 'asset', 2, 'cash'],
            4  => ['1102', 'Kas Bank (Transfer)', 'debit', 'asset', 2, 'bank'],
            5  => ['1103', 'E-Wallet / QRIS', 'debit', 'asset', 2, 'ewallet'],
            6  => ['1200', 'Piutang Usaha', 'debit', 'asset', 1, 'receivable'],
            7  => ['1201', 'Piutang Penjualan', 'debit', 'asset', 6, 'receivable'],
            8  => ['1202', 'Piutang Jasa Servis', 'debit', 'asset', 6, 'receivable'],
            9  => ['1300', 'Persediaan Barang Dagang', 'debit', 'asset', 1, 'inventory'],
            10 => ['1301', 'Persediaan Sparepart', 'debit', 'asset', 9, 'inventory'],
            11 => ['1400', 'Piutang Karyawan (Kasbon)', 'debit', 'asset', 1, 'receivable'],
            15 => ['2000', 'KEWAJIBAN', 'credit', 'liability', null, null],
            16 => ['2100', 'Hutang Usaha', 'credit', 'liability', 15, 'payable'],
            17 => ['2101', 'Hutang Supplier', 'credit', 'liability', 16, 'payable'],
            18 => ['2200', 'Kewajiban Lain', 'credit', 'liability', 15, null],
            25 => ['3000', 'EKUITAS', 'credit', 'equity', null, null],
            26 => ['3100', 'Modal Pemilik', 'credit', 'equity', 25, null],
            27 => ['3101', 'Modal Awal Owner', 'credit', 'equity', 26, null],
            28 => ['3200', 'Laba Ditahan', 'credit', 'equity', 25, null],
            30 => ['4000', 'PENDAPATAN', 'credit', 'revenue', null, null],
            31 => ['4100', 'Pendapatan Penjualan', 'credit', 'revenue', 30, 'revenue'],
            32 => ['4101', 'Penjualan Toko', 'credit', 'revenue', 31, 'revenue'],
            33 => ['4102', 'Penjualan Cabang', 'credit', 'revenue', 31, 'revenue'],
            34 => ['4103', 'Penjualan Partai', 'credit', 'revenue', 31, 'revenue'],
            35 => ['4200', 'Pendapatan Jasa', 'credit', 'revenue', 30, 'revenue'],
            36 => ['4201', 'Pendapatan Servis HP', 'credit', 'revenue', 35, 'revenue'],
            37 => ['4300', 'Pendapatan Lain-lain', 'credit', 'revenue', 30, 'revenue'],
            38 => ['4301', 'Pendapatan Lain (Pemasukan Back Office)', 'credit', 'revenue', 37, 'revenue'],
            40 => ['5000', 'HARGA POKOK PENJUALAN', 'debit', 'expense', null, null],
            41 => ['5100', 'HPP Penjualan', 'debit', 'expense', 40, 'cogs'],
            42 => ['5101', 'HPP Sparepart Terjual', 'debit', 'expense', 41, 'cogs'],
            43 => ['5200', 'HPP Jasa', 'debit', 'expense', 40, 'cogs'],
            44 => ['5201', 'HPP Sparepart Servis', 'debit', 'expense', 43, 'cogs'],
            50 => ['6000', 'BIAYA OPERASIONAL', 'debit', 'expense', null, null],
            51 => ['6100', 'Biaya Toko & Operasional', 'debit', 'expense', 50, null],
            52 => ['6101', 'Biaya Operasional (Pengeluaran Back Office)', 'debit', 'expense', 51, null],
            53 => ['6200', 'Beban Gaji & Upah', 'debit', 'expense', 50, 'payroll'],
            54 => ['6201', 'Kasbon Karyawan (Beban)', 'debit', 'expense', 53, 'payroll'],
        ];

        DB::table('chart_of_accounts')->delete();

        foreach ($accounts as $id => [$code, $name, $nb, $cat, $parent, $type]) {
            ChartOfAccount::create([
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'normal_balance' => $nb,
                'category' => $cat,
                'parent_id' => $parent,
                'type' => $type,
                'is_postable' => $cat !== 'revenue' && $cat !== 'expense',
                'is_active' => true,
                'description' => $name,
            ]);
        }

        // Map each back-office cash account (tokens, bank, ewallet) to a COA asset
        $typeMap = [
            'cash' => 3,    // Kas Toko
            'bank' => 4,    // Kas Bank
            'ewallet' => 5, // E-Wallet
        ];
        foreach (BackOfficeCashAccount::select('id', 'type')->get() as $ca) {
            $coaId = $typeMap[$ca->type] ?? 3;
            $ca->update(['chart_of_account_id' => $coaId]);
        }
    }
}

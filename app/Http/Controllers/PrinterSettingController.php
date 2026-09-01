<?php

namespace App\Http\Controllers;

use App\Models\PosSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Storage;

class PrinterSettingController extends Controller
{
    public const KEY_COMPANY_NAME = 'company.name';
    public const KEY_COMPANY_ADDRESS = 'company.address';
    public const KEY_COMPANY_CITY = 'company.city';
    public const KEY_COMPANY_PROVINCE = 'company.province';
    public const KEY_COMPANY_COUNTRY = 'company.country';
    public const KEY_COMPANY_LOCATION_CODE = 'company.location_code';
    public const KEY_COMPANY_TAX_NUMBER = 'company.tax_number';
    public const KEY_COMPANY_TAXABLE_NUMBER = 'company.taxable_number';
    public const KEY_COMPANY_SLOGAN = 'company.slogan';
    public const KEY_COMPANY_REF_PURCHASE = 'company.ref.purchase';
    public const KEY_COMPANY_REF_PURCHASE_RETURN = 'company.ref.purchase_return';
    public const KEY_COMPANY_REF_RECEIVE_RETURN_ITEM = 'company.ref.receive_return_item';
    public const KEY_COMPANY_REF_STORE_SALE = 'company.ref.store_sale';
    public const KEY_COMPANY_REF_SALE_RETURN = 'company.ref.sale_return';
    public const KEY_COMPANY_REF_EXPENSE = 'company.ref.expense';
    public const KEY_COMPANY_REF_PAY_DEBT = 'company.ref.pay_debt';
    public const KEY_COMPANY_REF_RECEIVE_DEBT = 'company.ref.receive_debt';
    public const KEY_COMPANY_REF_CASH_BANK_MUTATION = 'company.ref.cash_bank_mutation';
    public const KEY_COMPANY_REF_STOCK_ADJUSTMENT = 'company.ref.stock_adjustment';
    public const KEY_COMPANY_REF_POINT_REDEMPTION = 'company.ref.point_redemption';
 
    public const KEY_PRINTER_MODE = 'printer.mode'; // browser|bridge
    public const KEY_PRINTER_BRIDGE_URL = 'printer.bridge_url';
    public const KEY_PRINTER_NAME = 'printer.name';
    public const KEY_PRINTER_PAPER_WIDTH = 'printer.paper_width_mm'; // 58|80
    public const KEY_PRINTER_COPIES = 'printer.copies'; // int >= 1
    public const KEY_PRINTER_HEADER_TEXT = 'printer.header_text';
    public const KEY_PRINTER_FOOTER_TEXT = 'printer.footer_text';
    public const KEY_PRINTER_SHOW_STORE_NAME = 'printer.show_store_name'; // 0|1
    public const KEY_PRINTER_SHOW_DATETIME = 'printer.show_datetime'; // 0|1
    public const KEY_PRINTER_AUTO_PRINT = 'printer.auto_print'; // 0|1
 
    public function edit(Request $request)
    {
        $settingsTableReady = Schema::hasTable('pos_settings');
 
        $backups = [];
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => Storage::size($file),
                    'created_at' => date('Y-m-d H:i:s', Storage::lastModified($file)),
                ];
            }
            usort($backups, fn ($a, $b) => strcmp($b['created_at'], $a['created_at']));
        }

        return view('settings.printer', [
            'backups' => $backups,
            'settingsTableReady' => $settingsTableReady,
            'activeTab' => $request->query('tab', $request->old('setting_section', session('active_settings_tab', 'company'))),
            'company' => [
                'name' => PosSetting::getString(self::KEY_COMPANY_NAME, 'UTE Parts'),
                'address' => PosSetting::getString(self::KEY_COMPANY_ADDRESS, ''),
                'city' => PosSetting::getString(self::KEY_COMPANY_CITY, ''),
                'province' => PosSetting::getString(self::KEY_COMPANY_PROVINCE, ''),
                'country' => PosSetting::getString(self::KEY_COMPANY_COUNTRY, 'Indonesia'),
                'location_code' => PosSetting::getString(self::KEY_COMPANY_LOCATION_CODE, '01'),
                'tax_number' => PosSetting::getString(self::KEY_COMPANY_TAX_NUMBER, ''),
                'taxable_number' => PosSetting::getString(self::KEY_COMPANY_TAXABLE_NUMBER, ''),
                'slogan' => PosSetting::getString(self::KEY_COMPANY_SLOGAN, ''),
                'references' => self::referenceSettings(),
            ],
            'printer' => [
                'mode' => PosSetting::getString(self::KEY_PRINTER_MODE, 'browser'),
                'bridge_url' => PosSetting::getString(self::KEY_PRINTER_BRIDGE_URL, ''),
                'printer_name' => PosSetting::getString(self::KEY_PRINTER_NAME, ''),
                'paper_width_mm' => PosSetting::getInt(self::KEY_PRINTER_PAPER_WIDTH, 80),
                'copies' => max(1, PosSetting::getInt(self::KEY_PRINTER_COPIES, 1)),
                'header_text' => PosSetting::getString(self::KEY_PRINTER_HEADER_TEXT, ''),
                'footer_text' => PosSetting::getString(self::KEY_PRINTER_FOOTER_TEXT, 'Terima kasih.'),
                'show_store_name' => (bool) PosSetting::getInt(self::KEY_PRINTER_SHOW_STORE_NAME, 1),
                'show_datetime' => (bool) PosSetting::getInt(self::KEY_PRINTER_SHOW_DATETIME, 1),
                'auto_print' => (bool) PosSetting::getInt(self::KEY_PRINTER_AUTO_PRINT, 0),
            ],
        ]);
    }

    public function update(Request $request)
    {
        if (! Schema::hasTable('pos_settings')) {
            return back()->with('error', 'Pengaturan belum bisa disimpan karena tabel database belum tersedia.');
        }

        $section = $request->input('setting_section', 'printer');

        if ($section === 'company') {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:1000',
                'city' => 'nullable|string|max:100',
                'province' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'location_code' => 'nullable|string|max:50',
                'tax_number' => 'nullable|string|max:100',
                'taxable_number' => 'nullable|string|max:100',
                'slogan' => 'nullable|string|max:255',
                'ref_purchase' => 'nullable|string|max:20',
                'ref_purchase_return' => 'nullable|string|max:20',
                'ref_receive_return_item' => 'nullable|string|max:20',
                'ref_store_sale' => 'nullable|string|max:20',
                'ref_sale_return' => 'nullable|string|max:20',
                'ref_expense' => 'nullable|string|max:20',
                'ref_pay_debt' => 'nullable|string|max:20',
                'ref_receive_debt' => 'nullable|string|max:20',
                'ref_cash_bank_mutation' => 'nullable|string|max:20',
                'ref_stock_adjustment' => 'nullable|string|max:20',
                'ref_point_redemption' => 'nullable|string|max:20',
            ]);

            PosSetting::setString(self::KEY_COMPANY_NAME, (string) $validated['name']);
            PosSetting::setString(self::KEY_COMPANY_ADDRESS, (string) ($validated['address'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_CITY, (string) ($validated['city'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_PROVINCE, (string) ($validated['province'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_COUNTRY, (string) ($validated['country'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_LOCATION_CODE, (string) ($validated['location_code'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_TAX_NUMBER, (string) ($validated['tax_number'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_TAXABLE_NUMBER, (string) ($validated['taxable_number'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_SLOGAN, (string) ($validated['slogan'] ?? ''));
            PosSetting::setString(self::KEY_COMPANY_REF_PURCHASE, (string) ($validated['ref_purchase'] ?? 'R21'));
            PosSetting::setString(self::KEY_COMPANY_REF_PURCHASE_RETURN, (string) ($validated['ref_purchase_return'] ?? 'R22'));
            PosSetting::setString(self::KEY_COMPANY_REF_RECEIVE_RETURN_ITEM, (string) ($validated['ref_receive_return_item'] ?? 'R23'));
            PosSetting::setString(self::KEY_COMPANY_REF_STORE_SALE, (string) ($validated['ref_store_sale'] ?? 'R43'));
            PosSetting::setString(self::KEY_COMPANY_REF_SALE_RETURN, (string) ($validated['ref_sale_return'] ?? 'R44'));
            PosSetting::setString(self::KEY_COMPANY_REF_EXPENSE, (string) ($validated['ref_expense'] ?? 'R31'));
            PosSetting::setString(self::KEY_COMPANY_REF_PAY_DEBT, (string) ($validated['ref_pay_debt'] ?? 'R32'));
            PosSetting::setString(self::KEY_COMPANY_REF_RECEIVE_DEBT, (string) ($validated['ref_receive_debt'] ?? 'R33'));
            PosSetting::setString(self::KEY_COMPANY_REF_CASH_BANK_MUTATION, (string) ($validated['ref_cash_bank_mutation'] ?? 'R34'));
            PosSetting::setString(self::KEY_COMPANY_REF_STOCK_ADJUSTMENT, (string) ($validated['ref_stock_adjustment'] ?? 'R35'));
            PosSetting::setString(self::KEY_COMPANY_REF_POINT_REDEMPTION, (string) ($validated['ref_point_redemption'] ?? 'R36'));

            return redirect()
                ->route('settings.printer.edit', ['tab' => 'company'])
                ->with('active_settings_tab', 'company')
                ->with('success', 'Setting company berhasil disimpan.');
        }

        $validated = $request->validate([
            'mode' => 'required|in:browser,bridge',
            'bridge_url' => 'nullable|string|max:255',
            'printer_name' => 'nullable|string|max:255',
            'paper_width_mm' => 'required|in:58,80',
            'copies' => 'required|integer|min:1|max:5',
            'header_text' => 'nullable|string|max:1000',
            'footer_text' => 'nullable|string|max:1000',
            'show_store_name' => 'nullable|boolean',
            'show_datetime' => 'nullable|boolean',
            'auto_print' => 'nullable|boolean',
        ]);

        // Basic sanity: bridge_url is required only when mode=bridge
        if (($validated['mode'] ?? 'browser') === 'bridge' && blank($validated['bridge_url'] ?? null)) {
            return back()
                ->withErrors(['bridge_url' => 'Bridge URL wajib diisi jika mode cetak memakai Printer Bridge.'])
                ->withInput()
                ->with('active_settings_tab', 'printer');
        }

        PosSetting::setString(self::KEY_PRINTER_MODE, (string) $validated['mode']);
        PosSetting::setString(self::KEY_PRINTER_BRIDGE_URL, (string) ($validated['bridge_url'] ?? ''));
        PosSetting::setString(self::KEY_PRINTER_NAME, (string) ($validated['printer_name'] ?? ''));
        PosSetting::setString(self::KEY_PRINTER_PAPER_WIDTH, (string) $validated['paper_width_mm']);
        PosSetting::setString(self::KEY_PRINTER_COPIES, (string) $validated['copies']);
        PosSetting::setString(self::KEY_PRINTER_HEADER_TEXT, (string) ($validated['header_text'] ?? ''));
        PosSetting::setString(self::KEY_PRINTER_FOOTER_TEXT, (string) ($validated['footer_text'] ?? ''));
        PosSetting::setString(self::KEY_PRINTER_SHOW_STORE_NAME, (string) ((int) (bool) ($validated['show_store_name'] ?? false)));
        PosSetting::setString(self::KEY_PRINTER_SHOW_DATETIME, (string) ((int) (bool) ($validated['show_datetime'] ?? false)));
        PosSetting::setString(self::KEY_PRINTER_AUTO_PRINT, (string) ((int) (bool) ($validated['auto_print'] ?? false)));

        return redirect()
            ->route('settings.printer.edit', ['tab' => 'printer'])
            ->with('active_settings_tab', 'printer')
            ->with('success', 'Setting printer berhasil disimpan.');
    }

    public function testPrint(Request $request)
    {
        $paperWidth = max(58, min(80, PosSetting::getInt(self::KEY_PRINTER_PAPER_WIDTH, 80)));
        $copies = max(1, PosSetting::getInt(self::KEY_PRINTER_COPIES, 1));
        $headerText = PosSetting::getString(self::KEY_PRINTER_HEADER_TEXT, '');
        $footerText = PosSetting::getString(self::KEY_PRINTER_FOOTER_TEXT, 'Terima kasih.');
        $showStoreName = (bool) PosSetting::getInt(self::KEY_PRINTER_SHOW_STORE_NAME, 1);
        $showDatetime = (bool) PosSetting::getInt(self::KEY_PRINTER_SHOW_DATETIME, 1);
        $autoPrint = (bool) PosSetting::getInt(self::KEY_PRINTER_AUTO_PRINT, 0);
        $company = $this->companySettings();

        return view('settings.printer-test', [
            'printer' => [
                'paper_width_mm' => in_array($paperWidth, [58, 80], true) ? $paperWidth : 80,
                'copies' => $copies,
                'header_text' => $headerText,
                'footer_text' => $footerText,
                'show_store_name' => $showStoreName,
                'show_datetime' => $showDatetime,
                'auto_print' => $autoPrint,
            ],
            'company' => $company,
            'cashier_name' => auth()->user()?->name ?: '-',
            'customer_name' => '-',
        ]);
    }

    public static function companySettings(): array
    {
        return [
            'name' => PosSetting::getString(self::KEY_COMPANY_NAME, 'UTE Parts'),
            'address' => PosSetting::getString(self::KEY_COMPANY_ADDRESS, ''),
            'city' => PosSetting::getString(self::KEY_COMPANY_CITY, ''),
            'province' => PosSetting::getString(self::KEY_COMPANY_PROVINCE, ''),
            'country' => PosSetting::getString(self::KEY_COMPANY_COUNTRY, 'Indonesia'),
            'location_code' => PosSetting::getString(self::KEY_COMPANY_LOCATION_CODE, '01'),
            'tax_number' => PosSetting::getString(self::KEY_COMPANY_TAX_NUMBER, ''),
            'taxable_number' => PosSetting::getString(self::KEY_COMPANY_TAXABLE_NUMBER, ''),
            'slogan' => PosSetting::getString(self::KEY_COMPANY_SLOGAN, ''),
            'references' => self::referenceSettings(),
        ];
    }

    public static function referenceSettings(): array
    {
        return [
            'purchase' => PosSetting::getString(self::KEY_COMPANY_REF_PURCHASE, 'R21'),
            'purchase_return' => PosSetting::getString(self::KEY_COMPANY_REF_PURCHASE_RETURN, 'R22'),
            'receive_return_item' => PosSetting::getString(self::KEY_COMPANY_REF_RECEIVE_RETURN_ITEM, 'R23'),
            'store_sale' => PosSetting::getString(self::KEY_COMPANY_REF_STORE_SALE, 'R43'),
            'sale_return' => PosSetting::getString(self::KEY_COMPANY_REF_SALE_RETURN, 'R44'),
            'expense' => PosSetting::getString(self::KEY_COMPANY_REF_EXPENSE, 'R31'),
            'pay_debt' => PosSetting::getString(self::KEY_COMPANY_REF_PAY_DEBT, 'R32'),
            'receive_debt' => PosSetting::getString(self::KEY_COMPANY_REF_RECEIVE_DEBT, 'R33'),
            'cash_bank_mutation' => PosSetting::getString(self::KEY_COMPANY_REF_CASH_BANK_MUTATION, 'R34'),
            'stock_adjustment' => PosSetting::getString(self::KEY_COMPANY_REF_STOCK_ADJUSTMENT, 'R35'),
            'point_redemption' => PosSetting::getString(self::KEY_COMPANY_REF_POINT_REDEMPTION, 'R36'),
        ];
    }

    public static function referencePrefix(string $key, string $fallback): string
    {
        $references = self::referenceSettings();
        $prefix = strtoupper(trim((string) ($references[$key] ?? $fallback)));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix) ?: strtoupper($fallback);

        return $prefix;
    }

    public function createBackup(DatabaseBackupService $backupService)
    {
        try {
            $fileName = $backupService->backup();
            
            // Log user activity
            \App\Models\UserLog::log('DATABASE_BACKUP', "Created database backup file: {$fileName}");

            return redirect()->route('settings.index', ['tab' => 'backup'])
                ->with('success', "Backup database berhasil dibuat: {$fileName}");
        } catch (\Throwable $e) {
            return redirect()->route('settings.index', ['tab' => 'backup'])
                ->with('error', "Gagal membuat backup: " . $e->getMessage());
        }
    }

    public function restoreBackup(string $filename, DatabaseBackupService $backupService)
    {
        try {
            $backupService->restore($filename);

            // Log user activity
            \App\Models\UserLog::log('DATABASE_RESTORE', "Restored database using file: {$filename}");

            return redirect()->route('settings.index', ['tab' => 'backup'])
                ->with('success', "Restore database dari file {$filename} berhasil dilakukan.");
        } catch (\Throwable $e) {
            return redirect()->route('settings.index', ['tab' => 'backup'])
                ->with('error', "Gagal melakukan restore: " . $e->getMessage());
        }
    }

    public function downloadBackup(string $filename)
    {
        $path = 'backups/' . $filename;
        if (!Storage::exists($path)) {
            return redirect()->route('settings.index', ['tab' => 'backup'])
                ->with('error', 'File backup tidak ditemukan.');
        }

        return Storage::download($path);
    }

    public function deleteBackup(string $filename)
    {
        $path = 'backups/' . $filename;
        if (!Storage::exists($path)) {
            return redirect()->route('settings.index', ['tab' => 'backup'])
                ->with('error', 'File backup tidak ditemukan.');
        }

        Storage::delete($path);
        
        // Log user activity
        \App\Models\UserLog::log('DATABASE_BACKUP_DELETE', "Deleted database backup file: {$filename}");

        return redirect()->route('settings.index', ['tab' => 'backup'])
            ->with('success', "File backup {$filename} berhasil dihapus.");
    }
}

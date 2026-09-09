<?php

namespace App\Http\Livewire;

use App\Http\Controllers\PrinterSettingController;

use Livewire\Component;
use App\Models\PosSetting;
use App\Services\DatabaseBackupService;

class PrinterSettingsComponent extends Component
{
    // Company fields
    public $company_name;
    public $company_address;
    public $company_city;
    public $company_province;
    public $company_country = 'Indonesia';
    public $company_location_code = '01';
    public $company_tax_number;
    public $company_taxable_number;
    public $company_slogan;
    public $ref_purchase;
    public $ref_purchase_return;
    public $ref_receive_return_item;
    public $ref_store_sale;
    public $ref_sale_return;
    public $ref_expense;
    public $ref_pay_debt;
    public $ref_receive_debt;
    public $ref_cash_bank_mutation;
    public $ref_stock_adjustment;
    public $ref_point_redemption;

    // Printer fields
    public $printer_mode;
    public $printer_bridge_url;
    public $printer_name;
    public $printer_paper_width_mm;
    public $printer_copies;
    public $printer_header_text;
    public $printer_footer_text;
    public $show_store_name;
    public $show_datetime;
    public $auto_print;

    // Tabs & backups
    public $activeTab = 'company';
    public $backups = [];

    public function mount()
    {
        // Load company settings
        $this->company_name = PosSetting::getString(PrinterSettingController::KEY_COMPANY_NAME, 'UTE Parts');
        $this->company_address = PosSetting::getString(PrinterSettingController::KEY_COMPANY_ADDRESS, '');
        $this->company_city = PosSetting::getString(PrinterSettingController::KEY_COMPANY_CITY, '');
        $this->company_province = PosSetting::getString(PrinterSettingController::KEY_COMPANY_PROVINCE, '');
        $this->company_country = PosSetting::getString(PrinterSettingController::KEY_COMPANY_COUNTRY, 'Indonesia');
        $this->company_location_code = PosSetting::getString(PrinterSettingController::KEY_COMPANY_LOCATION_CODE, '01');
        $this->company_tax_number = PosSetting::getString(PrinterSettingController::KEY_COMPANY_TAX_NUMBER, '');
        $this->company_taxable_number = PosSetting::getString(PrinterSettingController::KEY_COMPANY_TAXABLE_NUMBER, '');
        $this->company_slogan = PosSetting::getString(PrinterSettingController::KEY_COMPANY_SLOGAN, '');
        $refs = PrinterSettingController::referenceSettings();
        $this->ref_purchase = $refs['purchase'];
        $this->ref_purchase_return = $refs['purchase_return'];
        $this->ref_receive_return_item = $refs['receive_return_item'];
        $this->ref_store_sale = $refs['store_sale'];
        $this->ref_sale_return = $refs['sale_return'];
        $this->ref_expense = $refs['expense'];
        $this->ref_pay_debt = $refs['pay_debt'];
        $this->ref_receive_debt = $refs['receive_debt'];
        $this->ref_cash_bank_mutation = $refs['cash_bank_mutation'];
        $this->ref_stock_adjustment = $refs['stock_adjustment'];
        $this->ref_point_redemption = $refs['point_redemption'];

        // Load printer settings
        $this->printer_mode = PosSetting::getString(PrinterSettingController::KEY_PRINTER_MODE, 'browser');
        $this->printer_bridge_url = PosSetting::getString(PrinterSettingController::KEY_PRINTER_BRIDGE_URL, '');
        $this->printer_name = PosSetting::getString(PrinterSettingController::KEY_PRINTER_NAME, '');
        $this->printer_paper_width_mm = PosSetting::getInt(PrinterSettingController::KEY_PRINTER_PAPER_WIDTH, 80);
        $this->printer_copies = max(1, PosSetting::getInt(PrinterSettingController::KEY_PRINTER_COPIES, 1));
        $this->printer_header_text = PosSetting::getString(PrinterSettingController::KEY_PRINTER_HEADER_TEXT, '');
        $this->printer_footer_text = PosSetting::getString(PrinterSettingController::KEY_PRINTER_FOOTER_TEXT, 'Terima kasih.');
        $this->show_store_name = (bool) PosSetting::getInt(PrinterSettingController::KEY_PRINTER_SHOW_STORE_NAME, 1);
        $this->show_datetime = (bool) PosSetting::getInt(PrinterSettingController::KEY_PRINTER_SHOW_DATETIME, 1);
        $this->auto_print = (bool) PosSetting::getInt(PrinterSettingController::KEY_PRINTER_AUTO_PRINT, 0);

        // Load backups
        if (\Illuminate\Support\Facades\Storage::exists('backups')) {
            $files = \Illuminate\Support\Facades\Storage::files('backups');
            foreach ($files as $file) {
                $this->backups[] = [
                    'filename' => basename($file),
                    'size' => \Illuminate\Support\Facades\Storage::size($file),
                    'created_at' => date('Y-m-d H:i:s', \Illuminate\Support\Facades\Storage::lastModified($file)),
                ];
            }
            usort($this->backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        }
    }

    public function saveCompany()
    {
        $validated = $this->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'company_city' => 'nullable|string|max:100',
            'company_province' => 'nullable|string|max:100',
            'company_country' => 'nullable|string|max:100',
            'company_location_code' => 'nullable|string|max:50',
            'company_tax_number' => 'nullable|string|max:100',
            'company_taxable_number' => 'nullable|string|max:100',
            'company_slogan' => 'nullable|string|max:255',
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

        PosSetting::setString(PrinterSettingController::KEY_COMPANY_NAME, (string) $validated['company_name']);
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_ADDRESS, (string) ($validated['company_address'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_CITY, (string) ($validated['company_city'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_PROVINCE, (string) ($validated['company_province'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_COUNTRY, (string) ($validated['company_country'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_LOCATION_CODE, (string) ($validated['company_location_code'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_TAX_NUMBER, (string) ($validated['company_tax_number'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_TAXABLE_NUMBER, (string) ($validated['company_taxable_number'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_SLOGAN, (string) ($validated['company_slogan'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_PURCHASE, (string) ($validated['ref_purchase'] ?? 'R21'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_PURCHASE_RETURN, (string) ($validated['ref_purchase_return'] ?? 'R22'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_RECEIVE_RETURN_ITEM, (string) ($validated['ref_receive_return_item'] ?? 'R23'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_STORE_SALE, (string) ($validated['ref_store_sale'] ?? 'R43'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_SALE_RETURN, (string) ($validated['ref_sale_return'] ?? 'R44'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_EXPENSE, (string) ($validated['ref_expense'] ?? 'R31'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_PAY_DEBT, (string) ($validated['ref_pay_debt'] ?? 'R32'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_RECEIVE_DEBT, (string) ($validated['ref_receive_debt'] ?? 'R33'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_CASH_BANK_MUTATION, (string) ($validated['ref_cash_bank_mutation'] ?? 'R34'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_STOCK_ADJUSTMENT, (string) ($validated['ref_stock_adjustment'] ?? 'R35'));
        PosSetting::setString(PrinterSettingController::KEY_COMPANY_REF_POINT_REDEMPTION, (string) ($validated['ref_point_redemption'] ?? 'R36'));

        session()->flash('success', 'Setting company berhasil disimpan.');
        return redirect()->route('settings.printer.edit', ['tab' => 'company']);
    }

    public function savePrinter()
    {
        $validated = $this->validate([
            'printer_mode' => 'required|in:browser,bridge',
            'printer_bridge_url' => 'nullable|string|max:255',
            'printer_name' => 'nullable|string|max:255',
            'printer_paper_width_mm' => 'required|in:58,80',
            'printer_copies' => 'required|integer|min:1|max:5',
            'printer_header_text' => 'nullable|string|max:1000',
            'printer_footer_text' => 'nullable|string|max:1000',
            'show_store_name' => 'nullable|boolean',
            'show_datetime' => 'nullable|boolean',
            'auto_print' => 'nullable|boolean',
        ]);

        if ($validated['printer_mode'] === 'bridge' && blank($validated['printer_bridge_url'] ?? null)) {
            $this->addError('printer_bridge_url', 'Bridge URL wajib diisi jika mode cetak memakai Printer Bridge.');
            return;
        }

        PosSetting::setString(PrinterSettingController::KEY_PRINTER_MODE, (string) $validated['printer_mode']);
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_BRIDGE_URL, (string) ($validated['printer_bridge_url'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_NAME, (string) ($validated['printer_name'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_PAPER_WIDTH, (string) $validated['printer_paper_width_mm']);
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_COPIES, (string) $validated['printer_copies']);
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_HEADER_TEXT, (string) ($validated['printer_header_text'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_FOOTER_TEXT, (string) ($validated['printer_footer_text'] ?? ''));
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_SHOW_STORE_NAME, (string) ((int) (bool) ($validated['show_store_name'] ?? false)));
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_SHOW_DATETIME, (string) ((int) (bool) ($validated['show_datetime'] ?? false)));
        PosSetting::setString(PrinterSettingController::KEY_PRINTER_AUTO_PRINT, (string) ((int) (bool) ($validated['auto_print'] ?? false)));

        session()->flash('success', 'Setting printer berhasil disimpan.');
        return redirect()->route('settings.printer.edit', ['tab' => 'printer']);
    }

    public function testPrint()
    {
        return redirect()->route('settings.printer.test-print');
    }

    public function createBackup()
    {
        $service = app(DatabaseBackupService::class);
        try {
            $fileName = $service->backup();
            session()->flash('success', "Backup database berhasil dibuat: {$fileName}");
        } catch (\Throwable $e) {
            session()->flash('error', "Gagal membuat backup: " . $e->getMessage());
        }
        return redirect()->route('settings.index', ['tab' => 'backup']);
    }

    public function restoreBackup(string $filename)
    {
        $service = app(DatabaseBackupService::class);
        try {
            $service->restore($filename);
            session()->flash('success', "Restore database dari file {$filename} berhasil dilakukan.");
        } catch (\Throwable $e) {
            session()->flash('error', "Gagal melakukan restore: " . $e->getMessage());
        }
        return redirect()->route('settings.index', ['tab' => 'backup']);
    }

    public function downloadBackup(string $filename)
    {
        $path = 'backups/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            session()->flash('error', 'File backup tidak ditemukan.');
            return redirect()->route('settings.index', ['tab' => 'backup']);
        }
        return \Illuminate\Support\Facades\Storage::download($path);
    }

    public function deleteBackup(string $filename)
    {
        $path = 'backups/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            session()->flash('error', 'File backup tidak ditemukan.');
            return redirect()->route('settings.index', ['tab' => 'backup']);
        }
        \Illuminate\Support\Facades\Storage::delete($path);
        session()->flash('success', "File backup {$filename} berhasil dihapus.");
        return redirect()->route('settings.index', ['tab' => 'backup']);
    }

    public function render()
    {
        return view('livewire.settings.printer-component');
    }
}

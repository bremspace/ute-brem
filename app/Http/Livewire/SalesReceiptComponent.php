<?php

namespace App\Http\Livewire;

use App\Http\Controllers\PrinterSettingController;
use App\Models\PosSetting;
use App\Models\Sale;
use Livewire\Component;

class SalesReceiptComponent extends Component
{
    public int $saleId;

    public function mount(int $saleId): void
    {
        $this->saleId = $saleId;
    }

    public function render()
    {
        $sale = Sale::with(['items.product', 'customer', 'location', 'cashier'])
            ->find($this->saleId);

        if (! $sale) {
            return view('livewire.sales.sales-receipt-component', [
                'sale' => null,
                'company' => [],
                'printer' => ['paper_width_mm' => 80],
            ]);
        }

        $paperWidth = max(58, min(80, PosSetting::getInt(PrinterSettingController::KEY_PRINTER_PAPER_WIDTH, 80)));
        $copies = max(1, PosSetting::getInt(PrinterSettingController::KEY_PRINTER_COPIES, 1));
        $headerText = PosSetting::getString(PrinterSettingController::KEY_PRINTER_HEADER_TEXT, '');
        $footerText = PosSetting::getString(PrinterSettingController::KEY_PRINTER_FOOTER_TEXT, 'Terima kasih.');
        $showStoreName = (bool) PosSetting::getInt(PrinterSettingController::KEY_PRINTER_SHOW_STORE_NAME, 1);
        $showDatetime = (bool) PosSetting::getInt(PrinterSettingController::KEY_PRINTER_SHOW_DATETIME, 1);
        $autoPrint = (bool) PosSetting::getInt(PrinterSettingController::KEY_PRINTER_AUTO_PRINT, 0);
        $company = PrinterSettingController::companySettings();

        return view('livewire.sales.sales-receipt-component', [
            'sale' => $sale,
            'company' => $company,
            'printer' => [
                'paper_width_mm' => in_array($paperWidth, [58, 80], true) ? $paperWidth : 80,
                'copies' => $copies,
                'header_text' => $headerText,
                'footer_text' => $footerText,
                'show_store_name' => $showStoreName,
                'show_datetime' => $showDatetime,
                'auto_print' => $autoPrint,
            ],
        ]);
    }
}

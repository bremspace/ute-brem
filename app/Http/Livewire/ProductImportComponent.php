<?php

namespace App\Http\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Location;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProductImportComponent extends Component
{
    use WithFileUploads;

    public ?\Livewire\TemporaryUploadedFile $file = null;
    public array $preview = [];
    public bool $showPreview = false;
    public bool $showResult = false;
    public int $createdCount = 0;
    public int $skippedCount = 0;

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:10240',
    ];

    public function previewImport(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $spreadsheet = IOFactory::load($this->file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $header = array_shift($rows);

        $preview = [];
        $valid = 0;
        $invalid = 0;

        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) continue;
            $data = array_combine($header, $row);
            $errors = [];

            if (empty($data['name'] ?? '')) $errors[] = 'Nama wajib diisi.';
            if (empty($data['product_code'] ?? '')) $errors[] = 'Kode produk wajib diisi.';

            if (!empty($errors)) $invalid++; else $valid++;

            $preview[] = [
                'row_number' => $index + 2,
                'status' => empty($errors) ? 'valid' : 'error',
                'data' => $data,
                'errors' => $errors,
            ];
        }

        $this->preview = [
            'rows' => $preview,
            'total' => count($preview),
            'valid' => $valid,
            'invalid' => $invalid,
            'has_errors' => $invalid > 0,
            'previewed_at' => now()->format('d/m/Y H:i'),
        ];
        $this->showPreview = true;
    }

    public function storeImport(): void
    {
        if (!$this->showPreview || empty($this->preview['rows'])) return;

        foreach ($this->preview['rows'] as $row) {
            if ($row['status'] !== 'valid') continue;
            $data = $row['data'];
            try {
                Product::create([
                    'product_code' => $data['product_code'],
                    'name' => $data['name'],
                    'purchase_price' => $data['purchase_price'] ?? 0,
                    'selling_price' => $data['selling_price'] ?? 0,
                    'stock_global' => $data['stock_global'] ?? 0,
                    'is_active' => true,
                ]);
                $this->createdCount++;
            } catch (\Throwable $e) {
                $this->skippedCount++;
            }
        }

        $this->showResult = true;
        $this->showPreview = false;
        $this->file = null;
    }

    public function downloadTemplate(): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = ['product_code', 'name', 'purchase_price', 'selling_price', 'stock_global'];
        foreach ($headers as $i => $h) $sheet->setCellValueByColumnAndRow($i + 1, 1, $h);

        $sample = ['PRD-001', 'Contoh Produk', 50000, 75000, 100];
        foreach ($sample as $i => $v) $sheet->setCellValueByColumnAndRow($i + 1, 2, $v);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template-import-produk.xlsx"');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    public function render()
    {
        return view('livewire.products.product-import-component');
    }
}
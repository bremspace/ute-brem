<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomerImportComponent extends Component
{
    use WithFileUploads;

    public ?\Livewire\TemporaryUploadedFile $file = null;
    public array $preview = [];
    public bool $showPreview = false;
    public bool $showResult = false;
    public int $createdCount = 0;
    public int $skippedCount = 0;

    public array $customerGroups = [];
    public int $defaultGroupId = 0;

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
    ];

    public function mount(): void
    {
        $this->customerGroups = CustomerGroup::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function previewImport(): void
    {
        $this->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $spreadsheet = IOFactory::load($this->file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $header = array_shift($rows);
        $preview = [];
        $valid = 0;
        $invalid = 0;

        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) {
                continue;
            }

            $data = array_combine($header, $row);
            $errors = [];

            if (empty($data['name'] ?? '')) {
                $errors[] = 'Nama wajib diisi.';
            }
            if (empty($data['type'] ?? '')) {
                $errors[] = 'Tipe wajib diisi.';
            }
            if ($data['type'] ?? '') {
                if ($data['type'] === 'member' && empty($data['password'] ?? '')) {
                    $errors[] = 'Password wajib diisi untuk type member.';
                }
            }

            if (! empty($errors)) {
                $invalid++;
            } else {
                $valid++;
            }

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
        if (! $this->showPreview || empty($this->preview['rows'])) {
            return;
        }

        foreach ($this->preview['rows'] as $row) {
            if ($row['status'] !== 'valid') {
                continue;
            }

            $data = $row['data'];
            try {
                Customer::create([
                    'customer_group_id' => $this->defaultGroupId,
                    'name' => $data['name'],
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'type' => $data['type'] ?? 'regular',
                    'password' => Hash::make($data['password'] ?? 'Member123!'),
                    'is_active' => ($data['is_active'] ?? 'ya') === 'ya',
                    'member_code' => 'MBR-' . str_pad(Customer::count() + 1, 6, '0', STR_PAD_LEFT),
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

        $sheet->setCellValue('A1', 'member_code');
        $sheet->setCellValue('B1', 'customer_group');
        $sheet->setCellValue('C1', 'name');
        $sheet->setCellValue('D1', 'phone');
        $sheet->setCellValue('E1', 'email');
        $sheet->setCellValue('F1', 'type');
        $sheet->setCellValue('G1', 'password');
        $sheet->setCellValue('H1', 'is_active');

        $sheet->setCellValue('A2', 'MBR-000111');
        $sheet->setCellValue('B2', 'Member Gold');
        $sheet->setCellValue('C2', 'Fajar Pratama');
        $sheet->setCellValue('D2', '081234567890');
        $sheet->setCellValue('E2', 'fajar@example.com');
        $sheet->setCellValue('F2', 'member');
        $sheet->setCellValue('G2', 'Member123!');
        $sheet->setCellValue('H2', 'ya');

        $sheet->setCellValue('A3', '');
        $sheet->setCellValue('B3', 'Retail');
        $sheet->setCellValue('C3', 'Rina Toko');
        $sheet->setCellValue('D3', '081277788899');
        $sheet->setCellValue('E3', '');
        $sheet->setCellValue('F3', 'regular');
        $sheet->setCellValue('G3', '');
        $sheet->setCellValue('H3', 'ya');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template-import-customer.xlsx"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function render()
    {
        return view('livewire.customers.customer-import-component', [
            'customerGroups' => $this->customerGroups,
        ]);
    }
}
<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use Livewire\Component;

class CustomerExportComponent extends Component
{
    public string $scope = 'all';
    public ?int $selectedCount = 0;
    public array $selectedIds = [];

    protected $rules = [
        'scope' => 'required|in:all,selected',
    ];

    public function export(): void
    {
        $this->validate();

        $query = Customer::query();

        if ($this->scope === 'selected' && ! empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        }

        $customers = $query->orderBy('name')->get();

        if ($customers->isEmpty()) {
            session()->flash('error', 'Tidak ada data customer untuk diekspor.');
            return;
        }

        // Generate CSV
        $filename = 'customer-export-' . now()->format('YmdHis') . '.csv';
        $handle = fopen('php://memory', 'r+');

        // Headers
        fputcsv($handle, ['ID', 'Kode Member', 'Nama', 'No HP', 'Email', 'Tipe', 'Total Poin', 'Group', 'Status', 'Created At']);

        foreach ($customers as $customer) {
            fputcsv($handle, [
                $customer->id,
                $customer->member_code ?? '',
                $customer->name,
                $customer->phone ?? '',
                $customer->email ?? '',
                $customer->type,
                $customer->points_balance ?? 0,
                $customer->group?->name ?? '',
                $customer->is_active ? 'Active' : 'Inactive',
                $customer->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response()->make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function render()
    {
        return view('livewire.customers.customer-export-component');
    }
}
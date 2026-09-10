<?php

namespace App\Http\Livewire;

use App\Models\BackOfficeCostCategory;
use Livewire\Component;

class CostCategoriesComponent extends Component
{
    public string $code = '';
    public string $name = '';
    public string $type = 'expense';
    public bool $isActive = true;

    public bool $showFormModal = false;

    protected $rules = [
        'code' => 'required|string|max:50|unique:back_office_cost_categories,code',
        'name' => 'required|string|max:255',
        'type' => 'required|in:income,expense',
        'isActive' => 'boolean',
    ];

    public function openFormModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function resetForm(): void
    {
        $this->code = '';
        $this->name = '';
        $this->type = 'expense';
        $this->isActive = true;
    }

    public function store(): void
    {
        $validated = $this->validate();

        BackOfficeCostCategory::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => (bool) ($validated['isActive']),
        ]);

        session()->flash('success', 'Data biaya berhasil ditambahkan.');
        $this->showFormModal = false;
    }

    public function toggleStatus(int $categoryId): void
    {
        $category = BackOfficeCostCategory::find($categoryId);
        if ($category) {
            $category->update(['is_active' => ! $category->is_active]);
        }
    }

    public function getCategoriesProperty()
    {
        return BackOfficeCostCategory::orderBy('type')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.back-office.cost-categories-component');
    }
}
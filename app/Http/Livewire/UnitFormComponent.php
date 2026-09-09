<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Unit;
use App\Models\UserLog;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UnitFormComponent extends Component
{
    public $unitId = null;
    public $name = '';
    public $code = '';
    public $is_active = false;

    public function getRulesProperty(): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('units', 'name')->ignore($this->unitId)],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('units', 'code')->ignore($this->unitId)],
            'is_active' => 'nullable|boolean',
        ];
    }

    public function mount($unitId = null)
    {
        $this->unitId = $unitId;
        if ($unitId) {
            $unit = Unit::findOrFail($unitId);
            $this->name = $unit->name;
            $this->code = $unit->code;
            $this->is_active = (bool) $unit->is_active;
        }
    }

    public function updatedName($value)
    {
        $this->code = strtoupper(substr($value, 0, 20));
    }

    public function save()
    {
        $data = $this->validate();
        $data['name'] = strtoupper($data['name']);
        $data['code'] = $data['code'] ? strtoupper($data['code']) : strtoupper(substr($data['name'], 0, 20));
        $data['is_active'] = $data['is_active'] ?? false;

        if ($this->unitId) {
            $unit = Unit::findOrFail($this->unitId);
            $old = $unit->only(['name', 'code', 'is_active']);
            $unit->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => $data['is_active'],
            ]);
            UserLog::log('UPDATE_UNIT', "Updated unit: {$unit->name}", null, $old, $unit->fresh()->only(['name', 'code', 'is_active']));
        } else {
            $unit = Unit::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => $data['is_active'],
            ]);
            UserLog::log('CREATE_UNIT', "Created unit: {$unit->name}", null, null, $unit->only(['name', 'code', 'is_active']));
        }
        return redirect()->route('units.index');
    }

    public function render()
    {
        return view('livewire.units.unit-form-component');
    }
}

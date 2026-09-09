<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Location;
use App\Models\Branch;
use App\Models\LocationRack;
use App\Models\UserLog;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LocationFormComponent extends Component
{
    public $locationId = null;
    public $name = '';
    public $code = '';
    public $branch_id = null;
    public $racks = [];
    public $racks_text = '';
    public $is_active = false;
    public $branches = [];

    public function getRulesProperty(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($this->locationId)],
            'code' => ['required', 'string', 'max:50', Rule::unique('locations', 'code')->ignore($this->locationId)],
            'branch_id' => $this->locationId ? 'required|exists:branches,id' : 'nullable|exists:branches,id',
            'racks' => 'nullable|array',
            'racks.*' => 'nullable|string|max:100',
            'racks_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function mount($locationId = null)
    {
        $this->branches = Branch::where('is_active', true)->orderBy('name')->get();
        $this->locationId = $locationId;
        if ($locationId) {
            $location = Location::with('racks')->findOrFail($locationId);
            $this->name = $location->name;
            $this->code = $location->code;
            $this->branch_id = $location->branch_id;
            $this->is_active = (bool) $location->is_active;
            $this->racks = $location->racks->pluck('name')->toArray();
        }
    }

    public function save()
    {
        $data = $this->validate();
        $data['code'] = strtoupper($data['code'] ?? $this->code);
        $data['is_active'] = $data['is_active'] ?? false;
        $rackNames = $this->extractRackNames($data);
        if ($this->locationId) {
            $location = Location::findOrFail($this->locationId);
            $old = $location->only(['name', 'code', 'branch_id', 'is_active']);
            $location->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'branch_id' => $data['branch_id'],
                'is_active' => $data['is_active'],
            ]);
            $this->syncRacks($location, $rackNames);
            UserLog::log('UPDATE_LOCATION', "Updated location: {$location->name}", null, $old, $location->fresh('racks')->toArray());
        } else {
            $branchId = $data['branch_id'] ?? auth()->user()->branch_id;
            if (! $branchId) {
                $branchId = Branch::where('is_main', true)->value('id');
            }
            $location = Location::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'branch_id' => $branchId,
                'is_active' => $data['is_active'],
            ]);
            $this->syncRacks($location, $rackNames);
            UserLog::log('CREATE_LOCATION', "Created location: {$location->name}", null, null, $location->fresh('racks')->toArray());
        }
        return redirect()->route('locations.index');
    }

    private function extractRackNames(array $validated): array
    {
        $items = $validated['racks'] ?? [];
        if (! empty($validated['racks_text'])) {
            $items = array_merge($items, preg_split('/\r\n|\r|\n|,/', $validated['racks_text']) ?: []);
        }
        return collect($items)
            ->map(fn($n) => trim((string) $n))
            ->filter()
            ->unique(fn($n) => mb_strtolower($n))
            ->values()
            ->all();
    }

    private function syncRacks(Location $location, array $rackNames): void
    {
        foreach ($rackNames as $rackName) {
            LocationRack::updateOrCreate(
                ['location_id' => $location->id, 'name' => $rackName],
                ['code' => strtoupper($location->code . '-' . str($rackName)->slug('-')), 'is_active' => true]
            );
        }
        if ($rackNames !== []) {
            LocationRack::where('location_id', $location->id)
                ->whereNotIn('name', $rackNames)
                ->update(['is_active' => false]);
        }
    }

    public function render()
    {
        return view('livewire.locations.location-form-component');
    }
}

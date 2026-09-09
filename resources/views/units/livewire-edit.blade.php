<!-- wrapper view for unit edit Livewire -->
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('error'))
        <<div class="alert alert-danger" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @livewire('unit-form-component', ['unitId' => $unit->id])
</div>

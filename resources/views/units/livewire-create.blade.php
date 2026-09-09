<!-- wrapper view for unit create Livewire -->
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @livewire('unit-form-component')
</div>

<!-- wrapper view for category index Livewire 
<div class="container-xxl flex-grow-1 container-p-y"
    @if(session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" role="file");
        <!-- etc etc etc...
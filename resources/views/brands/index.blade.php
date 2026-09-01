@extends('layouts.sneat')

@section('title', 'Brand')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Brand</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali ke Produk</a>
                @if(auth()->user()->hasPermission('master.brands.create'))<a href="{{ route('brands.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Tambah Brand</a>@endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table id="brands-table" class="table table-striped">
                    <thead><tr><th>ID</th><th>Nama</th><th>Slug</th><th>Jumlah Tipe</th><th>Status</th><th>Aksi</th></tr></thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function(){ $('#brands-table').DataTable({processing:true,serverSide:true,ajax:'{{ route('brands.data') }}',columns:[{data:'id',name:'id'},{data:'name',name:'name'},{data:'slug',name:'slug'},{data:'product_types_count',name:'product_types_count',searchable:false},{data:'status_badge',name:'is_active',orderable:false,searchable:false},{data:'action',name:'action',orderable:false,searchable:false}],dom:'lBfrtip',buttons:['copy','csv','excel','pdf','print','colvis']});});
</script>
@endpush

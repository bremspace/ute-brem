@extends('layouts.sneat')

@section('title', 'Master Jasa')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if (session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if (session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
        @if (!$servicesTableReady)
            <div class="alert alert-warning">Tabel jasa belum tersedia. Jalankan migration terlebih dahulu.</div>
        @endif

        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <h5 class="mb-1">Master Jasa</h5>
                    <div class="text-muted small">Daftar jasa service untuk transaksi service.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if (auth()->user()->hasPermission('master.products.create'))
                        <a href="{{ route('services.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Tambah Jasa</a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="services-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Jasa</th>
                                <th>Kategori</th>
                                <th>Golongan</th>
                                <th>Harga Toko</th>
                                <th>Open Price</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#services-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('services.data') }}',
                columns: [
                    { data: 'service_code', name: 'service_code' },
                    { data: 'name', name: 'name' },
                    { data: 'category', name: 'category', defaultContent: '-' },
                    { data: 'group', name: 'group', defaultContent: '-' },
                    { data: 'price_label', name: 'price_toko', searchable: false },
                    { data: 'open_price_badge', name: 'is_open_price', orderable: false, searchable: false },
                    { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                dom: 'lBfrtip',
                buttons: ['copy', 'excel', 'pdf', 'print', 'colvis']
            });
        });
    </script>
@endpush

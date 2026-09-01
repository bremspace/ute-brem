@extends('layouts.sneat')

@section('title', 'Purchase Order')

@push('styles')
<style>
    #purchase-orders-table {
        width: 100% !important;
    }

    #purchase-orders-table th,
    #purchase-orders-table td {
        vertical-align: middle;
    }

    .purchase-order-table-wrapper .dt-buttons {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        gap: .35rem;
    }

    .purchase-order-table-wrapper .dataTables_filter,
    .purchase-order-table-wrapper .dataTables_length {
        margin-bottom: .75rem;
    }

    .purchase-order-table-wrapper .dataTables_filter label,
    .purchase-order-table-wrapper .dataTables_length label {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-bottom: 0;
    }

    .purchase-order-table-wrapper .dataTables_filter input,
    .purchase-order-table-wrapper .dataTables_length select {
        margin-left: 0;
    }

    @media (max-width: 767.98px) {
        .purchase-order-table-wrapper .dt-buttons {
            justify-content: flex-start;
        }

        .purchase-order-table-wrapper .dataTables_filter label,
        .purchase-order-table-wrapper .dataTables_length label {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="mb-1">Riwayat Purchase Order</h5>
                <div class="text-muted small">Daftar PO dari notifikasi stok minimum dan restock manual.</div>
            </div>
        </div>
        <div class="card-body purchase-order-table-wrapper">
            <div class="table-responsive">
                <table id="purchase-orders-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>PO</th>
                            <th>Tanggal</th>
                            <th>Produk</th>
                            <th>Supplier</th>
                            <th>Lokasi</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Attachment</th>
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
$(function () {
    $('#purchase-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('purchase-orders.data') }}',
        order: [[0, 'desc']],
        autoWidth: false,
        scrollX: true,
        columns: [
            { data: 'po_info', name: 'po_number', width: '18%' },
            { data: 'ordered_at_label', name: 'ordered_at' },
            { data: 'product_info', name: 'product_id', searchable: false },
            { data: 'supplier_name', name: 'supplier_id', searchable: false },
            { data: 'location_name', name: 'location_id', searchable: false },
            { data: 'qty_label', name: 'quantity', searchable: false },
            { data: 'total_label', name: 'total_price', searchable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'attachments', name: 'files_count', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: '<"row align-items-center mb-3 g-2"<"col-lg-4"l><"col-lg-8 text-lg-end"B>><"row mb-3"<"col-12"f>>rt<"row align-items-center mt-3 g-2"<"col-md-6"i><"col-md-6"p>>',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis']
    });
});
</script>
@endpush

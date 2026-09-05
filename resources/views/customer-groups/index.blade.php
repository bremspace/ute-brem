@extends('layouts.sneat')

@section('title', 'Customer Group')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customer Group</h5>
            <div class="d-flex gap-2">
                <form id="customer-groups-export-form" action="{{ route('customer-groups.export') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="scope" id="customer-groups-export-scope" value="all">
                    <input type="hidden" name="selected_ids" id="customer-groups-selected-ids" value="">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-download me-1"></i>Export
                            <span id="customer-groups-selected-count" class="badge bg-white text-primary ms-1">0</span>
                        </button>
                        <div class="dropdown-menu">
                            <button type="button" id="export-all-customer-groups" class="dropdown-item">
                                <i class="bx bx-list-ul me-1"></i>Export Semua
                            </button>
                            <button type="button" id="export-selected-customer-groups" class="dropdown-item">
                                <i class="bx bx-check-square me-1"></i>Export Pilihan
                            </button>
                        </div>
                    </div>
                </form>
                @if(auth()->user()->hasPermission('master.customer_groups.create'))
                    <a href="{{ route('customer-groups.import') }}" class="btn btn-outline-primary"><i class="bx bx-upload me-1"></i>Import</a>
                    <a href="{{ route('customer-groups.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Tambah Group</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table id="customer-groups-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-customer-groups-page" class="form-check-input"></th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Urutan</th>
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
$(function () {
    const selectedIds = new Set();

    const table = $('#customer-groups-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('customer-groups.data') }}',
        columns: [
            { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'slug', name: 'slug' },
            { data: 'sort_order', name: 'sort_order' },
            { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: 'lBfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print', 'colvis'],
        drawCallback: function () {
            $('.row-export-checkbox').each(function () {
                $(this).prop('checked', selectedIds.has(String($(this).val())));
            });
            syncSelectAll();
        }
    });

    function updateSelectedCount() {
        $('#customer-groups-selected-count').text(selectedIds.size);
        $('#customer-groups-selected-ids').val(Array.from(selectedIds).join(','));
    }

    function syncSelectAll() {
        const checkboxes = $('.row-export-checkbox');
        const checked = checkboxes.filter(':checked').length;
        $('#select-customer-groups-page')
            .prop('checked', checkboxes.length > 0 && checked === checkboxes.length)
            .prop('indeterminate', checked > 0 && checked < checkboxes.length);
    }

    $('#customer-groups-table').on('change', '.row-export-checkbox', function () {
        const id = String($(this).val());
        if ($(this).is(':checked')) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        updateSelectedCount();
        syncSelectAll();
    });

    $('#customer-groups-table').on('change', '#select-customer-groups-page', function () {
        const checked = $(this).is(':checked');
        $('.row-export-checkbox').each(function () {
            const id = String($(this).val());
            $(this).prop('checked', checked);
            if (checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
        });
        updateSelectedCount();
        syncSelectAll();
    });

    $('#export-all-customer-groups').on('click', function () {
        $('#customer-groups-export-scope').val('all');
        $('#customer-groups-export-form').trigger('submit');
    });

    $('#export-selected-customer-groups').on('click', function () {
        if (selectedIds.size === 0) {
            alert('Pilih minimal 1 customer group untuk export.');
            return;
        }

        $('#customer-groups-export-scope').val('selected');
        updateSelectedCount();
        $('#customer-groups-export-form').trigger('submit');
    });

    updateSelectedCount();
});
</script>
@endpush

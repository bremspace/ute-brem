@extends('layouts.sneat')

@section('title', 'Customer')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if(session('success'))<div class="alert alert-success alert-dismissible" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Customer</h5>
            <div class="d-flex gap-2">
                <form id="customers-export-form" action="{{ route('customers.export') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="scope" id="customers-export-scope" value="all">
                    <input type="hidden" name="selected_ids" id="customers-selected-ids" value="">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-download me-1"></i>Export
                            <span id="customers-selected-count" class="badge bg-white text-primary ms-1">0</span>
                        </button>
                        <div class="dropdown-menu">
                            <button type="button" id="export-all-customers" class="dropdown-item">
                                <i class="bx bx-list-ul me-1"></i>Export Semua
                            </button>
                            <button type="button" id="export-selected-customers" class="dropdown-item">
                                <i class="bx bx-check-square me-1"></i>Export Pilihan
                            </button>
                        </div>
                    </div>
                </form>
                @if($customersTableReady && auth()->user()->hasPermission('master.customer_groups.create'))
                    <a href="{{ route('customers.import') }}" class="btn btn-outline-primary"><i class="bx bx-upload me-1"></i>Import</a>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i>Tambah Customer</a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table id="customers-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-customers-page" class="form-check-input"></th>
                            <th>ID</th>
                            <th>Kode Member</th>
                            <th>Nama</th>
                            <th>No HP</th>
                            <th>Email</th>
                            <th>Tipe</th>
                            <th>Total Poin</th>
                            <th>Group</th>
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

    const table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        searching: {{ $customersTableReady ? 'true' : 'false' }},
        ajax: '{{ route('customers.data') }}',
        language: {
            emptyTable: 'Belum ada data customer.'
        },
        columns: [
            { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'member_code_link', name: 'member_code', orderable: false, searchable: false },
            { data: 'name_link', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'type_badge', name: 'type', orderable: false, searchable: false },
            { data: 'points_label', name: 'points_balance', searchable: false },
            { data: 'group_name', name: 'group.name', orderable: false },
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
        $('#customers-selected-count').text(selectedIds.size);
        $('#customers-selected-ids').val(Array.from(selectedIds).join(','));
    }

    function syncSelectAll() {
        const checkboxes = $('.row-export-checkbox');
        const checked = checkboxes.filter(':checked').length;
        $('#select-customers-page')
            .prop('checked', checkboxes.length > 0 && checked === checkboxes.length)
            .prop('indeterminate', checked > 0 && checked < checkboxes.length);
    }

    $('#customers-table').on('change', '.row-export-checkbox', function () {
        const id = String($(this).val());
        if ($(this).is(':checked')) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        updateSelectedCount();
        syncSelectAll();
    });

    $('#customers-table').on('change', '#select-customers-page', function () {
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

    $('#export-all-customers').on('click', function () {
        $('#customers-export-scope').val('all');
        $('#customers-export-form').trigger('submit');
    });

    $('#export-selected-customers').on('click', function () {
        if (selectedIds.size === 0) {
            alert('Pilih minimal 1 customer untuk export.');
            return;
        }

        $('#customers-export-scope').val('selected');
        updateSelectedCount();
        $('#customers-export-form').trigger('submit');
    });

    updateSelectedCount();
});
</script>
@endpush

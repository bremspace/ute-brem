<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-surface-800 mb-0">Manajemen Supplier</h1>
            <p class="text-muted mb-0">Kelola data supplier</p>
        </div>
        <button type="button" class="btn btn-primary" wire:click.prevent="clearForm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Supplier
        </button>
    </div>

    <!-- Supplier Form -->
    @if($action === 'edit')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-semibold">Edit Supplier #{{ $supplierId }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Nama Supplier *</label>
                        <input type="text" wire:model="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Kode Supplier</label>
                        <input type="text" wire:model="code" class="form-control font-monospace" placeholder="SedikitOpsional">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Email</label>
                        <input type="email" wire:model="email" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Telepon</label>
                        <input type="text" wire:model="phone" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">PIC (Contact Person)</label>
                        <input type="text" wire:model="contact_person" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-surface-700">Alamat</label>
                        <textarea wire:model="address"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-medium text-surface-700">Catatan</label>
                        <textarea wire:model="notes"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>
                    <div class="col-12 mt-3">
                        <div class="d-sm-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" wire:click.prevent="clearForm">
                                <i class="bi bi-x-lg me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-medium text-surface-600">Cari</label>
                    <input type="text"
                           wire:model="search"
                           class="form-control"
                           placeholder="Nama, kode, atau PIC..."
                           @keyup.enter="resetPage()">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-medium text-surface-600">Per Halaman</label>
                    <select wire:model="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary" wire:click.prevent="resetFilters">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Telp</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td class="font-monospace small">
                                @if($supplier->code)
                                <span class="fw-medium">{{ $supplier->code }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person ?? '-' }}</td>
                            <td>{{ $supplier->phone ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">
                                    {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button type="button" class="dropdown-item" wire:click="edit({{ $supplier }})">
                                            <i class="bx bx-edit-alt me-1"></i>Edit
                                        </button>
                                        <form wire:submit.prevent="delete({{ $supplier }})"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <p class="text-muted mb-0">Belum ada data supplier</p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3 d-flex justify-content-end">
        {{ $suppliers->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', function() {
        Livewire.on('flash.success', function(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 2000,
                showConfirmButton: false
            });
        });

        Livewire.on('flash.error', function(message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: message,
                confirmButtonText: 'OK'
            });
        });
    });
</script>
@endpush
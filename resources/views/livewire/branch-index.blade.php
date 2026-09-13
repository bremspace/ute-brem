<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-surface-800 mb-0">Manajemen Cabang</h1>
            <p class="text-muted mb-0">Kelola data cabang/lokasi</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" wire:click.prevent="resetForm">
                <i class="bi bi-plus-lg me-1"></i> Tambah Cabang
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-medium text-surface-600">Cari</label>
                    <input type="text"
                           wire:model="search"
                           class="form-control"
                           placeholder="Kode / Nama cabang..."
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
                <div class="col-md-5 d-flex align-items-end">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" wire:click.prevent="resetFilters">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branch Form -->
    @if($action === 'edit')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-semibold">Edit Cabang #{{ $branchId }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Nama Cabang *</label>
                        <input type="text" wire:model="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Kode Cabang *</label>
                        <input type="text" wire:model="code" class="form-control font-monospace" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Nomor Telepon</label>
                        <input type="text" wire:model="phone" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Alamat</label>
                        <textarea wire:model="address"
                                  class="form-control"
                                  rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Tipe</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_main" class="form-check-input" id="is_main">
                            <label class="form-check-label" for="is_main">Pusat / Cabang Utama</label>
                        </div>
                        @if($branchId)
                        <small class="text-muted">Hanya 1 cabang yang bisa menjadi pusat</small>
                        @endif
                    </div>
                    <div class="col-12 mt-3">
                        <div class="d-sm-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" wire:click.prevent="resetForm">
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

    <!-- Branch Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($branches as $branch)
                        <tr>
                            <td class="font-monospace fw-medium">{{ $branch->code }}</td>
                            <td class="fw-medium">{{ $branch->name }}</td>
                            <td>
                                @php
                                    $badgeClass = $branch->is_main ? 'bg-primary' : 'bg-secondary';
                                    $badgeText = $branch->is_main ? 'Pusat' : 'Cabang';
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $branch->is_active ? 'success' : 'secondary' }}">
                                    {{ $branch->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button type="button" class="dropdown-item" wire:click="edit({{ $branch }})">
                                            <i class="bx bx-edit-alt me-1"></i>Edit
                                        </button>
                                        @if(!$branch->is_main)
                                        <form wire:submit.prevent="delete({{ $branch }})"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <p class="text-muted mb-0">Belum ada data cabang</p>
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
        {{ $branches->links() }}
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
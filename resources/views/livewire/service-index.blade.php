<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-surface-800 mb-0">Manajemen Jasa/Service</h1>
            <p class="text-muted mb-0">Kelola data jasa layanan</p>
        </div>
        <button type="button" class="btn btn-primary" wire:click.prevent="resetForm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Jasa
        </button>
    </div>

    <!-- Services Form -->
    @if($action === 'edit')
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-semibold">Edit Jasa #{{ $serviceId }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Nama Jasa *</label>
                        <input type="text" wire:model="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Kode Jasa</label>
                        <input type="text" wire:model="service_code" class="form-control font-monospace" placeholder="Otomatis jika dikosongkan">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-medium text-surface-700">Per Halaman</label>
                        <select wire:model="perPage" class="form-select">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Kategori</label>
                        <input type="text" wire:model="category" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Group</label>
                        <input type="text" wire:model="group" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-surface-700">Harga Toko (Rp) *</label>
                        <input type="number" wire:model="price_toko" class="form-control" required min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-surface-700">Harga Partai (Rp)</label>
                        <input type="number" wire:model="price_partai" class="form-control" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-surface-700">Harga Cabang (Rp)</label>
                        <input type="number" wire:model="price_cabang" class="form-control" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Status Harga</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_open_price" class="form-check-input" id="is_open_price">
                            <label class="form-check-label" for="is_open_price">Harga Buka (Open Price)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Diskon Override</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="allow_discount_override" class="form-check-input" id="allow_discount_override">
                            <label class="form-check-label" for="allow_discount_override">Izinkan Diskon</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="is_active">
                            <label class="form-check-label" for="is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Pembayaran Pajak</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_taxable" class="form-check-input" id="is_taxable">
                            <label class="form-check-label" for="is_taxable">Pajak Boleh</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium text-surface-700">Status Publikasi</label>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" wire:model="is_published" class="form-check-input" id="is_published">
                            <label class="form-check-label" for="is_published">Terpublikasi</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium text-surface-700">Aksi</label>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-secondary flex-fill" wire:click.prevent="resetForm">
                                <i class="bi bi-x-lg me-1"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary flex-fill">
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
                <div class="col-md-8">
                    <label class="form-label small fw-medium text-surface-600">Cari</label>
                    <input type="text"
                           wire:model="search"
                           class="form-control"
                           placeholder="Nama, kode, kategori, atau group..."
                           @keyup.enter="resetPage()">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary" wire:click.prevent="resetFilters">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori / Group</th>
                            <th>Harga Toko</th>
                            <th>Open Price</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($services as $service)
                        <tr>
                            <td class="font-monospace small fw-medium">{{ $service->service_code }}</td>
                            <td class="fw-medium">{{ $service->name }}</td>
                            <td>
                                @if($service->category || $service->group)
                                <div>
                                    @if($service->category)
                                    <small>{{ $service->category }}</small>
                                    @endif
                                    @if($service->group)
                                    <br><small class="text-muted">{{ $service->group }}</small>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="fw-medium">Rp {{ number_format((float) $service->price_toko, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $service->is_open_price ? 'success' : 'secondary' }}">
                                    {{ $service->is_open_price ? 'Ya' : 'Tidak' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $service->is_active ? 'success' : 'secondary' }}">
                                    {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button type="button" class="dropdown-item" wire:click="edit({{ $service->id }})">
                                            <i class="bx bx-edit-alt me-1"></i>Edit
                                        </button>
                                        @if(!$service->transactionItems()->exists())
                                        <form wire:submit.prevent="delete({{ $service->id }})"
                                              style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i>Hapus
                                            </button>
                                        </form>
                                        @else
                                        <span class="dropdown-item disabled text-muted">
                                            <i class="bx bx-trash me-1 opacity-50"></i>Tidak Dapat Dihapus
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <p class="text-muted mb-0">Belum ada data jasa</p>
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
        {{ $services->links() }}
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
<div wire:poll.60s x-data="{ showDeleteModal: @entangle('showDeleteModal') }">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3 py-3">
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <i class="bx bx-search position-absolute" style="top:50%;left:12px;transform:translateY(-50%);color:#9ca3af;"></i>
                    <input type="search" class="form-control ps-4" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kode, atau HP supplier..." style="min-width:300px;">
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select class="form-select form-select-sm" wire:model.change="perPage" style="width:80px;">
                    <option value="15">15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th style="width:40px;" class="text-center">#</th>
                        <th style="min-width:100px;">Kode</th>
                        <th style="min-width:200px;">Nama Supplier</th>
                        <th style="min-width:150px;">Kontak</th>
                        <th style="min-width:200px;">Alamat</th>
                        <th style="min-width:90px;">Status</th>
                        <th style="min-width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" wire:loading.remove target="table-body" class="table-border-bottom-0">
                    @forelse ($suppliers as $index => $supplier)
                        <tr wire:key="row-{{ $supplier->id }}">
                            <td class="text-center text-muted">{{ $suppliers->firstItem() + $index }}</td>
                            <td><span class="font-monospace small">{{ $supplier->code ?: '-' }}</span></td>
                            <td class="fw-semibold text-body">{{ $supplier->name }}</td>
                            <td>
                                <div>{{ $supplier->phone ?: '-' }}</div>
                                @if ($supplier->contact_person)<div class="small text-muted">{{ $supplier->contact_person }}</div>@endif
                            </td>
                            <td class="small text-truncate" style="max-width:220px;">{{ $supplier->address ?: '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $supplier->is_active ? 'success' : 'secondary' }}">{{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        @if (auth()->user()->hasPermission('master.products.edit'))
                                            <a class="dropdown-item" href="{{ route('suppliers.edit', $supplier->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('master.products.delete'))
                                            <button class="dropdown-item text-danger" wire:click="confirmDelete({{ $supplier->id }})" wire:loading.attr="disabled"><i class="bx bx-trash me-1"></i> Hapus</button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Tidak ada data supplier.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tbody wire:loading target="table-body" class="table-border-bottom-0">
                    @foreach(range(1, 5) as $i)
                    <tr>
                        <td class="text-center"><div class="placeholder-glow"><span class="placeholder col-2"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-4"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-6"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-5"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-7"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-3"></span></div></td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">Total {{ number_format($suppliers->total()) }} supplier</span>
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" x-cloak class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);" @click.self="showDeleteModal = false; $wire.set('showDeleteModal', false);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bx bx-error-circle me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body"><p class="mb-0">Apakah kamu yakin ingin menghapus data supplier ini?</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Batal</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteSupplier()" wire:loading.attr="disabled">
                        <span wire:loading.remove>Ya, Hapus Sekarang</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

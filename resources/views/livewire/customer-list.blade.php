@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

<div wire:poll.60s x-data="{ showDeleteModal: @entangle('showDeleteModal') }">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3 py-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="position-relative">
                    <i class="bx bx-search position-absolute" style="top:50%;left:12px;transform:translateY(-50%);color:#9ca3af;"></i>
                    <input type="search" class="form-control ps-4" wire:model.live.debounce.300ms="search" placeholder="Cari nama, kode, atau nomor HP..." style="min-width:300px;">
                </div>
                <select class="form-select" wire:model.change="filterMember" style="width:150px;">
                    <option value="">Semua Tipe</option>
                    <option value="true">Member</option>
                    <option value="false">Biasa</option>
                </select>
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
                        <th style="min-width:120px;">Kode Member</th>
                        <th style="min-width:180px;">Nama</th>
                        <th style="min-width:150px;">Kontak</th>
                        <th style="min-width:150px;">Email</th>
                        <th style="min-width:90px;">Tipe</th>
                        <th style="min-width:80px;">Poin</th>
                        <th style="min-width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body" wire:loading.remove target="table-body" class="table-border-bottom-0">
                    @forelse ($customers as $index => $customer)
                        <tr wire:key="row-{{ $customer->id }}">
                            <td class="text-center text-muted">{{ $customers->firstItem() + $index }}</td>
                            <td><span class="font-monospace small">{{ $customer->member_code ?: '-' }}</span></td>
                            <td class="fw-semibold text-body">{{ $customer->name }}</td>
                            <td>
                                <div>{{ $customer->phone ?: '-' }}</div>
                            </td>
                            <td class="small text-muted">{{ $customer->email ?: '-' }}</td>
                            <td>
                                @if ($customer->type === 'member')
                                    <span class="badge bg-label-success">Member</span>
                                @else
                                    <span class="badge bg-label-secondary">Regular</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ number_format((int) $customer->points_balance) }}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                                    <div class="dropdown-menu">
                                        @if (auth()->user()->hasPermission('master.customer_groups.edit'))
                                            <a class="dropdown-item" href="{{ route('customers.edit', $customer->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                        @endif
                                        @if (auth()->user()->hasPermission('master.customer_groups.delete'))
                                            <button class="dropdown-item text-danger" wire:click="confirmDelete({{ $customer->id }})" wire:loading.attr="disabled"><i class="bx bx-trash me-1"></i> Hapus</button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Tidak ada data pelanggan.</td>
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
                        <td><div class="placeholder-glow"><span class="placeholder col-5"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-3"></span></div></td>
                        <td><div class="placeholder-glow"><span class="placeholder col-2"></span></div></td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top py-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small text-muted">Total {{ number_format($customers->total()) }} pelanggan</span>
                {{ $customers->links() }}
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
                <div class="modal-body"><p class="mb-0">Apakah kamu yakin ingin menghapus data pelanggan ini?</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Batal</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteCustomer()" wire:loading.attr="disabled">
                        <span wire:loading.remove>Ya, Hapus Sekarang</span>
                        <span wire:loading><span class="spinner-border spinner-border-sm me-1"></span>Menghapus...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

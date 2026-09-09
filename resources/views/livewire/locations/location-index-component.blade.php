<?php
/**
 * Livewire component view for locations index.
 */
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lokasi</h5>
        <div class="d-flex gap-2">
            @can('master.locations.create')
                <button type="button" class="btn btn-primary" @click="$dispatch('openCreate')">Tambah Lokasi</button>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <input type="text" wire:model.debounce="search" placeholder="Cari..." class="form-control mb-3" />
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th><th>Nama</th><th>Kode</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($locations as $loc)
                <tr>
                    <td>{{ $loc->id }}</td>
                    <td>{{ $loc->name }}</td>
                    <td>{{ $loc->code }}</td>
                    <td><span class="badge bg-{{ $loc->is_active ? 'success' : 'secondary' }}">{{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" @click="$dispatch('openEdit', {id: {{ $loc->id }} })">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="confirmDelete({{ $loc->id }})">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $locations->links() }}
    </div>
</div>
<!-- Delete modal -->
<div x-data="{open: $wire.entangle('showDeleteModal') }" x-show="open" class="modal" style="display:none;">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Konfirmasi Hapus</h5><button type="button" class="btn-close" @click="open = false"></button></div>
        <div class="modal-body">Anda yakin ingin menghapus lokasi ini?</div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" @click="open = false">Batal</button><button type="button" class="btn btn-danger" wire:click="deleteLocation">Hapus</button></div>
    </div></div>
</div>

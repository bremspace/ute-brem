<!-- Livewire component view for category index --
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Kategori Barang</h5>
        <div class="d-flex gap-2">
            @can('master.categories.create')
                <button type="button" class="btn btn-primary" @click="$dispatch('openCreate')">Tambah Kategori</button>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <input type="text" wire:model.debounce="search" placeholder="Cari..." class="form-control mb-3" />
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th><th>Nama</th><th>Slug</th><th>Produk</th><th>Status</th><th>Dibuat</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td>{{ $cat->id }}</td>
                    <td>{{ $cat->name }}</td>
                    <td>{{ $cat->slug }}</td>
                    <td>{{ $cat->products_count }}</td>
                    <td><span class="badge bg-{{ $cat->is_active ? 'success' : 'secondary' }}">{{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td>{{ $cat->created_at->format('d M Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary" @click="$dispatch('openEdit', {id: {{ $cat->id }} })">Edit</button>
                        <button type="button" class="btn btn-sm btn-danger" wire:click="confirmDelete({{ $cat->id }})">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $categories->links() }}
    </div>
</div>
<!-- Delete modal -->
<div x-data="{open: $wire.entangle('showDeleteModal') }" x-show="open" class="modal" style="display:none;">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Konfirmasi Hapus</h5><button type="button" class="btn-close" @click="open = false"></button></div>
        <div class="modal-body">Anda yakin ingin menghapus kategori ini?</div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" @click="open = false">Batal</button><button type="button" class="btn btn-danger" wire:click="deleteCategory">Hapus</button></div>
    </div></div>
</div>

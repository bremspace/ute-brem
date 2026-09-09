<?php
/**
 * Blade view for PrinterSettingsComponent.
 * Mirrors original settings.printer view but uses Livewire bindings.
 */
?>
<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (!$settingsTableReady ?? true)
        <div class="alert alert-warning">
            Setting belum bisa disimpan karena tabel <code>pos_settings</code> belum tersedia.
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="mb-1">Setting</h4>
            <div class="text-muted">Atur identitas company dan printer POS.</div>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <ul class="nav nav-pills settings-tabs gap-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'company' ? 'active' : '' }}" id="company-tab"
                        wire:click="$set('activeTab','company')" data-bs-toggle="tab" data-bs-target="#company-pane" type="button" role="tab">
                        <i class="bx bx-buildings me-1"></i> Setting Company
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'printer' ? 'active' : '' }}" id="printer-tab"
                        wire:click="$set('activeTab','printer')" data-bs-toggle="tab" data-bs-target="#printer-pane" type="button" role="tab">
                        <i class="bx bx-printer me-1"></i> Setting Printer
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'backup' ? 'active' : '' }}" id="backup-tab"
                        wire:click="$set('activeTab','backup')" data-bs-toggle="tab" data-bs-target="#backup-pane" type="button" role="tab">
                        <i class="bx bx-data me-1"></i> Backup & Restore
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content p-0">
                {{-- Company Tab --}}
                <div class="tab-pane fade {{ $activeTab === 'company' ? 'show active' : '' }}" id="company-pane" role="tabpanel" aria-labelledby="company-tab">
                    <form wire:submit.prevent="saveCompany" class="row g-3">
                        @csrf
                        <input type="hidden" name="setting_section" value="company">
                        <div class="col-lg-8">
                            <label class="form-label">Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" wire:model.lazy="company_name" class="form-control @error('company_name') is-invalid @enderror">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Kode Lokasi</label>
                            <input type="text" wire:model.lazy="company_location_code" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea wire:model.lazy="company_address" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kota</label>
                            <input type="text" wire:model.lazy="company_city" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provinsi</label>
                            <input type="text" wire:model.lazy="company_province" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Negara</label>
                            <input type="text" wire:model.lazy="company_country" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor NPWP</label>
                            <input type="text" wire:model.lazy="company_tax_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor NPPKP</label>
                            <input type="text" wire:model.lazy="company_taxable_number" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Slogan Toko</label>
                            <input type="text" wire:model.lazy="company_slogan" class="form-control">
                        </div>
                        {{-- References --}}
                        <div class="col-12">
                            <div class="border rounded-3 p-3">
                                <div class="settings-section-title mb-3">Referensi</div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Pembelian</label>
                                        <input type="text" wire:model.lazy="ref_purchase" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pengeluaran/Biaya</label>
                                        <input type="text" wire:model.lazy="ref_expense" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Retur Pembelian</label>
                                        <input type="text" wire:model.lazy="ref_purchase_return" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bayar Hutang</label>
                                        <input type="text" wire:model.lazy="ref_pay_debt" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Terima Barang Retur</label>
                                        <input type="text" wire:model.lazy="ref_receive_return_item" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Bayar Piutang</label>
                                        <input type="text" wire:model.lazy="ref_receive_debt" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Penjualan Toko</label>
                                        <input type="text" wire:model.lazy="ref_store_sale" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Mutasi Kas/Bank</label>
                                        <input type="text" wire:model.lazy="ref_cash_bank_mutation" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Retur Penjualan</label>
                                        <input type="text" wire:model.lazy="ref_sale_return" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Penyesuaian Stok</label>
                                        <input type="text" wire:model.lazy="ref_stock_adjustment" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Penukaran Poin</label>
                                        <input type="text" wire:model.lazy="ref_point_redemption" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" {{ $settingsTableReady ? '' : 'disabled' }}>
                                <i class="bx bx-save me-1"></i> Simpan Company
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Printer Tab --}}
                <div class="tab-pane fade {{ $activeTab === 'printer' ? 'show active' : '' }}" id="printer-pane" role="tabpanel" aria-labelledby="printer-tab">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <form wire:submit.prevent="savePrinter" class="row g-3">
                                @csrf
                                <input type="hidden" name="setting_section" value="printer">
                                <div class="col-12">
                                    <label class="form-label">Mode Cetak</label>
                                    <select wire:model.lazy="printer_mode" id="printer_mode" class="form-select">
                                        <option value="browser">Browser Print (Ctrl+P)</option>
                                        <option value="bridge">Printer Bridge (ESC/POS / Network)</option>
                                    </select>
                                    <div class="form-text">
                                        Browser Print pakai dialog print bawaan browser. Printer Bridge dipakai jika ada aplikasi bridge di PC kasir.
                                    </div>
                                </div>
                                <div class="col-md-7" id="bridge_url_wrap" wire:ignore.self>
                                    <label class="form-label">Bridge URL</label>
                                    <input type="text" wire:model.lazy="printer_bridge_url" class="form-control @error('printer_bridge_url') is-invalid @enderror" placeholder="Contoh: http://127.0.0.1:9000">
                                    @error('printer_bridge_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-5" id="printer_name_wrap">
                                    <label class="form-label">Nama Printer (Opsional)</label>
                                    <input type="text" wire:model.lazy="printer_name" class="form-control" placeholder="Contoh: EPSON TM-T82">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lebar Kertas</label>
                                    <select wire:model.lazy="printer_paper_width_mm" class="form-select">
                                        <option value="58">58mm</option>
                                        <option value="80">80mm</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jumlah Copy</label>
                                    <input type="number" min="1" max="5" wire:model.lazy="printer_copies" class="form-control @error('printer_copies') is-invalid @enderror">
                                    @error('printer_copies')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model.lazy="auto_print" id="auto_print">
                                        <label class="form-check-label" for="auto_print">Auto Print</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Header Struk Tambahan</label>
                                    <textarea wire:model.lazy="printer_header_text" rows="3" class="form-control"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Footer Struk</label>
                                    <textarea wire:model.lazy="printer_footer_text" rows="3" class="form-control"></textarea>
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model.lazy="show_store_name" id="show_store_name">
                                        <label class="form-label" for="show_store_name">Tampilkan Identitas Toko</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model.lazy="show_datetime" id="show_datetime">
                                        <label class="form-label" for="show_datetime">Tampilkan Tanggal/Jam</label>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <a href="{{ route('settings.printer.test-print') }}" target="_blank" class="btn btn-primary btn-sm" id="testPrintBtn">
                                        <i class="bx bx-printer me-1"></i> Test Print
                                    </a>
                                    <button type="submit" class="btn btn-primary" {{ $settingsTableReady ? '' : 'disabled' }}>
                                        <i class="bx bx-save me-1"></i> Simpan Printer
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-lg-5">
                            <h6 class="mb-3">Preview Struk</h6>
                            <div id="receipt_preview" class="receipt-preview {{ ((int) $printer_paper_width_mm) === 58 ? 'narrow' : 'wide' }}">
                                <div id="preview_company_block">
                                    <div class="center fw-bold" id="preview_company_name" style="letter-spacing: .08em;">
                                        {{ $company_name }}
                                    </div>
                                    <div class="center muted" id="preview_company_address" style="white-space: pre-line;">
                                        {{ $company_address }}
                                    </div>
                                    <div class="center muted" id="preview_company_region">
                                        {{ trim(implode(', ', array_filter([$company_city, $company_province, $company_country]))) }}
                                    </div>
                                    <div class="center muted" id="preview_company_slogan">
                                        {{ $company_slogan }}
                                    </div>
                                </div>
                                <div class="center muted" id="preview_header_text"></div>
                                <div class="hr"></div>
                                <div class="row">
                                    <div>TRX</div>
                                    <div>{{ $ref_store_sale }}-2604240000-001</div>
                                </div>
                                <div class="row muted" id="preview_datetime_row">
                                    <div>Tanggal</div>
                                    <div>{{ now()->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="muted">Kasir: Owner</div>
                                <div class="muted">Pelanggan: Customer Member</div>
                                <div class="hr"></div>
                                <div class="row">
                                    <div>Barang A</div>
                                    <div>1 x 10.000</div>
                                </div>
                                <div class="row">
                                    <div>Barang B</div>
                                    <div>2 x 5.000</div>
                                </div>
                                <div class="hr"></div>
                                <div class="row fw-bold">
                                    <div>Grand Total</div>
                                    <div>20.000</div>
                                </div>
                                <div class="hr"></div>
                                <div class="center muted" id="preview_footer_text"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Backup Tab --}}
                <div class="tab-pane fade {{ $activeTab === 'backup' ? 'show active' : '' }}" id="backup-pane" role="tabpanel" aria-labelledby="backup-tab">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-1">Pencadangan Database (Backup & Restore)</h5>
                            <div class="text-muted small">Ekspor seluruh data sistem menjadi file SQL cadangan atau pulihkan database dari file cadangan yang ada.</div>
                        </div>
                        <form wire:submit.prevent="createBackup">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin membuat backup database baru?')">
                                <i class="bx bx-plus-circle me-1"></i> Buat Backup Baru
                            </button>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama File Backup</th>
                                    <th>Ukuran File</th>
                                    <th>Tanggal Dibuat</th>
                                    <th class="text-center" style="width: 280px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($backups as $index => $backup)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="fw-semibold text-primary"><i class="bx bx-file me-1"></i>{{ $backup['filename'] }}</span></td>
                                        <td>{{ number_format($backup['size'] / 1024, 2, ',', '.') }} KB</td>
                                        <td>{{ \Carbon\Carbon::parse($backup['created_at'])->format('d M Y, H:i') }} WIB</td>
                                        <td class="text-center">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <a href="{{ route('settings.backup.download', $backup['filename']) }}" class="btn btn-sm btn-outline-info">
                                                    <i class="bx bx-download me-1"></i> Download
                                                </a>
                                                <form wire:submit.prevent="restoreBackup('{{ $backup['filename'] }}' )" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-warning" onclick="return confirm('PERINGATAN! Melakukan restore database akan menimpa data yang ada saat ini dengan data dalam file backup. Apakah Anda yakin?')">
                                                        <i class="bx bx-reset me-1"></i> Restore
                                                    </button>
                                                </form>
                                                <form wire:submit.prevent="deleteBackup('{{ $backup['filename'] }}' )" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus file backup ini?')">
                                                        <i class="bx bx-trash me-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="bx bx-info-circle fs-3 d-block mb-2"></i> Belum ada file backup. Silakan klik tombol "Buat Backup Baru".
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const modeSelect = document.getElementById('printer_mode');
    const bridgeUrlWrap = document.getElementById('bridge_url_wrap');
    const printerNameWrap = document.getElementById('printer_name_wrap');
    const paperWidth = document.getElementById('printer_paper_width_mm');
    const headerText = document.getElementById('printer_header_text');
    const footerText = document.getElementById('printer_footer_text');
    const showStore = document.getElementById('show_store_name');
    const showDatetime = document.getElementById('show_datetime');
    const preview = document.getElementById('receipt_preview');
    const previewCompany = document.getElementById('preview_company_block');
    const previewHeader = document.getElementById('preview_header_text');
    const previewFooter = document.getElementById('preview_footer_text');
    const previewDatetime = document.getElementById('preview_datetime_row');

    function updateModeVisibility() {
        const bridge = modeSelect.value === 'bridge';
        bridgeUrlWrap.classList.toggle('d-none', !bridge);
        printerNameWrap.classList.toggle('d-none', !bridge);
    }
    function updatePreview() {
        const width = paperWidth.value;
        preview.classList.toggle('narrow', width === '58');
        preview.classList.toggle('wide', width !== '58');
        const hdr = (headerText.value || '').trim();
        const ftr = (footerText.value || '').trim();
        previewHeader.textContent = hdr;
        previewHeader.classList.toggle('d-none', hdr.length === 0);
        previewFooter.textContent = ftr;
        previewFooter.classList.toggle('d-none', ftr.length === 0);
        previewCompany.classList.toggle('d-none', !showStore.checked);
        previewDatetime.classList.toggle('d-none', !showDatetime.checked);
    }
    ['change','input'].forEach(evt => {
        modeSelect.addEventListener(evt, updateModeVisibility);
        paperWidth.addEventListener(evt, updatePreview);
        headerText.addEventListener(evt, updatePreview);
        footerText.addEventListener(evt, updatePreview);
        showStore.addEventListener(evt, updatePreview);
        showDatetime.addEventListener(evt, updatePreview);
    });
    updateModeVisibility();
    updatePreview();
})();
</script>
@endpush

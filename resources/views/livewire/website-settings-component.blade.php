<?php
/**
 * Blade view for WebsiteSettingsComponent.
 * Admin form to manage consumer website display settings.
 */
?>
<div class="container-xxl flex-grow-1 container-p-y">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h4 class="mb-1">Pengaturan Website</h4>
            <div class="text-muted">Atur tampilan website konsumen (hero, footer, info toko).</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('website.products.index') }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bx bx-globe me-1"></i> Lihat Website
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <ul class="nav nav-pills settings-tabs gap-2" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}"
                        wire:click="$set('activeTab','general')" type="button" role="tab">
                        <i class="bx bx-store me-1"></i> Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'hero' ? 'active' : '' }}"
                        wire:click="$set('activeTab','hero')" type="button" role="tab">
                        <i class="bx bx-image me-1"></i> Hero Banner
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'footer' ? 'active' : '' }}"
                        wire:click="$set('activeTab','footer')" type="button" role="tab">
                        <i class="bx bx-footer me-1"></i> Footer & Kontak
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                {{-- General Tab --}}
                @if ($activeTab === 'general')
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label class="form-label">Nama Toko <span class="text-danger">*</span></label>
                            <input type="text" wire:model.lazy="general_store_name" class="form-control @error('general_store_name') is-invalid @enderror">
                            @error('general_store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Tagline</label>
                            <input type="text" wire:model.lazy="general_tagline" class="form-control @error('general_tagline') is-invalid @enderror" placeholder="Contoh: Toko sparepart HP terlengkap">
                            @error('general_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Hero Tab --}}
                @if ($activeTab === 'hero')
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <label class="form-label">Judul Hero</label>
                            <input type="text" wire:model.lazy="hero_title" class="form-control @error('hero_title') is-invalid @enderror" placeholder="Judul utama di banner hero">
                            @error('hero_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Teks Tombol CTA</label>
                            <input type="text" wire:model.lazy="hero_cta_text" class="form-control @error('hero_cta_text') is-invalid @enderror" placeholder="Lihat Produk">
                            @error('hero_cta_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Subjudul Hero</label>
                            <textarea wire:model.lazy="hero_subtitle" rows="2" class="form-control @error('hero_subtitle') is-invalid @enderror" placeholder="Deskripsi singkat di bawah judul hero"></textarea>
                            @error('hero_subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Link Tombol CTA</label>
                            <input type="text" wire:model.lazy="hero_cta_link" class="form-control @error('hero_cta_link') is-invalid @enderror" placeholder="#catalogProducts atau URL">
                            @error('hero_cta_link')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Gunakan #catalogProducts untuk anchor di halaman yang sama, atau URL lengkap untuk halaman lain.</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Footer & Contact Tab --}}
                @if ($activeTab === 'footer')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Deskripsi Brand (Footer)</label>
                            <textarea wire:model.lazy="footer_description" rows="3" class="form-control @error('footer_description') is-invalid @enderror"></textarea>
                            @error('footer_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-8">
                            <label class="form-label">Alamat</label>
                            <input type="text" wire:model.lazy="footer_address" class="form-control @error('footer_address') is-invalid @enderror">
                            @error('footer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Telepon / WhatsApp</label>
                            <input type="text" wire:model.lazy="footer_phone" class="form-control @error('footer_phone') is-invalid @enderror">
                            @error('footer_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Jam Operasional</label>
                            <input type="text" wire:model.lazy="footer_hours" class="form-control @error('footer_hours') is-invalid @enderror" placeholder="Senin — Sabtu, 08:00 — 17:00">
                            @error('footer_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="border rounded-3 p-3 mt-2">
                                <div class="settings-section-title mb-3">Media Sosial (URL)</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Instagram</label>
                                        <input type="url" wire:model.lazy="footer_instagram" class="form-control @error('footer_instagram') is-invalid @enderror" placeholder="https://instagram.com/...">
                                        @error('footer_instagram')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">WhatsApp</label>
                                        <input type="url" wire:model.lazy="footer_whatsapp" class="form-control @error('footer_whatsapp') is-invalid @enderror" placeholder="https://wa.me/6281234567890">
                                        @error('footer_whatsapp')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Facebook</label>
                                        <input type="url" wire:model.lazy="footer_facebook" class="form-control @error('footer_facebook') is-invalid @enderror" placeholder="https://facebook.com/...">
                                        @error('footer_facebook')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>

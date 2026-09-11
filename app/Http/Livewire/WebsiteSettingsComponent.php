<?php

namespace App\Http\Livewire;

use App\Models\WebsiteSetting;
use Livewire\Component;

class WebsiteSettingsComponent extends Component
{
    // General
    public $general_store_name = '';
    public $general_tagline = '';

    // Hero
    public $hero_title = '';
    public $hero_subtitle = '';
    public $hero_cta_text = '';
    public $hero_cta_link = '';

    // Footer
    public $footer_description = '';
    public $footer_address = '';
    public $footer_phone = '';
    public $footer_hours = '';
    public $footer_instagram = '';
    public $footer_whatsapp = '';
    public $footer_facebook = '';

    public $activeTab = 'general';

    public function mount(): void
    {
        $this->general_store_name = WebsiteSetting::get('general.store_name', 'UTE Parts');
        $this->general_tagline = WebsiteSetting::get('general.tagline', '');
        $this->hero_title = WebsiteSetting::get('hero.title', '');
        $this->hero_subtitle = WebsiteSetting::get('hero.subtitle', '');
        $this->hero_cta_text = WebsiteSetting::get('hero.cta_text', '');
        $this->hero_cta_link = WebsiteSetting::get('hero.cta_link', '');
        $this->footer_description = WebsiteSetting::get('footer.description', '');
        $this->footer_address = WebsiteSetting::get('footer.address', '');
        $this->footer_phone = WebsiteSetting::get('footer.phone', '');
        $this->footer_hours = WebsiteSetting::get('footer.hours', '');
        $this->footer_instagram = WebsiteSetting::get('footer.instagram', '');
        $this->footer_whatsapp = WebsiteSetting::get('footer.whatsapp', '');
        $this->footer_facebook = WebsiteSetting::get('footer.facebook', '');
    }

    public function save(): void
    {
        $this->validate([
            'general_store_name' => 'required|string|max:255',
            'general_tagline' => 'nullable|string|max:255',
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_cta_text' => 'nullable|string|max:100',
            'hero_cta_link' => 'nullable|string|max:255',
            'footer_description' => 'nullable|string|max:1000',
            'footer_address' => 'nullable|string|max:255',
            'footer_phone' => 'nullable|string|max:50',
            'footer_hours' => 'nullable|string|max:100',
            'footer_instagram' => 'nullable|string|max:255',
            'footer_whatsapp' => 'nullable|string|max:255',
            'footer_facebook' => 'nullable|string|max:255',
        ]);

        WebsiteSetting::set('general.store_name', $this->general_store_name, 'general');
        WebsiteSetting::set('general.tagline', $this->general_tagline, 'general');
        WebsiteSetting::set('hero.title', $this->hero_title, 'hero');
        WebsiteSetting::set('hero.subtitle', $this->hero_subtitle, 'hero', 'textarea');
        WebsiteSetting::set('hero.cta_text', $this->hero_cta_text, 'hero');
        WebsiteSetting::set('hero.cta_link', $this->hero_cta_link, 'hero');
        WebsiteSetting::set('footer.description', $this->footer_description, 'footer', 'textarea');
        WebsiteSetting::set('footer.address', $this->footer_address, 'footer');
        WebsiteSetting::set('footer.phone', $this->footer_phone, 'footer');
        WebsiteSetting::set('footer.hours', $this->footer_hours, 'footer');
        WebsiteSetting::set('footer.instagram', $this->footer_instagram, 'footer', 'url');
        WebsiteSetting::set('footer.whatsapp', $this->footer_whatsapp, 'footer', 'url');
        WebsiteSetting::set('footer.facebook', $this->footer_facebook, 'footer', 'url');

        session()->flash('success', 'Pengaturan website berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.website-settings-component');
    }
}

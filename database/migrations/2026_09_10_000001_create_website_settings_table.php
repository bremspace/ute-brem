<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group')->default('general');
            $table->string('type')->default('text');
            $table->timestamps();
        });

        // Seed defaults
        DB::table('website_settings')->insert([
            ['key' => 'general.store_name',  'value' => 'UTE Parts',    'group' => 'general', 'type' => 'text',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'general.tagline',     'value' => 'Toko sparepart HP terlengkap', 'group' => 'general', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero.title',          'value' => 'Semua Kebutuhan HP Anda, Satu Tempat', 'group' => 'hero', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero.subtitle',       'value' => 'LCD, baterai, charger, casing, dan aksesoris HP berkualitas dengan harga terbaik.', 'group' => 'hero', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero.cta_text',       'value' => 'Lihat Produk', 'group' => 'hero',  'type' => 'text',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'hero.cta_link',       'value' => '#catalogProducts', 'group' => 'hero', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.description',  'value' => 'Toko sparepart HP terlengkap. Menyediakan LCD, baterai, charger, casing, dan aksesoris HP berkualitas dengan harga terbaik.', 'group' => 'footer', 'type' => 'textarea', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.address',      'value' => 'Jl. Raya Sparepart No. 123, Kota', 'group' => 'footer', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.phone',        'value' => '0812-3456-7890', 'group' => 'footer', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.hours',        'value' => 'Senin — Sabtu, 08:00 — 17:00', 'group' => 'footer', 'type' => 'text', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.instagram',    'value' => '',              'group' => 'footer', 'type' => 'url',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.whatsapp',     'value' => '',              'group' => 'footer', 'type' => 'url',     'created_at' => now(), 'updated_at' => now()],
            ['key' => 'footer.facebook',     'value' => '',              'group' => 'footer', 'type' => 'url',     'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};

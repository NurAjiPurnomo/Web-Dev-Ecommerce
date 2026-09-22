<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('Toko Online Kami');
            $table->string('sender_name')->default('Admin Toko');
            $table->string('sender_phone')->default('081234567890');
            $table->text('address_detail')->nullable(); // Nama Jalan / RT / RW
            $table->string('village')->nullable(); // Desa / Kelurahan
            $table->string('district')->nullable(); // Kecamatan
            $table->string('city')->nullable(); // Kota / Kabupaten
            $table->string('province')->nullable(); // Provinsi
            $table->string('postal_code')->nullable(); // Kode Pos
            $table->string('biteship_area_id')->nullable(); // Biteship Area ID
            $table->json('active_couriers')->nullable(); // ["jne", "jnt", "sicepat", "pos"]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};

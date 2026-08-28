<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('diskon_nominal'); // gratis_ongkir, diskon_nominal, diskon_persen
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('min_spend')->default(0);
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status')->default('aktif'); // aktif, nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

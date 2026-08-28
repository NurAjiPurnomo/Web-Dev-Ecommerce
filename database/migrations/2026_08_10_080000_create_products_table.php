<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('Umum');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('original_price')->nullable();
            $table->string('discount')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('sold')->default(0);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('aktif'); // aktif, nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

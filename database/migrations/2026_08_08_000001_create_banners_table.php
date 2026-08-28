<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('highlight_text')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('button_text')->default('Belanja Sekarang');
            $table->string('button_url')->default('/catalog');
            $table->string('category_tag')->nullable();
            $table->string('status')->default('aktif');
            $table->integer('order_column')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};

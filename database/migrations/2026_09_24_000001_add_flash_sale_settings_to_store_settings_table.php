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
        Schema::table('store_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('store_settings', 'flash_sale_end_time')) {
                $table->dateTime('flash_sale_end_time')->nullable();
            }
            if (!Schema::hasColumn('store_settings', 'flash_sale_is_active')) {
                $table->boolean('flash_sale_is_active')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn(['flash_sale_end_time', 'flash_sale_is_active']);
        });
    }
};

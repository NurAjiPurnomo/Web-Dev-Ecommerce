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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'biteship_area_id')) {
                $table->string('biteship_area_id')->nullable()->after('city_id');
            }
            if (!Schema::hasColumn('users', 'district')) {
                $table->string('district')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'village')) {
                $table->string('village')->nullable()->after('district');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['biteship_area_id', 'district', 'village']);
        });
    }
};

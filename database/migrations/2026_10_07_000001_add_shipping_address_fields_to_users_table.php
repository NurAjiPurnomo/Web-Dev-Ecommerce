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
            if (!Schema::hasColumn('users', 'recipient_name')) {
                $table->string('recipient_name')->nullable()->after('address');
            }
            if (!Schema::hasColumn('users', 'recipient_phone')) {
                $table->string('recipient_phone')->nullable()->after('recipient_name');
            }
            if (!Schema::hasColumn('users', 'address_label')) {
                $table->string('address_label')->default('Rumah')->nullable()->after('recipient_phone');
            }
            if (!Schema::hasColumn('users', 'is_primary_address')) {
                $table->boolean('is_primary_address')->default(true)->after('address_label');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['recipient_name', 'recipient_phone', 'address_label', 'is_primary_address']);
        });
    }
};

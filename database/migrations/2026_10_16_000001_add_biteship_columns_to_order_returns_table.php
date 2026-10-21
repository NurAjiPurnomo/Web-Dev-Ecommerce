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
        Schema::table('order_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('order_returns', 'biteship_order_id')) {
                $table->string('biteship_order_id')->nullable()->after('return_tracking_number');
            }
            if (!Schema::hasColumn('order_returns', 'biteship_tracking_id')) {
                $table->string('biteship_tracking_id')->nullable()->after('biteship_order_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            if (Schema::hasColumn('order_returns', 'biteship_order_id')) {
                $table->dropColumn('biteship_order_id');
            }
            if (Schema::hasColumn('order_returns', 'biteship_tracking_id')) {
                $table->dropColumn('biteship_tracking_id');
            }
        });
    }
};

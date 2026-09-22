<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->decimal('biteship_handling_fee', 12, 2)->default(0)->after('active_couriers');
            $table->decimal('biteship_shipping_discount', 12, 2)->default(0)->after('biteship_handling_fee');
            $table->decimal('min_order_for_discount', 12, 2)->default(0)->after('biteship_shipping_discount');
            $table->string('biteship_round_shipping')->default('none')->after('min_order_for_discount');
        });
    }

    public function down(): void
    {
        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn([
                'biteship_handling_fee',
                'biteship_shipping_discount',
                'min_order_for_discount',
                'biteship_round_shipping',
            ]);
        });
    }
};

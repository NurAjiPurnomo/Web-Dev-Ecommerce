<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('biteship_order_id')->nullable()->after('tracking_number');
            $table->string('waybill_number')->nullable()->after('biteship_order_id');
            $table->text('waybill_pdf_url')->nullable()->after('waybill_number');
            $table->string('courier_code')->nullable()->after('courier');
            $table->string('courier_service')->nullable()->after('courier_code');
            $table->string('destination_area_id')->nullable()->after('shipping_address');
            $table->string('destination_postal_code')->nullable()->after('destination_area_id');
            $table->string('destination_district')->nullable()->after('destination_postal_code');
            $table->string('destination_village')->nullable()->after('destination_district');
            $table->string('destination_rt_rw')->nullable()->after('destination_village');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'biteship_order_id',
                'waybill_number',
                'waybill_pdf_url',
                'courier_code',
                'courier_service',
                'destination_area_id',
                'destination_postal_code',
                'destination_district',
                'destination_village',
                'destination_rt_rw'
            ]);
        });
    }
};

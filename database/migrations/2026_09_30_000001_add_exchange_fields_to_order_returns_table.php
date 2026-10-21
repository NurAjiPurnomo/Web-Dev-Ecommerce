<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('order_returns', 'solution_type')) {
                $table->string('solution_type')->default('refund')->after('video_proof');
            }
            if (!Schema::hasColumn('order_returns', 'return_tracking_number')) {
                $table->string('return_tracking_number')->nullable()->after('solution_type');
            }
            if (!Schema::hasColumn('order_returns', 'return_shipping_receipt')) {
                $table->string('return_shipping_receipt')->nullable()->after('return_tracking_number');
            }
            if (!Schema::hasColumn('order_returns', 'replacement_tracking_number')) {
                $table->string('replacement_tracking_number')->nullable()->after('return_shipping_receipt');
            }
            if (!Schema::hasColumn('order_returns', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('replacement_tracking_number');
            }
            if (!Schema::hasColumn('order_returns', 'bank_account_number')) {
                $table->string('bank_account_number')->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('order_returns', 'bank_account_name')) {
                $table->string('bank_account_name')->nullable()->after('bank_account_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropColumn([
                'solution_type',
                'return_tracking_number',
                'return_shipping_receipt',
                'replacement_tracking_number'
            ]);
        });
    }
};

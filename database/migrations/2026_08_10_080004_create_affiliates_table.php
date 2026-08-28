<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('referral_code')->unique();
            $table->decimal('commission_rate', 5, 2)->default(5.00); // 5%
            $table->unsignedBigInteger('total_sales')->default(0);
            $table->unsignedBigInteger('commission_earned')->default(0);
            $table->string('status')->default('aktif'); // aktif, nonaktif
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};

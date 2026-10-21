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
        if (!Schema::hasColumn('product_reviews', 'likes_count')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->unsignedInteger('likes_count')->default(0)->after('comment');
            });
        }

        if (!Schema::hasTable('review_likes')) {
            Schema::create('review_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('review_id')->constrained('product_reviews')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('session_id', 100)->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['review_id', 'user_id']);
                $table->index(['review_id', 'session_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_likes');

        if (Schema::hasColumn('product_reviews', 'likes_count')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->dropColumn('likes_count');
            });
        }
    }
};

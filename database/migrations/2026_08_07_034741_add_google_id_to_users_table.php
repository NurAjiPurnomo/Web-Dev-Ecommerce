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
            $table->string('google_id')->nullable()->after('email');
            $table->string('password')->nullable()->change();
        });
    }https://127.0.0.1:51692/static/artifacts/4be1c4f8-2881-4cda-b60e-f18102094838/.user_uploaded/media_1790147535435.png?csrf=1aac340b-b012-4b44-b6e9-e615fb8a6a6a

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};

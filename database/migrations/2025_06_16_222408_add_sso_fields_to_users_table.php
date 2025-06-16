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
            $table->string('azure_id')->nullable()->unique()->after('email');
            $table->boolean('is_sso_enabled')->default(false)->after('azure_id');
            $table->string('avatar')->nullable()->after('is_sso_enabled');
            $table->timestamp('last_sso_login')->nullable()->after('avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['azure_id', 'is_sso_enabled', 'avatar', 'last_sso_login']);
        });
    }
};

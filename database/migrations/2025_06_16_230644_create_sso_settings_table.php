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
        Schema::create('sso_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['email', 'domain']); // Type of SSO setting
            $table->string('value'); // Email address or domain name
            $table->boolean('is_active')->default(true); // Enable/disable setting
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();

            // Ensure unique combinations of type and value
            $table->unique(['type', 'value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_settings');
    }
};

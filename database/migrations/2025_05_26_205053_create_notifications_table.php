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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'website_down', 'website_up', 'ssl_expiry', 'domain_expiry'
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (website info, etc.)
            $table->timestamp('read_at')->nullable();
            $table->string('icon')->default('bell'); // Icon name for display
            $table->string('color')->default('gray'); // Color theme (red, green, yellow, blue)
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->foreignId('website_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('website_url_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

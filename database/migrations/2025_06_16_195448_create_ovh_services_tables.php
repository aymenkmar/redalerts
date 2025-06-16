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
        // OVH VPS Services Table
        Schema::create('ovh_vps', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->unique();
            $table->string('display_name');
            $table->string('state')->default('unknown');
            $table->timestamp('expiration_date')->nullable();
            $table->timestamp('engagement_date')->nullable();
            $table->string('renewal_type')->default('manual');
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['expiration_date']);
            $table->index(['engagement_date']);
            $table->index(['last_synced_at']);
        });

        // OVH Dedicated Server Services Table
        Schema::create('ovh_dedicated_servers', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->unique();
            $table->string('display_name');
            $table->string('state')->default('unknown');
            $table->timestamp('expiration_date')->nullable();
            $table->timestamp('engagement_date')->nullable();
            $table->string('renewal_type')->default('manual');
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['expiration_date']);
            $table->index(['engagement_date']);
            $table->index(['last_synced_at']);
        });

        // OVH Domain Services Table
        Schema::create('ovh_domains', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->unique();
            $table->string('display_name');
            $table->string('state')->default('active');
            $table->timestamp('expiration_date')->nullable();
            $table->timestamp('engagement_date')->nullable();
            $table->string('renewal_type')->default('manual');
            $table->json('raw_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['expiration_date']);
            $table->index(['engagement_date']);
            $table->index(['last_synced_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ovh_domains');
        Schema::dropIfExists('ovh_dedicated_servers');
        Schema::dropIfExists('ovh_vps');
    }
};

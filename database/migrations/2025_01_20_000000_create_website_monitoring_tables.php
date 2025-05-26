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
        // Websites table
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('notification_emails')->nullable(); // Store multiple emails as JSON
            $table->boolean('is_active')->default(true);
            $table->string('overall_status')->default('unknown'); // up, down, warning, unknown
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });

        // Website URLs table
        Schema::create('website_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->boolean('monitor_status')->default(true);
            $table->boolean('monitor_domain')->default(false);
            $table->boolean('monitor_ssl')->default(false);
            $table->string('current_status')->default('unknown'); // up, down, warning, unknown
            $table->integer('response_time')->nullable(); // in milliseconds
            $table->integer('status_code')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_status_change')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'current_status']);
        });

        // Website monitoring logs table
        Schema::create('website_monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_url_id')->constrained()->onDelete('cascade');
            $table->string('check_type'); // status, domain, ssl
            $table->string('status'); // up, down, warning, error
            $table->integer('response_time')->nullable(); // in milliseconds
            $table->integer('status_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('additional_data')->nullable(); // SSL info, domain info, etc.
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['website_url_id', 'check_type', 'checked_at']);
            $table->index(['checked_at']);
        });

        // Website downtime incidents table
        Schema::create('website_downtime_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_url_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable(); // calculated when ended
            $table->string('cause')->nullable(); // status, domain, ssl
            $table->text('error_message')->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->boolean('recovery_notification_sent')->default(false);
            $table->timestamps();

            $table->index(['website_url_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_downtime_incidents');
        Schema::dropIfExists('website_monitoring_logs');
        Schema::dropIfExists('website_urls');
        Schema::dropIfExists('websites');
    }
};

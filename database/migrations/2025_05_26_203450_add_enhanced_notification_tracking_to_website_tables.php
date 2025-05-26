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
        // Add enhanced notification tracking to downtime incidents
        Schema::table('website_downtime_incidents', function (Blueprint $table) {
            $table->timestamp('last_notification_sent_at')->nullable()->after('recovery_notification_sent');
            $table->integer('notification_count')->default(0)->after('last_notification_sent_at');
        });

        // Add notification tracking for domain/SSL warnings
        Schema::table('website_urls', function (Blueprint $table) {
            $table->timestamp('domain_warning_notification_sent_at')->nullable()->after('updated_at');
            $table->timestamp('ssl_warning_notification_sent_at')->nullable()->after('domain_warning_notification_sent_at');
            $table->integer('domain_warning_notification_count')->default(0)->after('ssl_warning_notification_sent_at');
            $table->integer('ssl_warning_notification_count')->default(0)->after('domain_warning_notification_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('website_downtime_incidents', function (Blueprint $table) {
            $table->dropColumn(['last_notification_sent_at', 'notification_count']);
        });

        Schema::table('website_urls', function (Blueprint $table) {
            $table->dropColumn([
                'domain_warning_notification_sent_at',
                'ssl_warning_notification_sent_at',
                'domain_warning_notification_count',
                'ssl_warning_notification_count'
            ]);
        });
    }
};

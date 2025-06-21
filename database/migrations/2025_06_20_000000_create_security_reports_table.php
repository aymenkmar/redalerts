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
        Schema::create('security_reports', function (Blueprint $table) {
            $table->id();
            $table->string('cluster_name');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->timestamp('scan_started_at')->nullable();
            $table->timestamp('scan_completed_at')->nullable();
            $table->integer('scan_duration_seconds')->nullable();
            
            // Vulnerability counts
            $table->integer('critical_count')->default(0);
            $table->integer('high_count')->default(0);
            $table->integer('medium_count')->default(0);
            $table->integer('low_count')->default(0);
            $table->integer('unknown_count')->default(0);
            $table->integer('total_vulnerabilities')->default(0);
            
            // Report file paths
            $table->string('json_report_path')->nullable();
            $table->string('summary_report_path')->nullable();
            $table->string('pdf_report_path')->nullable();
            
            // Scan metadata
            $table->text('trivy_version')->nullable();
            $table->text('scan_command')->nullable();
            $table->text('error_message')->nullable();
            $table->json('scan_metadata')->nullable(); // Additional metadata as JSON
            
            // Scheduling
            $table->boolean('is_scheduled')->default(false); // True for cron jobs, false for manual scans
            $table->timestamp('next_scheduled_scan')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['cluster_name', 'status']);
            $table->index(['cluster_name', 'created_at']);
            $table->index('status');
            $table->index('is_scheduled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_reports');
    }
};

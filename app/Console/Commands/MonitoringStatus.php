<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\WebsiteMonitoringLog;

class MonitoringStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitoring:status {--website-id= : Show status for specific website}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show current monitoring status for all websites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Website Monitoring Status');
        $this->line('========================');

        $query = Website::with(['urls']);
        
        if ($this->option('website-id')) {
            $query->where('id', $this->option('website-id'));
        }
        
        $websites = $query->get();

        if ($websites->isEmpty()) {
            $this->warn('No websites found.');
            return 0;
        }

        foreach ($websites as $website) {
            $this->line('');
            $this->info("Website: {$website->name}");
            $this->line("Status: {$website->status_text}");
            $this->line("Uptime: {$website->uptime_percentage}%");
            
            if ($website->last_checked_at) {
                $this->line("Last checked: {$website->last_checked_at->diffForHumans()}");
            }

            foreach ($website->urls as $url) {
                $this->line("  URL: {$url->url}");
                $this->line("    Status: " . ($url->status_code === 200 ? '✅ UP' : '❌ DOWN'));
                
                if ($url->status_code) {
                    $this->line("    HTTP Code: {$url->status_code}");
                }
                
                if ($url->response_time) {
                    $this->line("    Response Time: {$url->response_time}ms");
                }

                // Show recent logs
                $recentLogs = WebsiteMonitoringLog::where('website_url_id', $url->id)
                    ->where('check_type', 'status')
                    ->latest()
                    ->take(3)
                    ->get();

                if ($recentLogs->count() > 0) {
                    $this->line("    Recent checks:");
                    foreach ($recentLogs as $log) {
                        $statusIcon = $log->status === 'up' ? '✅' : '❌';
                        $this->line("      {$statusIcon} {$log->checked_at->format('Y-m-d H:i:s')} - {$log->status}");
                    }
                }
            }
        }

        $this->line('');
        $this->info('Monitoring System Status:');
        $this->line('- Status monitoring: Every minute');
        $this->line('- SSL monitoring: Daily at 2 AM');
        $this->line('- Scheduler: ' . (file_exists('/tmp/schedule-running') ? '🟢 Running' : '🔴 Check cron job'));

        return 0;
    }
}

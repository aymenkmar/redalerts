<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Services\WebsiteMonitoringService;
use App\Services\WebsiteNotificationService;

class MonitorWebsiteStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websites:monitor-status {--website-id= : Monitor specific website ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor website status (HTTP 200 checks) - runs every minute';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting website status monitoring...');

        $monitoringService = new WebsiteMonitoringService();
        $notificationService = new WebsiteNotificationService();
        
        // Get websites to monitor
        $query = Website::with(['urls' => function ($query) {
            $query->where('monitor_status', true);
        }])->where('is_active', true);
        
        if ($this->option('website-id')) {
            $query->where('id', $this->option('website-id'));
        }
        
        $websites = $query->get();
        
        if ($websites->isEmpty()) {
            $this->warn('No active websites found for status monitoring.');
            return 0;
        }

        $this->info("Found {$websites->count()} website(s) to monitor for status.");

        $totalUrls = 0;
        $successfulChecks = 0;
        $failedChecks = 0;

        foreach ($websites as $website) {
            $this->line("Monitoring website: {$website->name}");
            
            foreach ($website->urls as $url) {
                if (!$url->monitor_status) {
                    continue;
                }

                $totalUrls++;
                $this->line("  Checking status: {$url->url}");

                try {
                    $result = $monitoringService->checkStatus($url);
                    
                    $status = $result['status'];
                    $statusIcon = $status === 'up' ? '✅' : '❌';
                    
                    $this->line("    {$statusIcon} Status: {$status}");
                    
                    if (isset($result['response_time'])) {
                        $this->line("      Response time: {$result['response_time']}ms");
                    }
                    
                    if (isset($result['status_code'])) {
                        $this->line("      HTTP Status: {$result['status_code']}");
                    }
                    
                    if (isset($result['error']) && $result['error']) {
                        $this->line("      Error: {$result['error']}");
                    }
                    
                    $successfulChecks++;
                    
                } catch (\Exception $e) {
                    $this->error("    Failed to monitor {$url->url}: " . $e->getMessage());
                    $failedChecks++;
                }
            }
            
            $this->line('');
        }

        // Process notifications
        $this->info('Processing notifications...');
        $notificationService->processAllNotifications();

        // Summary
        $this->info('Status monitoring completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total URLs processed', $totalUrls],
                ['Successful checks', $successfulChecks],
                ['Failed checks', $failedChecks],
                ['Success rate', $totalUrls > 0 ? round(($successfulChecks / $totalUrls) * 100, 2) . '%' : '0%'],
            ]
        );

        return 0;
    }
}

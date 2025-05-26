<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Services\WebsiteMonitoringService;

class MonitorWebsiteDomainSsl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websites:monitor-domain-ssl {--website-id= : Monitor specific website ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor website domain and SSL validation - runs every 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting website domain and SSL monitoring...');

        $monitoringService = new WebsiteMonitoringService();

        // Get websites to monitor
        $query = Website::with(['urls' => function ($query) {
            $query->where(function ($q) {
                $q->where('monitor_domain', true)
                  ->orWhere('monitor_ssl', true);
            });
        }])->where('is_active', true);

        if ($this->option('website-id')) {
            $query->where('id', $this->option('website-id'));
        }

        $websites = $query->get();

        if ($websites->isEmpty()) {
            $this->warn('No active websites found for domain/SSL monitoring.');
            return 0;
        }

        $this->info("Found {$websites->count()} website(s) to monitor for domain/SSL.");

        $totalChecks = 0;
        $successfulChecks = 0;
        $failedChecks = 0;

        foreach ($websites as $website) {
            $this->line("Monitoring website: {$website->name}");

            foreach ($website->urls as $url) {
                $this->line("  Checking: {$url->url}");

                try {
                    // Domain validation
                    if ($url->monitor_domain) {
                        $totalChecks++;
                        $this->line("    Checking domain validation...");

                        $result = $monitoringService->checkDomain($url);
                        $status = $result['status'];
                        $statusIcon = $status === 'up' ? '✅' : ($status === 'warning' ? '⚠️' : '❌');

                        $this->line("      {$statusIcon} Domain: {$status}");

                        if (isset($result['error']) && $result['error']) {
                            $this->line("        Error: {$result['error']}");
                        }

                        if (isset($result['dns_records'])) {
                            $this->line("        DNS records: " . count($result['dns_records']) . " found");
                        }

                        $successfulChecks++;
                    }

                    // SSL validation
                    if ($url->monitor_ssl) {
                        $totalChecks++;
                        $this->line("    Checking SSL certificate...");

                        $result = $monitoringService->checkSSL($url);
                        $status = $result['status'];
                        $statusIcon = $status === 'up' ? '✅' : ($status === 'warning' ? '⚠️' : '❌');

                        $this->line("      {$statusIcon} SSL: {$status}");

                        if (isset($result['error']) && $result['error']) {
                            $this->line("        Error: {$result['error']}");
                        }

                        if (isset($result['days_until_expiry'])) {
                            $days = (int) $result['days_until_expiry'];
                            $this->line("        Expires in: {$days} days");

                            if (isset($result['issuer'])) {
                                $this->line("        Issuer: {$result['issuer']}");
                            }
                        }

                        $successfulChecks++;
                    }

                } catch (\Exception $e) {
                    $this->error("    Failed to monitor {$url->url}: " . $e->getMessage());
                    $failedChecks++;
                }
            }

            $this->line('');
        }

        // Summary
        $this->info('Domain and SSL monitoring completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total checks processed', $totalChecks],
                ['Successful checks', $successfulChecks],
                ['Failed checks', $failedChecks],
                ['Success rate', $totalChecks > 0 ? round(($successfulChecks / $totalChecks) * 100, 2) . '%' : '0%'],
            ]
        );

        return 0;
    }
}

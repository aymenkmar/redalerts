<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OvhNotificationService;

class CheckOvhExpirations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ovh:check-expirations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check OVH services for expiration and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking OVH service expirations...');

        try {
            $notificationService = new OvhNotificationService();
            $results = $notificationService->checkAndNotifyExpiringServices();

            $this->info('✅ OVH expiration check completed successfully!');
            $this->line("VPS notifications sent: {$results['vps_notifications']}");
            $this->line("Server notifications sent: {$results['server_notifications']}");
            $this->line("Domain notifications sent: {$results['domain_notifications']}");

            $totalNotifications = array_sum($results);
            $this->info("Total notifications sent: {$totalNotifications}");

        } catch (\Exception $e) {
            $this->error('❌ Error checking OVH expirations: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

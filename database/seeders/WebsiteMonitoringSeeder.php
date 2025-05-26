<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Website;
use App\Models\WebsiteUrl;

class WebsiteMonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test websites
        $websites = [
            [
                'name' => 'Google',
                'description' => 'Google search engine',
                'notification_emails' => ['admin@redalerts.tn'],
                'urls' => [
                    [
                        'url' => 'https://google.com',
                        'monitor_status' => true,
                        'monitor_domain' => true,
                        'monitor_ssl' => true,
                    ]
                ]
            ],
            [
                'name' => 'GitHub',
                'description' => 'GitHub code repository platform',
                'notification_emails' => ['admin@redalerts.tn', 'dev@redalerts.tn'],
                'urls' => [
                    [
                        'url' => 'https://github.com',
                        'monitor_status' => true,
                        'monitor_domain' => false,
                        'monitor_ssl' => true,
                    ],
                    [
                        'url' => 'https://api.github.com',
                        'monitor_status' => true,
                        'monitor_domain' => false,
                        'monitor_ssl' => false,
                    ]
                ]
            ],
            [
                'name' => 'Example Website',
                'description' => 'Example website for testing',
                'notification_emails' => ['test@redalerts.tn'],
                'urls' => [
                    [
                        'url' => 'https://example.com',
                        'monitor_status' => true,
                        'monitor_domain' => true,
                        'monitor_ssl' => true,
                    ]
                ]
            ],
            [
                'name' => 'Test HTTP Site',
                'description' => 'HTTP only site for testing',
                'notification_emails' => ['test@redalerts.tn'],
                'urls' => [
                    [
                        'url' => 'http://httpbin.org/status/200',
                        'monitor_status' => true,
                        'monitor_domain' => true,
                        'monitor_ssl' => false,
                    ]
                ]
            ],
            [
                'name' => 'UptimeRobot',
                'description' => 'Uptime monitoring service (inspiration)',
                'notification_emails' => ['admin@redalerts.tn'],
                'urls' => [
                    [
                        'url' => 'https://uptimerobot.com',
                        'monitor_status' => true,
                        'monitor_domain' => true,
                        'monitor_ssl' => true,
                    ]
                ]
            ]
        ];

        foreach ($websites as $websiteData) {
            $website = Website::create([
                'name' => $websiteData['name'],
                'description' => $websiteData['description'],
                'notification_emails' => $websiteData['notification_emails'],
                'is_active' => true,
                'overall_status' => 'unknown',
            ]);

            foreach ($websiteData['urls'] as $urlData) {
                WebsiteUrl::create([
                    'website_id' => $website->id,
                    'url' => $urlData['url'],
                    'monitor_status' => $urlData['monitor_status'],
                    'monitor_domain' => $urlData['monitor_domain'],
                    'monitor_ssl' => $urlData['monitor_ssl'],
                    'current_status' => 'unknown',
                ]);
            }
        }

        $this->command->info('Website monitoring test data created successfully!');
    }
}

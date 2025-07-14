<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WebsiteDowntimeIncident;
use App\Models\WebsiteUrl;

class CleanupStuckIncidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websites:cleanup-stuck-incidents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up stuck downtime incidents where website is up but incident is still open';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for stuck downtime incidents...');

        $stuckIncidents = WebsiteDowntimeIncident::whereNull('ended_at')
            ->with('websiteUrl')
            ->get();

        if ($stuckIncidents->isEmpty()) {
            $this->info('No stuck incidents found.');
            return 0;
        }

        $fixedCount = 0;

        foreach ($stuckIncidents as $incident) {
            $url = $incident->websiteUrl;
            
            // If the current status is 'up' but incident is still open, close it
            if ($url->current_status === 'up') {
                $endedAt = now();
                $duration = (int) $incident->started_at->diffInMinutes($endedAt);
                
                $incident->update([
                    'ended_at' => $endedAt,
                    'duration_minutes' => $duration,
                ]);
                
                $this->line("✅ Closed stuck incident for {$url->url} (duration: {$duration} minutes)");
                $fixedCount++;
            } else {
                $this->line("⚠️  Active incident for {$url->url} - status is still '{$url->current_status}'");
            }
        }

        if ($fixedCount > 0) {
            $this->info("Fixed {$fixedCount} stuck incident(s).");
        } else {
            $this->info("No stuck incidents to fix.");
        }

        return 0;
    }
}

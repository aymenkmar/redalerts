<?php

namespace App\Livewire\Kubernetes\Workloads;

use Carbon\Carbon;

class CronJobList extends BaseWorkloadList
{
    protected function getResourceMethod(): string
    {
        return 'getCronJobs';
    }

    public function calculateNextExecution($schedule, $lastScheduleTime = null, $timeZone = 'UTC')
    {
        try {
            // Parse the cron schedule
            $parts = explode(' ', $schedule);
            if (count($parts) < 5) {
                return 'Invalid schedule';
            }

            // Create a CronExpression
            $cron = new \Cron\CronExpression($schedule);

            // Create a DateTime object with the correct timezone
            $now = new \DateTime('now', new \DateTimeZone($timeZone));

            // Get the next run date
            $nextRun = $cron->getNextRunDate($now);

            return $nextRun->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return 'Error calculating next execution: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.kubernetes.workloads.cron-job-list', [
            'cronJobs' => $this->filteredResources,
        ])->layout('layouts.kubernetes');
    }
}

<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        if (! filter_var(env('MARKETING_GOOGLE_SHEET_SYNC_ENABLED', true), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $event = $schedule->command('marketing:sync-google-sheet --truncate')->withoutOverlapping();
        match (env('MARKETING_GOOGLE_SHEET_SYNC_FREQUENCY', 'hourly')) {
            'every_fifteen_minutes' => $event->everyFifteenMinutes(),
            'every_thirty_minutes' => $event->everyThirtyMinutes(),
            'daily' => $event->daily(),
            default => $event->hourly(),
        };
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

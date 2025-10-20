<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Import therapists data every minute
        $schedule->command('app:import-therapists')
            ->everyMinute()
            ->withoutOverlapping();

        // Scrape therapists data - restart every 6 hours to ensure stability
        $schedule->command('app:scrape-therapists')
            ->everyMinute()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/scrape-therapists.log'));

        // Note: The scraping command is designed to run continuously
        // It will be restarted every 6 hours to ensure stability
        // For manual start: php artisan app:scrape-therapists
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

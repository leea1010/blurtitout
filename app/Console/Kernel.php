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
            ->withoutOverlapping()
            ->before(function () {
                Log::info('Import therapists data command started at ' . now());
            })
            ->after(function () {
                Log::info('Import therapists data command finished at ' . now());
            })
            ->onFailure(function () {
                Log::error('Import therapists data command failed at ' . now());
            })
            ->onSuccess(function () {
                Log::info('Import therapists data command completed successfully at ' . now());
            });

        // Scrape therapists data - restart every 6 hours to ensure stability
        $schedule->command('app:scrape-therapists')
            ->everySixHours()
            ->withoutOverlapping()
            ->before(function () {
                Log::info('Scrape therapists data command started at ' . now());
            })
            ->after(function () {
                Log::info('Scrape therapists data command finished at ' . now());
            })
            ->onFailure(function () {
                Log::error('Scrape therapists data command failed at ' . now());
            })
            ->onSuccess(function () {
                Log::info('Scrape therapists data command completed successfully at ' . now());
            });

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

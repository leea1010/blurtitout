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
        // Run scrape reins data command daily at 1:00 AM
        $schedule->command('app:scrape-reins-data')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->before(function () {
                Log::info('Scrape REINS data command started at ' . now() . ' (1:00 AM schedule)');
            })
            ->after(function () {
                Log::info('Scrape REINS data command finished at ' . now() . ' (1:00 AM schedule)');
            })
            ->onFailure(function () {
                Log::error('Scrape REINS data command failed at ' . now() . ' (1:00 AM schedule)');
            })
            ->onSuccess(function () {
                Log::info('Scrape REINS data command completed successfully at ' . now() . ' (1:00 AM schedule)');
            });

        // Run scrape reins data command daily at 12:00 PM (noon)
        $schedule->command('app:scrape-reins-data')
            ->dailyAt('12:00')
            ->withoutOverlapping()
            ->before(function () {
                Log::info('Scrape REINS data command started at ' . now() . ' (12:00 PM schedule)');
            })
            ->after(function () {
                Log::info('Scrape REINS data command finished at ' . now() . ' (12:00 PM schedule)');
            })
            ->onFailure(function () {
                Log::error('Scrape REINS data command failed at ' . now() . ' (12:00 PM schedule)');
            })
            ->onSuccess(function () {
                Log::info('Scrape REINS data command completed successfully at ' . now() . ' (12:00 PM schedule)');
            });
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

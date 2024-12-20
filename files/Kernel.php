<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\SyncCourseCatalogCommand::class,
        Commands\SyncImportantDatesCommand::class,
        Commands\SyncProfessorRatingsCommand::class,
        Commands\SyncTimetablesCommand::class,
        Commands\SyncHistoricalTimetablesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync course catalog weekly
        $schedule->command('sync:courses')
            ->weekly()
            ->sundays()
            ->at('01:00')
            ->runInBackground()
            ->withoutOverlapping();

        // Sync upcoming term timetables weekly
        $schedule->command('sync:timetables:upcoming')
            ->weekly()
            ->mondays()
            ->at('02:00')
            ->runInBackground()
            ->withoutOverlapping();

        // Sync professor ratings weekly
        $schedule->command('sync:professors')
            ->weekly()
            ->mondays()
            ->at('03:00')
            ->runInBackground()
            ->withoutOverlapping();

        // Sync important dates daily
        $schedule->command('sync:dates')
            ->dailyAt('00:00')
            ->runInBackground()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}

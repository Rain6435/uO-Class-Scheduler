<?php

namespace App\Console\Commands;

use App\Services\DataSync\TimetableSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncTimetablesCommand extends Command
{
    protected $signature = 'sync:timetables:upcoming
        {--subject=* : Specific subject codes to sync}';

    protected $description = 'Synchronize upcoming term timetables from the university website';

    public function __construct(private readonly TimetableSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $subjects = $this->option('subject');
        [$term, $year] = $this->getUpcomingTerm();

        // Skip if we've already synced this term recently (within a week)
        $cacheKey = "timetables_synced_{$year}_{$term}_weekly";
        if (Cache::has($cacheKey)) {
            $this->info("Skipping {$term} {$year} - already synced within the last week");
            return self::SUCCESS;
        }

        $this->info("Starting timetable sync for {$term} {$year}...");
        $startTime = now();

        try {
            $stats = $this->syncService->sync($year, $term, $subjects);

            $this->table(
                ['Metric', 'Count'],
                collect($stats)->map(fn($value, $key) => [$key, $value])->toArray()
            );

            // Cache for one week
            Cache::put($cacheKey, true, Carbon::now()->addWeek());

            $this->info("Sync completed in " . $startTime->diffInSeconds(now()) . " seconds");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }

    private function getUpcomingTerm(): array
    {
        $now = Carbon::now();

        return match (true) {
            $now->month >= 9 => ['winter', $now->year + 1],
            $now->month >= 5 => ['fall', $now->year],
            default => ['summer', $now->year]
        };
    }
}

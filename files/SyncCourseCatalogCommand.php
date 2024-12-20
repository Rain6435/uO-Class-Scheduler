<?php

namespace App\Console\Commands;

use App\Services\DataSync\CourseSyncService;
use Illuminate\Console\Command;


class SyncCourseCatalogCommand extends Command
{
    protected $signature = 'sync:courses {--subject=* : Specific subject codes to sync}';
    protected $description = 'Synchronize course catalog data from the university website';

    public function __construct(private readonly CourseSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting course catalog sync...');
        $startTime = now();

        try {
            $stats = $this->syncService->sync();

            $this->table(
                ['Metric', 'Count'],
                collect($stats)->map(fn($value, $key) => [$key, $value])->toArray()
            );

            $this->info("Sync completed in " . $startTime->diffInSeconds(now()) . " seconds");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return self::FAILURE;
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Services\DataSync\ImportantDatesSyncService;
use Illuminate\Console\Command;

class SyncImportantDatesCommand extends Command
{
    protected $signature = 'sync:dates';
    protected $description = 'Synchronize important academic dates from the university website';

    public function __construct(private readonly ImportantDatesSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting important dates sync...');
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

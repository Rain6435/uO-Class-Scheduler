<?php

namespace App\Console\Commands;

use App\Services\DataSync\ProfessorSyncService;
use Illuminate\Console\Command;


class SyncProfessorRatingsCommand extends Command
{
    protected $signature = 'sync:professors {--school=* : School names to sync (defaults to University of Ottawa)}';
    protected $description = 'Synchronize professor ratings from RateMyProfessor';

    public function __construct(private readonly ProfessorSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting professor ratings sync...');
        $startTime = now();

        try {
            $schools = $this->option('school') ?: ['University of Ottawa'];
            $totalStats = ['professors' => 0, 'updated' => 0, 'skipped' => 0];

            foreach ($schools as $school) {
                $this->info("Syncing data for: $school");
                $stats = $this->syncService->sync($school);

                foreach ($stats as $key => $value) {
                    $totalStats[$key] += $value;
                }

                $this->newLine();
            }

            $this->table(
                ['Metric', 'Count'],
                collect($totalStats)->map(fn($value, $key) => [$key, $value])->toArray()
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

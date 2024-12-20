<?php

namespace App\Console\Commands;

use App\Services\DataSync\TimetableSyncService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncHistoricalTimetablesCommand extends Command
{
    protected $signature = 'sync:timetables:historical
        {--from-year=2021 : Start year for historical sync}
        {--subject=* : Specific subject codes to sync}';

    protected $description = 'Synchronize historical course timetables from 2021 onwards';

    private const TERMS = ['winter', 'summer', 'fall'];

    public function __construct(private readonly TimetableSyncService $syncService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $fromYear = (int) $this->option('from-year');
        $subjects = $this->option('subject');
        $now = now();

        $this->info("Starting historical timetable sync from {$fromYear}...");
        $startTime = $now;

        $totalStats = ['sections' => 0, 'courses' => 0, 'failed' => 0];

        // Generate all term combinations from 2021 to current
        $combinations = $this->generateTermCombinations($fromYear, $now->year);

        $progressBar = $this->output->createProgressBar(count($combinations));
        $progressBar->start();

        foreach ($combinations as [$year, $term]) {
            try {
                $this->info("\nSyncing {$term} {$year}...");
                $stats = $this->syncService->sync($year, $term, $subjects);

                foreach ($stats as $key => $value) {
                    $totalStats[$key] += $value;
                }

                // Mark this term as synced in cache
                Cache::forever("timetables_synced_{$year}_{$term}", true);

                // Respect rate limits
                sleep(2);
            } catch (\Exception $e) {
                $this->error("\nFailed to sync {$term} {$year}: {$e->getMessage()}");
                $totalStats['failed']++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            collect($totalStats)->map(fn($value, $key) => [$key, $value])->toArray()
        );

        $this->info("\nHistorical sync completed in " . $startTime->diffInSeconds(now()) . " seconds");
        return self::SUCCESS;
    }

    private function generateTermCombinations(int $fromYear, int $currentYear): array
    {
        $combinations = [];

        for ($year = $fromYear; $year <= $currentYear; $year++) {
            foreach (self::TERMS as $term) {
                // Skip future terms
                if ($year === $currentYear) {
                    $skipTerm = match ($term) {
                        'winter' => Carbon::create($year, 1),
                        'summer' => Carbon::create($year, 5),
                        'fall' => Carbon::create($year, 9),
                    };

                    if ($skipTerm->isFuture()) {
                        continue;
                    }
                }

                $combinations[] = [$year, $term];
            }
        }

        return $combinations;
    }
}

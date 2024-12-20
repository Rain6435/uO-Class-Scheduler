<?php

namespace App\Services\DataSync;

use App\DTOs\ProfessorData;
use App\Repositories\Contracts\ProfessorRepositoryInterface;
use App\Services\Parser\RateMyProfessorParser;
use App\Services\Scraper\RateMyProfessorScraper;

class ProfessorSyncService extends BaseSyncService
{
    public function __construct(
        private readonly RateMyProfessorScraper $scraper,
        private readonly RateMyProfessorParser $parser,
        private readonly ProfessorRepositoryInterface $repository
    ) {}

    /**
     * Sync professor ratings
     *
     * @return array Statistics about the sync operation
     *
     * @throws DataSyncException
     */
    public function sync(string $schoolName = 'University of Ottawa'): array
    {
        return $this->executeSync(function () use ($schoolName) {
            $stats = ['professors' => 0, 'updated' => 0, 'skipped' => 0];

            $this->logProgress("Starting RateMyProfessor sync for {$schoolName}");

            $rawData = $this->scraper->scrape(['school_name' => $schoolName]);
            /** @var ProfessorData[] $professors */
            $professors = $this->parser->parse($rawData);

            foreach ($professors as $professor) {
                // Skip invalid or incomplete data
                if ($professor->rating < 0 || empty($professor->firstName) || empty($professor->lastName)) {
                    $stats['skipped']++;

                    continue;
                }

                $this->repository->upsertProfessor($professor);
                $stats['professors']++;

                if ($professor->rmpId) {
                    $stats['updated']++;
                }
            }

            $this->logProgress('Sync completed', $stats);

            return $stats;
        });
    }

    protected function getSyncName(): string
    {
        return 'RateMyProfessor';
    }
}

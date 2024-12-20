<?php

namespace App\Services\DataSync;

use App\DTOs\ImportantDateData;
use App\Repositories\Contracts\ImportantDatesRepositoryInterface;
use App\Services\Parser\ImportantDatesParser;
use App\Services\Scraper\ImportantDatesScraper;

class ImportantDatesSyncService extends BaseSyncService
{
    public function __construct(
        private readonly ImportantDatesScraper $scraper,
        private readonly ImportantDatesParser $parser,
        private readonly ImportantDatesRepositoryInterface $repository
    ) {}

    /**
     * Sync important academic dates
     *
     * @return array Statistics about the sync operation
     *
     * @throws DataSyncException
     */
    public function sync(): array
    {
        return $this->executeSync(function () {
            $stats = ['dates' => 0, 'terms' => 0];

            $this->logProgress('Starting important dates sync');

            $rawData = $this->scraper->scrape();
            /** @var ImportantDateData[] $dates */
            $dates = $this->parser->parse($rawData);

            $processedTerms = [];

            foreach ($dates as $date) {
                $termKey = "{$date->term}-{$date->year}";

                if (! in_array($termKey, $processedTerms)) {
                    $processedTerms[] = $termKey;
                    $stats['terms']++;
                }

                $this->repository->upsertDate($date);
                $stats['dates']++;
            }

            $this->logProgress('Sync completed', $stats);

            return $stats;
        });
    }

    protected function getSyncName(): string
    {
        return 'Important Dates';
    }
}

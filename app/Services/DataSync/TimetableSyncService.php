<?php

namespace App\Services\DataSync;

use App\DTOs\SectionData;
use App\Repositories\Contracts\TimetableRepositoryInterface;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Services\Parser\TimetableParser;
use App\Services\Scraper\TimetableScraper;
use Exception;

class TimetableSyncService extends BaseSyncService
{
    public function __construct(
        private readonly TimetableScraper $scraper,
        private readonly TimetableParser $parser,
        private readonly TimetableRepositoryInterface $repository,
        private readonly CourseRepositoryInterface $courseRepository
    ) {}

    /**
     * Sync timetables for specified term
     *
     * @param  array|null  $subjectCodes  Specific subjects to sync, or null for all
     * @return array Statistics about the sync operation
     *
     * @throws DataSyncException
     */
    public function sync(int $year, string $term, ?array $subjectCodes = null): array
    {
        return $this->executeSync(function () use ($year, $term, $subjectCodes) {
            $stats = ['sections' => 0, 'courses' => 0, 'failed' => 0];

            // Get subjects to process
            $subjects = $subjectCodes
                ? $this->courseRepository->getSubjectsByCodes($subjectCodes)
                : $this->courseRepository->getAllSubjects();

            foreach ($subjects as $subject) {
                $this->logProgress("Syncing timetable for {$subject->code} {$term} {$year}");

                try {
                    $rawData = $this->scraper->scrape([
                        'year' => $year,
                        'term' => $term,
                        'subject_code' => $subject->code,
                    ]);

                    /** @var SectionData[] $sections */
                    $sections = $this->parser->parse($rawData);
                    foreach ($sections as $section) {
                        $this->repository->upsertSection($section);
                        $stats['sections']++;
                    }

                    $stats['courses']++;
                } catch (Exception $e) {
                    $stats['failed']++;
                    $this->logProgress("Failed to sync {$subject->code}: {$e->getMessage()}");

                    continue;
                }

                // Respect rate limits
                sleep(2);
            }

            $this->logProgress('Sync completed', $stats);

            return $stats;
        });
    }

    protected function getSyncName(): string
    {
        return 'Timetable';
    }
}

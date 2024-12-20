<?php

namespace App\Services\DataSync;

use App\DTOs\CourseData;
use App\DTOs\SubjectData;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Services\Parser\CourseParser;
use App\Services\Scraper\CourseCatalogScraper;
use Exception;

class CourseSyncService extends BaseSyncService
{
    public function __construct(
        private readonly CourseCatalogScraper $scraper,
        private readonly CourseParser $parser,
        private readonly CourseRepositoryInterface $repository
    ) {}

    /**
     * Sync all courses data
     *
     * @return array Statistics about the sync operation
     *
     * @throws DataSyncException
     */
    public function sync(): array
    {
        return $this->executeSync(function () {
            $stats = ['subjects' => 0, 'courses' => 0, 'updated' => 0, 'failed' => 0];

            // First sync subjects
            $this->logProgress('Starting subjects sync');
            $rawSubjects = $this->scraper->scrape();
            /** @var SubjectData[] $subjects */
            $subjects = $this->parser->parse($rawSubjects);

            foreach ($subjects as $subject) {
                $this->repository->upsertSubject($subject);
                $stats['subjects']++;

                // Then sync courses for each subject
                $this->logProgress("Syncing courses for {$subject->subjectCode}");
                try {
                    $rawCourses = $this->scraper->scrape(['subject_code' => $subject->subjectCode]);
                    /** @var CourseData[] $courses */
                    $courses = $this->parser->parse($rawCourses);
                    dump($courses);

                    foreach ($courses as $course) {
                        $this->repository->upsertCourse($course);
                        $stats['courses']++;
                    }
                } catch (Exception $e) {
                    $stats['failed']++;
                    $this->logProgress("Failed to sync {$subject->subjectCode}: {$e->getMessage()}");

                    continue;
                }
            }

            $this->logProgress('Sync completed', $stats);

            return $stats;
        });
    }

    protected function getSyncName(): string
    {
        return 'Course Catalog';
    }
}

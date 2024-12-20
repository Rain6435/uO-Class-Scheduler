<?php

namespace Database\Seeders;

use App\Services\CourseApiClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FullSyncSeeder extends Seeder
{
    private array $seasons = ['winter', 'summer', 'fall'];

    public function __construct(
        private readonly CourseApiClient $apiClient
    ) {}

    public function run(): void
    {
        $this->command->info('Starting full database synchronization from 2021...');

        try {
            // Generate all term combinations from 2021 to current year + 1
            $currentYear = Carbon::now()->year;
            $terms = $this->generateTerms(2020, $currentYear + 1);

            $this->command->info(sprintf(
                'Will sync %d terms from Winter 2020 to Fall %d',
                count($terms),
                $currentYear + 1
            ));

            $totalStats = [
                'terms_processed' => 0,
                'courses_synced' => 0,
                'sections_synced' => 0,
                'errors' => 0,
            ];

            // Process each term
            foreach ($terms as $term) {
                $this->command->info(sprintf(
                    'Processing %s %d...',
                    ucfirst($term['season']),
                    $term['year']
                ));

                try {
                    // Get all courses for the term
                    $courses = $this->apiClient->getTermCourses(
                        $term['season'],
                        $term['year']
                    );

                    $termStats = [
                        'courses' => 0,
                        'sections' => 0,
                    ];

                    // Process each course
                    foreach ($courses as $course) {
                        try {
                            // Get detailed course info including sections
                            $details = $this->apiClient->getCourseDetails(
                                $course['subject_code'],
                                $course['course_code'],
                                $term['season'],
                                $term['year']
                            );

                            // TODO: Process course details and save to database
                            $termStats['courses']++;
                            $termStats['sections'] += count($details['sections'] ?? []);

                            // Respect API rate limits
                            usleep(250000); // 250ms delay between requests
                        } catch (\Exception $e) {
                            $this->command->warn(sprintf(
                                'Failed to process course %s%s: %s',
                                $course['subject_code'],
                                $course['course_code'],
                                $e->getMessage()
                            ));
                            $totalStats['errors']++;
                            continue;
                        }
                    }

                    // Update total stats
                    $totalStats['terms_processed']++;
                    $totalStats['courses_synced'] += $termStats['courses'];
                    $totalStats['sections_synced'] += $termStats['sections'];

                    // Show term stats
                    $this->command->table(
                        ['Metric', 'Count'],
                        [
                            ['Courses', $termStats['courses']],
                            ['Sections', $termStats['sections']],
                        ]
                    );

                    // Larger delay between terms
                    sleep(2);
                } catch (\Exception $e) {
                    $this->command->error(sprintf(
                        'Failed to process term %s %d: %s',
                        $term['season'],
                        $term['year'],
                        $e->getMessage()
                    ));
                    Log::error('Term sync failed', [
                        'term' => $term,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $totalStats['errors']++;
                    continue;
                }
            }

            // Show final stats
            $this->command->info('Synchronization completed!');
            $this->command->table(
                ['Metric', 'Count'],
                collect($totalStats)->map(fn($value, $key) => [
                    str_replace('_', ' ', ucfirst($key)),
                    $value
                ])->toArray()
            );
        } catch (\Exception $e) {
            Log::error('Full sync failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            $this->command->error('Synchronization failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate all term combinations between start and end year
     */
    private function generateTerms(int $startYear, int $endYear): array
    {
        $terms = [];
        for ($year = $startYear; $year <= $endYear; $year++) {
            foreach ($this->seasons as $season) {
                // Skip future terms beyond next term
                if ($year === $endYear && $season !== 'winter') {
                    continue;
                }
                $terms[] = ['season' => $season, 'year' => $year];
            }
        }
        return $terms;
    }
}

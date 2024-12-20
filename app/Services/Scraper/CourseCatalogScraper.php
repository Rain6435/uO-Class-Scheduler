<?php

namespace App\Services\Scraper;

use App\Exceptions\ScraperException;
use App\Services\Scraper\Contracts\ScraperInterface;

class CourseCatalogScraper extends BaseScraper implements ScraperInterface
{
    private const BASE_URL = 'https://catalogue.uottawa.ca/en/courses/';

    /**
     * Scrape course catalog data
     *
     * @param  array  $params  Optional parameters including 'subject_code' for specific subject
     *
     * @throws ScraperException
     */
    public function scrape(array $params = []): array
    {
        if (isset($params['subject_code'])) {
            return $this->scrapeCourses($params['subject_code']);
        }

        return $this->scrapeSubjects();
    }

    /**
     * Scrape list of all subjects
     *
     * @throws ScraperException
     */
    private function scrapeSubjects(): array
    {
        $html = $this->get(self::BASE_URL);

        return [
            'type' => 'subjects',
            'html' => $html,
            'base_url' => self::BASE_URL,
        ];
    }

    /**
     * Scrape courses for a specific subject
     *
     * @throws ScraperException
     */
    private function scrapeCourses(string $subjectCode): array
    {
        $url = self::BASE_URL.strtolower($subjectCode).'/';
        $html = $this->get($url);

        return [
            'type' => 'courses',
            'html' => $html,
            'subject_code' => $subjectCode,
        ];
    }
}

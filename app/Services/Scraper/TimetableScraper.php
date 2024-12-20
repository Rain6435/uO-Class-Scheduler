<?php

namespace App\Services\Scraper;

use App\Exceptions\ScraperException;
use App\Services\Scraper\Contracts\ScraperInterface;
use Symfony\Component\DomCrawler\Crawler;

class TimetableScraper extends BaseScraper implements ScraperInterface
{
    private const BASE_URL = 'https://uocampus.public.uottawa.ca/psc/csprpr9pub/EMPLOYEE/HRMS/c/UO_SR_AA_MODS.UO_PUB_CLSSRCH.GBL';

    private array $formState = [];

    private array $termMap = [
        'fall' => '9',
        'summer' => '5',
        'winter' => '1',
    ];

    /**
     * Scrape timetable data
     */
    public function scrape(array $params = []): array
    {
        dump("Starting scrape with params:", $params);

        $this->validateParams($params);

        // Initialize session and get form state
        $this->initializeSession();

        // Format the query parameters
        $queryParams = $this->formatQueryParams($params);

        // Debug final request data
        $finalParams = array_merge($this->formState, $queryParams);

        // Build query string for debugging
        $queryString = http_build_query($finalParams);
        dump("Full URL would be:", self::BASE_URL . '?' . $queryString);

        // Perform the search
        $html = $this->post(self::BASE_URL, $finalParams);

        return [
            'html' => $html,
            'year' => $params['year'],
            'term' => $params['term'],
        ];
    }

    private function initializeSession(): void
    {
        dump("Initializing session, making GET request to:", self::BASE_URL);

        $response = $this->get(self::BASE_URL);
        dump("Initial response received, length:", strlen($response));

        $crawler = new Crawler($response);

        // Extract hidden form fields
        $hiddenFields = [];
        $crawler->filter('input[type="hidden"]')->each(function (Crawler $node) use (&$hiddenFields) {
            $id = $node->attr('id');
            $value = $node->attr('value');
            if ($id && $value) {
                $hiddenFields[$id] = $value;
                $this->formState[$id] = $value;
            }
        });
        dump("Found hidden fields:", $hiddenFields);

        // Add required form parameters
        $additionalParams = [
            'ICAction' => 'CLASS_SRCH_WRK2_SSR_PB_CLASS_SRCH',
            'SSR_CLSRCH_WRK_SSR_OPEN_ONLY$chk$0' => 'N',
            'SSR_CLSRCH_WRK_SSR_OPEN_ONLY$0' => 'N',
            'ICStateNum' => '1',
        ];
        dump("Adding additional parameters:", $additionalParams);

        $this->formState = array_merge($this->formState, $additionalParams);
        dump("Final form state after initialization:", $this->formState);
    }

    private function formatQueryParams(array $params): array
    {
        dump("Formatting query params from:", $params);

        $termCode = '2' . substr($params['year'], -2) . $this->termMap[$params['term']];
        dump("Generated term code:", $termCode);

        $queryParams = [
            'CLASS_SRCH_WRK2_STRM$35$' => $termCode,
            'SSR_CLSRCH_WRK_SUBJECT$0' => $params['subject_code'] ?? substr($params['course_code'], 0, 3),
        ];

        if (isset($params['course_code'])) {
            $queryParams['SSR_CLSRCH_WRK_CATALOG_NBR$0'] = substr($params['course_code'], 3);
            $queryParams['SSR_CLSRCH_WRK_SSR_EXACT_MATCH1$0'] = 'E';
            dump("Added course code specific params:", [
                'catalog_number' => substr($params['course_code'], 3),
                'exact_match' => 'E'
            ]);
        }

        dump("Final query parameters:", $queryParams);
        return $queryParams;
    }

    private function validateParams(array $params): void
    {
        dump("Validating params:", $params);

        if (! isset($params['year'], $params['term'])) {
            throw new ScraperException('Year and term are required');
        }

        if (! isset($params['course_code']) && ! isset($params['subject_code'])) {
            throw new ScraperException('Either course_code or subject_code is required');
        }

        if (! isset($this->termMap[$params['term']])) {
            throw new ScraperException('Invalid term. Must be one of: ' . implode(', ', array_keys($this->termMap)));
        }

        dump("Params validation successful");
    }
}

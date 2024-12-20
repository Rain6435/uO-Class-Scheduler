<?php

namespace App\Services\Scraper;

use App\Exceptions\ScraperException;
use App\Services\Scraper\Contracts\ScraperInterface;

class ImportantDatesScraper extends BaseScraper implements ScraperInterface
{
    private const BASE_URL = 'https://www.uottawa.ca/important-academic-dates-and-deadlines/';

    /**
     * Scrape important academic dates
     *
     * @param  array  $params  Optional parameters
     *
     * @throws ScraperException
     */
    public function scrape(array $params = []): array
    {
        $html = $this->get(self::BASE_URL);

        return [
            'html' => $html,
            'params' => $params,
        ];
    }
}

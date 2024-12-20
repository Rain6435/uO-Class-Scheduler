<?php

namespace App\Services\Scraper\Contracts;

interface ScraperInterface
{
    /**
     * Scrape data from the source
     *
     * @param  array  $params  Optional parameters for the scraper
     * @return mixed Raw scraped data
     *
     * @throws \App\Exceptions\ScraperException
     */
    public function scrape(array $params = []): mixed;
}

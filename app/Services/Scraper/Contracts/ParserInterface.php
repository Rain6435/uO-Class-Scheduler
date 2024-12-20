<?php

namespace App\Services\Scraper\Contracts;

interface ParserInterface
{
    /**
     * Parse raw scraped data into a structured format
     *
     * @param  mixed  $rawData  The raw data to parse
     * @return array Parsed data in a structured format
     *
     * @throws \App\Exceptions\ScraperException
     */
    public function parse(mixed $rawData): array;
}

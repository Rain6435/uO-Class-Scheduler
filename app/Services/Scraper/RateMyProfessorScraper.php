<?php

namespace App\Services\Scraper;

use App\Exceptions\ScraperException;
use App\Services\Scraper\Contracts\ScraperInterface;

class RateMyProfessorScraper extends BaseScraper implements ScraperInterface
{
    private const BASE_URL = 'https://solr-aws-elb-production.ratemyprofessors.com//solr/rmp/select';

    /**
     * Scrape professor ratings for a school
     *
     * @param  array  $params  Must contain 'school_name'
     *
     * @throws ScraperException
     */
    public function scrape(array $params = []): array
    {
        if (! isset($params['school_name'])) {
            throw new ScraperException('School name is required');
        }

        // First get the school ID
        $schoolParams = $this->getSchoolParams($params['school_name']);

        // Then get all professors for that school
        return $this->getProfessorRatings($schoolParams);
    }

    /**
     * Get school parameters from RMP
     *
     * @throws ScraperException
     */
    private function getSchoolParams(string $schoolName): array
    {
        $params = [
            'wt' => 'json',
            'rows' => '1',
            'q' => $schoolName,
            'qf' => 'schoolname_autosuggest',
        ];

        $response = $this->get(self::BASE_URL, $params);
        $data = json_decode($response, true);

        if (! isset($data['response']['docs'][0]['pk_id'])) {
            throw new ScraperException("Could not find school: {$schoolName}");
        }

        return [
            'school_id' => $data['response']['docs'][0]['pk_id'],
            'rows' => $data['response']['numFound'],
        ];
    }

    /**
     * Get professor ratings for a school
     *
     * @throws ScraperException
     */
    private function getProfessorRatings(array $schoolParams): array
    {
        $params = [
            'wt' => 'json',
            'rows' => $schoolParams['rows'],
            'sort' => 'total_number_of_ratings_i desc',
            'fl' => 'pk_id teacherfirstname_t teacherlastname_t total_number_of_ratings_i averageratingscore_rf',
            'q' => "*:* AND schoolid_s:{$schoolParams['school_id']}",
        ];

        $response = $this->get(self::BASE_URL, $params);
        $data = json_decode($response, true);

        if (! isset($data['response']['docs'])) {
            throw new ScraperException('Failed to get professor ratings');
        }

        return $data;
    }
}

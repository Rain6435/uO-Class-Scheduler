<?php

namespace App\Services\Scraper;

use App\Exceptions\ScraperException;
use Exception;
use Illuminate\Support\Facades\Http;

abstract class BaseScraper
{
    /**
     * Make an HTTP GET request with retry logic
     *
     * @throws ScraperException
     */
    protected function get(string $url, array $params = [], int $retries = 3, int $sleepTime = 1): string
    {
        $attempt = 0;

        do {
            try {
                $response = Http::timeout(30)
                    ->retry($retries, $sleepTime * 1000)
                    ->get($url, $params);

                if ($response->successful()) {
                    return $response->body();
                }

                throw new ScraperException(
                    "HTTP request failed with status {$response->status()}: {$response->body()}"
                );
            } catch (Exception $e) {
                if (++$attempt === $retries) {
                    throw new ScraperException(
                        "Failed to fetch data after {$retries} attempts: {$e->getMessage()}",
                        0,
                        $e
                    );
                }

                sleep($sleepTime);
            }
        } while ($attempt < $retries);
    }

    /**
     * Make an HTTP POST request with retry logic
     *
     * @throws ScraperException
     */
    protected function post(
        string $url,
        array $data = [],
        array $headers = [],
        int $retries = 3,
        int $sleepTime = 1
    ): string {
        $attempt = 0;

        do {
            try {
                $response = Http::timeout(30)
                    ->withHeaders($headers)
                    ->retry($retries, $sleepTime * 1000)
                    ->post($url, $data);

                if ($response->successful()) {
                    return $response->body();
                }

                throw new ScraperException(
                    "HTTP request failed with status {$response->status()}: {$response->body()}"
                );
            } catch (Exception $e) {
                if (++$attempt === $retries) {
                    throw new ScraperException(
                        "Failed to fetch data after {$retries} attempts: {$e->getMessage()}",
                        0,
                        $e
                    );
                }

                sleep($sleepTime);
            }
        } while ($attempt < $retries);
    }
}

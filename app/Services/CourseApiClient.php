<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CourseApiClient
{
    private const BASE_URL = 'https://uschedule.me/api/scheduler/v1';

    /**
     * Query courses from the API
     */
    public function queryCourses(
        string $subjectCode,
        string $courseCode,
        string $season,
        int $year
    ): array {
        $response = Http::get(self::BASE_URL . '/courses/query/', [
            'school' => 'uottawa',
            'course_code' => $courseCode,
            'subject_code' => $subjectCode,
            'season' => $season,
            'year' => $year
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "API request failed with status code {$response->status()}: {$response->body()}"
            );
        }

        return $response->json();
    }

    /**
     * Get all available courses for a given term
     */
    public function getTermCourses(string $season, int $year): array
    {
        // TODO: Implement pagination if needed
        $response = Http::get(self::BASE_URL . '/courses/', [
            'school' => 'uottawa',
            'season' => $season,
            'year' => $year
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "API request failed with status code {$response->status()}: {$response->body()}"
            );
        }

        return $response->json();
    }

    /**
     * Get course details including sections
     */
    public function getCourseDetails(
        string $subjectCode,
        string $courseCode,
        string $season,
        int $year
    ): array {
        $response = Http::get(self::BASE_URL . '/courses/details/', [
            'school' => 'uottawa',
            'course_code' => $courseCode,
            'subject_code' => $subjectCode,
            'season' => $season,
            'year' => $year
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "API request failed with status code {$response->status()}: {$response->body()}"
            );
        }

        return $response->json();
    }
}

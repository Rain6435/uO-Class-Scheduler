<?php

namespace App\Services\Parser;

use App\DTOs\CourseData;
use App\DTOs\SubjectData;
use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

class CourseParser implements ParserInterface
{
    /**
     * Parse raw course catalog data
     */
    public function parse(mixed $rawData): array
    {
        return match ($rawData['type']) {
            'subjects' => $this->parseSubjects($rawData['html'], $rawData['base_url']),
            'courses' => $this->parseCourses($rawData['html'], $rawData['subject_code']),
            default => []
        };
    }

    /**
     * Parse subjects list
     */
    private function parseSubjects(string $html, string $baseUrl): array
    {
        $crawler = new Crawler($html);
        $subjects = [];

        $crawler->filter('.az_sitemap a')->each(function (Crawler $node) use (&$subjects, $baseUrl) {
            $href = $node->attr('href');
            if (! preg_match('/courses\/([a-zA-Z]+)\/?$/', $href, $matches)) {
                return;
            }

            $subjects[] = new SubjectData(
                subjectCode: strtoupper($matches[1]),
                name: trim($node->text()),
                catalogUrl: $baseUrl.$matches[1].'/'
            );
        });

        return $subjects;
    }

    /**
     * Parse courses for a subject
     */
    private function parseCourses(string $html, string $subjectCode): array
    {
        $crawler = new Crawler($html);
        $courses = [];

        $crawler->filter('.courseblock')->each(function (Crawler $node) use (&$courses, $subjectCode) {
            // Parse course code and title
            $titleNode = $node->filter('.courseblocktitle');
            if (! $titleNode->count()) {
                return;
            }

            $titleText = $titleNode->text();
            if (! preg_match('/([A-Z]{3,4}\s*[0-9]{4})(.+?)\(([0-9]+)\s+(?:units?|credits?)\)/', $titleText, $matches)) {
                return;
            }

            // Parse description
            $description = '';
            $descNode = $node->filter('.courseblockdesc');
            if ($descNode->count()) {
                $description = trim($descNode->text());
            }

            // Parse prerequisites and components
            $prerequisites = [];
            $components = [];
            $node->filter('.courseblockextra')->each(function (Crawler $extraNode) use (&$prerequisites, &$components) {
                $text = trim($extraNode->text());
                if (str_contains(strtolower($text), 'prerequisite')) {
                    $prerequisites[] = $text;
                } elseif (str_contains($text, 'Course Component:')) {
                    $components = array_map('trim', explode(',', explode(':', $text)[1]));
                }
            });

            $courses[] = new CourseData(
                courseCode: trim($matches[1]),
                subjectCode: $subjectCode,
                title: trim($matches[2]),
                credits: (int) $matches[3],
                description: $description,
                prerequisites: $prerequisites,
                components: $components
            );
        });

        return $courses;
    }
}

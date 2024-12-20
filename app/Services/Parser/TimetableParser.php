<?php

namespace App\Services\Parser;

use App\DTOs\ScheduleData;
use App\DTOs\SectionData;
use App\Services\Scraper\Contracts\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

class TimetableParser implements ParserInterface
{
    /**
     * Parse raw timetable data
     */
    public function parse(mixed $rawData): array
    {
        dump("Starting parse...");
        $crawler = new Crawler($rawData['html']);
        $sections = [];

        // First check if we're on a results page
        if (!str_contains($rawData['html'], 'Search Results')) {
            dump("Not a results page");
            return [];
        }
        dump("Found results page");

        // Use div.PSGROUPBOXWBO to find course sections, matching Python's behavior
        dump("Looking for course boxes...");
        $courseNodes = $crawler->filter('div.PSGROUPBOXWBO');
        dump("Found {$courseNodes->count()} potential course nodes");

        $courseNodes->each(function (Crawler $courseNode) use (&$sections, $rawData) {
            // Get course details
            $titleNode = $courseNode->filter('.PAGROUPDIVIDER');
            dump("Processing course node. Title node count: " . $titleNode->count());

            if (!$titleNode->count()) {
                dump("No title node found, skipping");
                return;
            }

            $titleText = $titleNode->text();
            dump("Found title: " . $titleText);

            preg_match('/([A-Z]{3,4}\s*[0-9]{4})/', $titleText, $matches);
            if (!$matches) {
                dump("Could not extract course code from title");
                return;
            }
            $courseCode = str_replace(' ', '', $matches[1]);
            dump("Extracted course code: " . $courseCode);

            // Parse each section - use class rather than id to match Python's behavior
            $sectionNodes = $courseNode->filter('div.PSGROUPBOXWBO');
            dump("Found {$sectionNodes->count()} section nodes");

            $sectionNodes->each(function (Crawler $sectionNode) use (&$sections, $courseCode, $rawData) {
                // Get section ID and type
                $sectionHeader = $sectionNode->filter('a[id^="MTG_CLASSNAME"]');
                dump("Processing section. Header node count: " . $sectionHeader->count());

                if (!$sectionHeader->count()) {
                    dump("No section header found, skipping");
                    return;
                }

                $headerText = $sectionHeader->text();
                dump("Section header text: " . $headerText);

                preg_match('/([A-Z0-9]+)-([A-Z]+)/', $headerText, $matches);
                if (!$matches) {
                    dump("Could not parse section ID and type");
                    return;
                }

                $sectionId = $matches[1];
                $type = $matches[2];
                dump("Section ID: $sectionId, Type: $type");

                // Get status - use class to match Python's behavior
                $status = 'CLOSED';
                $statusNode = $sectionNode->filter('.SSSGROUPBOXLTBLUE');
                dump("Status node count: " . $statusNode->count());

                if ($statusNode->count() && $statusNode->filter('img')->count()) {
                    $status = strtoupper($statusNode->filter('img')->attr('alt'));
                }
                dump("Section status: " . $status);

                // Get schedule details using classes instead of IDs
                $scheduleData = [
                    'days' => $sectionNode->filter('span[id^="MTG_DAYTIME"]'),
                    'room' => $sectionNode->filter('span[id^="MTG_ROOM"]'),
                    'dates' => $sectionNode->filter('span[id^="MTG_TOPIC"]'),
                    'instructors' => $sectionNode->filter('span[id^="MTG_INSTR"]'),
                ];

                dump("Schedule data counts:", [
                    'days' => $scheduleData['days']->count(),
                    'room' => $scheduleData['room']->count(),
                    'dates' => $scheduleData['dates']->count(),
                    'instructors' => $scheduleData['instructors']->count()
                ]);

                $schedule = [];

                if ($scheduleData['days']->count()) {
                    $daysCount = $scheduleData['days']->count();
                    dump("Processing $daysCount day entries");

                    for ($i = 0; $i < $daysCount; $i++) {
                        $dayTimeText = trim($scheduleData['days']->eq($i)->text());
                        dump("Day/Time text for index $i: " . $dayTimeText);

                        $dayTime = explode(' ', $dayTimeText);
                        if (count($dayTime) < 2) {
                            dump("Invalid day/time format, skipping");
                            continue;
                        }

                        $times = explode('-', $dayTime[1]);
                        if (count($times) !== 2) {
                            dump("Invalid time format, skipping");
                            continue;
                        }

                        $dates = [];
                        if ($scheduleData['dates']->count()) {
                            preg_match_all(
                                '/\d{4}-\d{2}-\d{2}/',
                                $scheduleData['dates']->eq($i)->text(),
                                $dateMatches
                            );
                            $dates = $dateMatches[0] ?? [];
                            dump("Extracted dates:", $dates);
                        }

                        $room = $scheduleData['room']->count() ? trim($scheduleData['room']->eq($i)->text()) : '';
                        dump("Room: " . $room);

                        $schedule[] = new ScheduleData(
                            day: $dayTime[0],
                            startTime: trim($times[0]),
                            endTime: trim($times[1]),
                            room: $room,
                            startDate: $dates[0] ?? '',
                            endDate: $dates[1] ?? ''
                        );
                    }
                }

                // Get professors
                $professors = [];
                if ($scheduleData['instructors']->count()) {
                    $professors = array_map(
                        'trim',
                        explode(',', $scheduleData['instructors']->text())
                    );
                    dump("Extracted professors:", $professors);
                }

                $sections[] = new SectionData(
                    sectionId: $sectionId,
                    courseCode: $courseCode,
                    term: $rawData['term'],
                    year: $rawData['year'],
                    type: $type,
                    status: $status,
                    schedule: array_map(fn($s) => $s->toArray(), $schedule),
                    professors: array_unique(array_filter($professors))
                );
                dump("Added section to results");
            });
        });

        dump("Parse complete. Found " . count($sections) . " total sections");
        return $sections;
    }
}

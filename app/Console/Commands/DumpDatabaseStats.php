<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Models\Course;
use App\Models\Professor;
use App\Models\CourseSection;
use App\Models\ImportantDate;
use App\Models\Term;
use Illuminate\Console\Command;

class DumpDatabaseStats extends Command
{
    protected $signature = 'db:stats';
    protected $description = 'Display database statistics';

    public function handle(): int
    {
        $stats = [
            ['Table', 'Count'],
            ['Subjects', Subject::count()],
            ['Courses', Course::count()],
            ['Professors', Professor::count()],
            ['Course Sections', CourseSection::count()],
            ['Important Dates', ImportantDate::count()],
            ['Terms', Term::count()],
        ];

        // Display some sample data
        $this->table(['Table', 'Count'], $stats);

        // Show some example data from each table
        $this->info("\nSample Subjects:");
        $this->table(
            ['Code', 'Name'],
            Subject::take(5)->get()->map(fn($s) => [$s->code, $s->name])->toArray()
        );

        $this->info("\nSample Courses:");
        $this->table(
            ['Code', 'Title'],
            Course::take(5)->get()->map(fn($c) => [$c->code, $c->title])->toArray()
        );

        $this->info("\nSample Professors:");
        $this->table(
            ['Name', 'Rating'],
            Professor::take(5)->get()->map(fn($p) => [
                $p->first_name . ' ' . $p->last_name,
                $p->rating
            ])->toArray()
        );

        return Command::SUCCESS;
    }
}

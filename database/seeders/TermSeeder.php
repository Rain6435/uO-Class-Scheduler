<?php

namespace Database\Seeders;

use App\Models\Term;
use Illuminate\Database\Seeder;

class TermSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = date('Y');
        $terms = ['winter', 'summer', 'fall'];

        // Create terms for current year and next year
        foreach (range($currentYear, $currentYear + 1) as $year) {
            foreach ($terms as $term) {
                Term::firstOrCreate([
                    'term' => $term,
                    'year' => $year,
                ]);
            }
        }
    }
}

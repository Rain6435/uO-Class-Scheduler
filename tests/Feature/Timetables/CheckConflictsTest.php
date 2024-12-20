<?php

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\SectionSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->course1 = Course::factory()->create([
        'subject_code' => 'MAT',
        'course_code' => '1300'
    ]);

    $this->course2 = Course::factory()->create([
        'subject_code' => 'PHY',
        'course_code' => '1300'
    ]);
});

test('can check for section conflicts', function () {
    // Arrange
    $section1 = CourseSection::factory()->create([
        'course_id' => $this->course1->id,
        'section_id' => 'A'
    ]);

    $section2 = CourseSection::factory()->create([
        'course_id' => $this->course2->id,
        'section_id' => 'B'
    ]);

    SectionSchedule::factory()->create([
        'section_id' => $section1->id,
        'day' => 'MON',
        'start_time' => '09:00:00',
        'end_time' => '10:20:00'
    ]);

    SectionSchedule::factory()->create([
        'section_id' => $section2->id,
        'day' => 'MON',
        'start_time' => '10:00:00',
        'end_time' => '11:20:00'
    ]);

    // Act
    $response = post('/timetables/check-conflicts', [
        'section_ids' => [$section1->id, $section2->id]
    ]);

    // Assert
    $response->assertOk()
        ->assertJson([
            'has_conflicts' => true,
            'conflicts' => [
                [
                    'course1' => 'MAT1300',
                    'course2' => 'PHY1300',
                    'day' => 'MON'
                ]
            ]
        ]);
});

test('can check if section is selectable', function () {
    // Arrange
    $section1 = CourseSection::factory()->create([
        'course_id' => $this->course1->id,
        'section_id' => 'A'
    ]);

    // Act
    $response = post('/timetables/check-section-selectable', [
        'course_id' => 'MAT1300',
        'section_id' => 'A',
        'selected_sections' => [],
        'section_conflicts' => []
    ]);

    // Assert
    $response->assertOk()
        ->assertJson([
            'is_selectable' => true
        ]);
});

test('validates required fields for conflict check', function () {
    $response = post('/timetables/check-conflicts', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['section_ids']);
});

test('handles invalid section ids', function () {
    $response = post('/timetables/check-conflicts', [
        'section_ids' => [999999]
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['section_ids.0']);
});

<?php

dataset('conflicting_schedules', [
    'overlapping_morning_classes' => [
        'section1' => [
            'day' => 'MON',
            'start_time' => '09:00:00',
            'end_time' => '10:20:00'
        ],
        'section2' => [
            'day' => 'MON',
            'start_time' => '10:00:00',
            'end_time' => '11:20:00'
        ],
        'should_conflict' => true
    ],
    'different_days' => [
        'section1' => [
            'day' => 'MON',
            'start_time' => '09:00:00',
            'end_time' => '10:20:00'
        ],
        'section2' => [
            'day' => 'TUE',
            'start_time' => '09:00:00',
            'end_time' => '10:20:00'
        ],
        'should_conflict' => false
    ],
    'back_to_back' => [
        'section1' => [
            'day' => 'MON',
            'start_time' => '09:00:00',
            'end_time' => '10:20:00'
        ],
        'section2' => [
            'day' => 'MON',
            'start_time' => '10:20:00',
            'end_time' => '11:40:00'
        ],
        'should_conflict' => false
    ]
]);

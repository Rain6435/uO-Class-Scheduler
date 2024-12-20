<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class SectionSelectableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'string'],
            'section_id' => ['required', 'string'],
            'selected_sections' => ['required', 'array'],
            'selected_sections.*.courseId' => ['required', 'string'],
            'selected_sections.*.sectionId' => ['required', 'string'],
            'section_conflicts' => ['required', 'array']
        ];
    }
}
